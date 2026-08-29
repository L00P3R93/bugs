<?php

namespace App\Console\Commands;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Facades\KadiApi;
use App\Models\Transaction;
use App\Models\User;
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
                    // If target was NOT reached yesterday, reset balance to zero
                    if (! $wallet->daily_target_reached && $wallet->daily_games_played > 0) {
                        $wallet->resetDailyBalance();
                        $this->line("Reset balance for tester {$tester->id} ({$tester->name}) — target not reached yesterday.");
                        Log::info("Daily reset: tester {$tester->id} balance zeroed — daily target not reached.");
                    }
                    $wallet->resetDailyStats();
                    $reset++;
                }

                // Step 2: Fetch today's game count from KadiApi
                $stats = KadiApi::getPlayerStats($tester->linked_id, $today);
                $totalGames = (int) ($stats['total'] ?? 0);

                // Step 3: Calculate new games since last check
                $newGames = $totalGames - $wallet->daily_games_played;

                if ($newGames <= 0) {
                    continue;
                }

                // Step 4: Credit earnings for new games
                $amount = $newGames * 3.50;

                DB::transaction(function () use ($wallet, $tester, $amount, $newGames, $today, &$paid, &$paidDetails, &$totalAmount) {
                    $wallet = $wallet->lockForUpdate()->first();

                    // Create payout transaction — completed immediately (no hold)
                    $transaction = Transaction::query()->create([
                        'wallet_id' => $wallet->id,
                        'user_id' => $tester->id,
                        'type' => TransactionType::PAYOUT,
                        'amount' => $amount,
                        'net_amount' => $amount,
                        'status' => TransactionStatus::COMPLETED,
                        'completed_at' => now(),
                    ]);

                    // Credit wallet directly
                    $wallet->increment('balance', $amount);
                    $wallet->increment('available_balance', $amount);
                    $wallet->increment('total_earned', $amount);
                    $wallet->increment('daily_games_played', $newGames);
                    $wallet->increment('daily_earned', $amount);

                    $this->line("Paid tester {$tester->id} ({$tester->name}): KES {$amount} for {$newGames} new games ({$wallet->daily_games_played} total today).");
                    Log::info("Hourly payout: tester {$tester->id} credited KES {$amount} for {$newGames} new games on {$today}. Total today: {$wallet->daily_games_played} games.");

                    $paidDetails[] = [
                        'name' => $tester->name,
                        'email' => $tester->email,
                        'amount' => $amount,
                        'games' => $newGames,
                        'total_games' => $wallet->daily_games_played,
                    ];
                    $totalAmount += $amount;
                    $paid++;
                });

                // Step 5: Check if daily target just reached
                $wallet->refresh();
                if ($wallet->daily_games_played >= 30 && ! $wallet->daily_target_reached) {
                    $wallet->update(['daily_target_reached' => true]);
                    $this->line("Tester {$tester->id} ({$tester->name}) reached daily target! Withdrawals unlocked.");
                    Log::info("Daily target reached: tester {$tester->id} hit {$wallet->daily_games_played} games.");

                    $tester->notify(new DailyTargetReachedNotification($wallet));
                }

            } catch (RequestException|ConnectionException $e) {
                $this->error("Failed to fetch stats for tester {$tester->id} ({$tester->name}): {$e->getMessage()}");
                Log::error("Hourly payout: failed to fetch stats for tester {$tester->id} on {$today} — {$e->getMessage()}");
                $errors++;
            }
        }

        $this->info("Done. Paid: {$paid}, Skipped: {$skipped}, Resets: {$reset}, Errors: {$errors}.");

        // Send summary notification to admins
        if ($paid > 0 || $errors > 0) {
            $notification = new DailyPayoutsSummaryNotification(
                date: $today,
                totalTesters: $testers->count(),
                paidCount: $paid,
                skippedCount: $skipped,
                errorCount: $errors,
                totalAmount: $totalAmount,
                paidDetails: $paidDetails
            );

            AdminNotificationRouter::notifyAdmins($notification);
        }

        return self::SUCCESS;
    }
}
