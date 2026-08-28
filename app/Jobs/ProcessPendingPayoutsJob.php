<?php

namespace App\Jobs;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Services\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPendingPayoutsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(WalletService $walletService): void
    {
        // Find all pending payouts older than 7 days
        $pendingPayouts = Transaction::where('type', TransactionType::PAYOUT)
            ->where('status', TransactionStatus::PENDING)
            ->where('created_at', '<=', now()->subDays(7))
            ->get();

        foreach ($pendingPayouts as $transaction) {
            $walletService->releasePayout($transaction);
        }
    }
}
