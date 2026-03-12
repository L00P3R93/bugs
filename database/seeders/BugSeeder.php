<?php

namespace Database\Seeders;

use App\Enums\BugStatus;
use App\Models\Bug;
use App\Models\Label;
use App\Models\Transaction;

class BugSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Non-Admin Users
        $this->command->warn(PHP_EOL.'Creating Bugs...');
        $bugs = $this->withProgressBar(100, fn () => collect([Bug::factory()->create()]));
        $bugs->each(function (Bug $bug) {
            $user = $bug->reporter;
            $wallet = $user->wallet;
            $randomLabel = Label::query()->inRandomOrder()->first();
            // Add labels to bugs
            $bug->labels()->attach($randomLabel->id, ['added_by' => $user->id]);

            if (in_array($bug->status, [BugStatus::PAID, BugStatus::FIXED, BugStatus::CLOSED])) {
                // Transactions For each bug
                $transaction = Transaction::query()->create([
                    'wallet_id' => $wallet->id,
                    'bug_id' => $bug->id,
                    'amount' => $bug->final_amount,
                    'type' => 'payout',
                    'status' => 'completed',
                ]);

                // Update wallet balance (for payouts)
                $wallet->increment('balance', $transaction->amount);
            }
        });
        $this->command->info('Created '.count($bugs).' bugs.');
    }
}
