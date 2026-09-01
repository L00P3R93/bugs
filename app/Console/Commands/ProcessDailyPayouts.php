<?php

namespace App\Console\Commands;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Facades\KadiApi;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\DailyPayoutsSummaryNotification;
use App\Notifications\DailyTargetReachedNotification;
use App\Services\AdminNotificationRouter;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessDailyPayouts extends Command
{
    protected $signature = 'payouts:process-daily';

    protected $description = 'Hourly payout processing — credits testers for games played and manages daily target resets';

    private const GAME_RATES = [
        '2_players' => 2.50,
        '3_players' => 3.50,
        '4_players' => 4.50,
    ];

    private const GAME_TARGETS = [
        '2_players' => 40,
        '3_players' => 29,
        '4_players' => 23,
    ];

    private const TOURNAMENT_RATE = 3.00;

    private const TOURNAMENT_TARGET = 3;

    private const JACKPOT_RATE = 3.00;

    private const JACKPOT_TARGET = 3;

    /**
     * @throws \Throwable
     */
    public function handle(): int
    {
        $today = now()->timezone('Africa/Nairobi')->toDateString();
        $this->info("Processing hourly payouts for {$today}...");

        $testers = User::query()
            ->role('Tester')
            ->whereNotNull('linked_id')
            ->with('wallet')
            ->get();

        $paid = 0;
        $skipped = 0;
        $errors = 0;
        $reset = 0;
        $paidDetails = [];
        $totalAmount = 0;
        $targetsReached = [];

        foreach ($testers as $tester) {
            if (! $tester->wallet) {
                $this->warn("Tester {$tester->id} ({$tester->name}) has no wallet — skipping.");
                $skipped++;

                continue;
            }

            try {
                $wallet = $tester->wallet;

                // Step 1: Midnight reset — if last reset was not today, reset daily stats
                if ($wallet->last_daily_reset_at === null || $wallet->last_daily_reset_at->toDateString() !== $today) {
                    /*
                    $anyTargetMet = $wallet->daily_target_reached
                        || $wallet->daily_2p_games_target_reached
                        || $wallet->daily_3p_games_target_reached
                        || $wallet->daily_4p_games_target_reached
                        || $wallet->daily_tournament_target_reached
                        || $wallet->daily_jackpot_target_reached;

                    if (! $anyTargetMet && $wallet->daily_games_played > 0) {
                        $wallet->resetDailyBalance();
                        $this->line("Reset balance for tester {$tester->id} ({$tester->name}) — no targets met yesterday.");
                        Log::info("Daily reset: tester {$tester->id} balance zeroed — no daily targets reached.");
                    }
                     */
                    $wallet->resetDailyStats();
                    $reset++;
                }

                // Step 2: Fetch today's stats from KadiApi
                $stats = KadiApi::getPlayerStats($tester->linked_id, $today);
                $current = $stats['data'] ?? $stats;

                // Step 3: Calculate deltas from previous snapshot
                $previous = $wallet->daily_stats_snapshot ?? $this->emptyStats();

                $gamesDelta = $this->calculateDelta($previous['games'] ?? [], $current['games'] ?? [], self::GAME_RATES);
                $tournamentDelta = max(0, ($current['tournament']['total'] ?? 0) - ($previous['tournament']['total'] ?? 0));
                $jackpotDelta = max(0, ($current['jackpots']['total'] ?? 0) - ($previous['jackpots']['total'] ?? 0));

                $totalNewGames = $gamesDelta['total'] + $tournamentDelta + $jackpotDelta;

                if ($totalNewGames <= 0) {
                    // Still update snapshot even if no new games
                    $wallet->update(['daily_stats_snapshot' => $current]);

                    continue;
                }

                // Step 4: Calculate earnings
                $gamesEarnings = $gamesDelta['earnings'];
                $tournamentEarnings = $tournamentDelta * self::TOURNAMENT_RATE;
                $jackpotEarnings = $jackpotDelta * self::JACKPOT_RATE;
                $totalEarnings = $gamesEarnings + $tournamentEarnings + $jackpotEarnings;

                DB::transaction(function () use ($jackpotEarnings, $tournamentEarnings, $gamesEarnings, $wallet, $tester, $totalEarnings, $totalNewGames, $gamesDelta, $tournamentDelta, $jackpotDelta, $current, $today, &$paid, &$paidDetails, &$totalAmount) {
                    $wallet = Wallet::query()->where('id', $wallet->id)->lockForUpdate()->first();

                    $transaction = Transaction::query()->create([
                        'wallet_id' => $wallet->id,
                        'user_id' => $tester->id,
                        'type' => TransactionType::PAYOUT,
                        'amount' => $totalEarnings,
                        'net_amount' => $totalEarnings,
                        'status' => TransactionStatus::COMPLETED,
                        'completed_at' => now(),
                    ]);

                    $wallet->increment('balance', $totalEarnings);
                    $wallet->increment('available_balance', $totalEarnings);
                    $wallet->increment('total_earned', $totalEarnings);
                    $wallet->increment('daily_games_played', $totalNewGames);
                    $wallet->increment('daily_earned', $totalEarnings);
                    $wallet->update(['daily_stats_snapshot' => $current]);

                    $breakdown = "games: {$gamesDelta['total']} (KES {$gamesEarnings}), tournaments: {$tournamentDelta} (KES {$tournamentEarnings}), jackpots: {$jackpotDelta} (KES {$jackpotEarnings})";
                    $this->line("Paid tester {$tester->id} ({$tester->name}): KES {$totalEarnings} for {$totalNewGames} new activities ({$breakdown}). Total today: {$wallet->daily_games_played} games.");
                    Log::info("Hourly payout: tester {$tester->id} credited KES {$totalEarnings} on {$today} — {$breakdown}. Total today: {$wallet->daily_games_played}.");

                    $paidDetails[] = [
                        'name' => $tester->name,
                        'email' => $tester->email,
                        'amount' => $totalEarnings,
                        'games' => $totalNewGames,
                        'total_games' => $wallet->daily_games_played,
                    ];
                    $totalAmount += $totalEarnings;
                    $paid++;
                });

                // Step 5: Check if daily target just reached
                $wallet->refresh();
                $gamesTotal = $current['games']['total'] ?? 0;
                $tournamentTotal = $current['tournament']['total'] ?? 0;
                $jackpotTotal = $current['jackpots']['total'] ?? 0;

                $games2pMet = ($current['games']['2_players'] ?? 0) >= self::GAME_TARGETS['2_players'];
                $games3pMet = ($current['games']['3_players'] ?? 0) >= self::GAME_TARGETS['3_players'];
                $games4pMet = ($current['games']['4_players'] ?? 0) >= self::GAME_TARGETS['4_players'];
                $tournamentMet = $tournamentTotal >= self::TOURNAMENT_TARGET;
                $jackpotMet = $jackpotTotal >= self::JACKPOT_TARGET;

                $updates = [];

                if ($games2pMet && ! $wallet->daily_2p_games_target_reached) {
                    $updates['daily_2p_games_target_reached'] = true;
                }
                if ($games3pMet && ! $wallet->daily_3p_games_target_reached) {
                    $updates['daily_3p_games_target_reached'] = true;
                }
                if ($games4pMet && ! $wallet->daily_4p_games_target_reached) {
                    $updates['daily_4p_games_target_reached'] = true;
                }
                if ($tournamentMet && ! $wallet->daily_tournament_target_reached) {
                    $updates['daily_tournament_target_reached'] = true;
                }
                if ($jackpotMet && ! $wallet->daily_jackpot_target_reached) {
                    $updates['daily_jackpot_target_reached'] = true;
                }

                $anyNewlyMet = count($updates) > 0;

                if ($anyNewlyMet && ! $wallet->daily_target_reached) {
                    $updates['daily_target_reached'] = true;
                }

                if ($anyNewlyMet) {
                    $wallet->update($updates);
                    $this->line("Tester {$tester->id} ({$tester->name}) reached daily target! Withdrawals unlocked.");
                    Log::info("Daily target reached: tester {$tester->id} — 2p: {$games2pMet}, 3p: {$games3pMet}, 4p: {$games4pMet}, tournament: {$tournamentMet}, jackpot: {$jackpotMet}.");

                    $targetLabels = collect(array_keys($updates))
                        ->filter(fn ($key) => $key !== 'daily_target_reached')
                        ->map(fn ($key) => match ($key) {
                            'daily_2p_games_target_reached' => '2 Player Games',
                            'daily_3p_games_target_reached' => '3 Player Games',
                            'daily_4p_games_target_reached' => '4 Player Games',
                            'daily_tournament_target_reached' => 'Tournaments',
                            'daily_jackpot_target_reached' => 'Jackpots',
                            default => $key,
                        })
                        ->values()
                        ->all();

                    $targetsReached[] = [
                        'name' => $tester->name,
                        'email' => $tester->email,
                        'targets' => $targetLabels,
                    ];

                    $tester->notify(new DailyTargetReachedNotification($wallet, array_keys($updates)));
                }

            } catch (RequestException|ConnectionException $e) {
                $this->error("Failed to fetch stats for tester {$tester->id} ({$tester->name}): {$e->getMessage()}");
                Log::error("Hourly payout: failed to fetch stats for tester {$tester->id} on {$today} — {$e->getMessage()}");
                $errors++;
            }
        }

        $this->info("Done. Paid: {$paid}, Skipped: {$skipped}, Resets: {$reset}, Errors: {$errors}, Targets reached: ".count($targetsReached).'.');

        if (count($targetsReached) > 0) {
            $notification = new DailyPayoutsSummaryNotification(
                date: $today,
                totalTesters: $testers->count(),
                paidCount: $paid,
                skippedCount: $skipped,
                errorCount: $errors,
                totalAmount: $totalAmount,
                paidDetails: $paidDetails,
                targetsReached: $targetsReached,
            );

            AdminNotificationRouter::notifyAdmins($notification);
        }

        return self::SUCCESS;
    }

    private function calculateDelta(array $previous, array $current, array $rates): array
    {
        $total = 0;
        $earnings = 0.0;

        foreach ($rates as $key => $rate) {
            $prev = $previous[$key] ?? 0;
            $curr = $current[$key] ?? 0;
            $delta = max(0, $curr - $prev);
            $total += $delta;
            $earnings += $delta * $rate;
        }

        return [
            'total' => $total,
            'earnings' => $earnings,
        ];
    }

    private function emptyStats(): array
    {
        return [
            'total' => 0,
            'games' => ['total' => 0, '2_players' => 0, '3_players' => 0, '4_players' => 0],
            'tournament' => ['total' => 0, '3_rounds' => 0, '4_rounds' => 0, '5_rounds' => 0],
            'jackpots' => ['total' => 0, '13_rounds' => 0, '17_rounds' => 0, '21_rounds' => 0],
        ];
    }
}
