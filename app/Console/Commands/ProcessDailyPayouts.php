<?php

namespace App\Console\Commands;

use App\Enums\TransactionType;
use App\Facades\KadiApi;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\DailyPayoutsSummaryNotification;
use App\Services\AdminNotificationRouter;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class ProcessDailyPayouts extends Command
{
    protected $signature = 'payouts:process-daily';

    protected $description = 'Pay testers who played 30 or more games the previous day (5 KES per game)';

    public function handle(): int
    {
        $date = now()->timezone('Africa/Nairobi')->subDay()->toDateString();
        $this->info("Processing daily payouts for {$date}...");

        $testers = User::query()
            ->role('Tester')
            ->whereNotNull('linked_id')
            ->with('wallet')
            ->get();

        $paid = 0;
        $skipped = 0;
        $errors = 0;
        $paidDetails = [];
        $totalAmount = 0;

        foreach ($testers as $tester) {
            if (! $tester->wallet) {
                $this->warn("Tester {$tester->id} ({$tester->name}) has no wallet — skipping.");
                $skipped++;

                continue;
            }

            try {
                $stats = KadiApi::getPlayerStats($tester->linked_id, $date);
                $total = (int) ($stats['total'] ?? 0);

                if ($total < 30) {
                    $skipped++;

                    continue;
                }

                $amount = $total * 5;

                Transaction::query()->create([
                    'wallet_id' => $tester->wallet->id,
                    'type' => TransactionType::PAYOUT,
                    'amount' => $amount,
                ]);

                $this->line("Paid tester {$tester->id} ({$tester->name}): KES {$amount} for {$total} games.");
                Log::info("Daily payout: tester {$tester->id} credited KES {$amount} for {$total} games on {$date}.");

                $paidDetails[] = [
                    'name' => $tester->name,
                    'email' => $tester->email,
                    'amount' => $amount,
                    'games' => $total,
                ];
                $totalAmount += $amount;
                $paid++;
            } catch (RequestException|ConnectionException $e) {
                $this->error("Failed to fetch stats for tester {$tester->id} ({$tester->name}): {$e->getMessage()}");
                Log::error("Daily payout: failed to fetch stats for tester {$tester->id} on {$date} — {$e->getMessage()}");
                $errors++;
            }
        }

        $this->info("Done. Paid: {$paid}, Skipped: {$skipped}, Errors: {$errors}.");

        // Send summary notification to admins
        if ($paid > 0 || $errors > 0) {
            $notification = new DailyPayoutsSummaryNotification(
                date: $date,
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
