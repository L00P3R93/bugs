<?php

namespace App\Console\Commands;

use App\Enums\TransactionStatus;
use App\Exceptions\MpesaApiException;
use App\Models\Withdraw;
use App\Notifications\WithdrawalFailedNotification;
use App\Notifications\WithdrawalsSummaryNotification;
use App\Services\AdminNotificationRouter;
use App\Services\MpesaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPendingWithdrawals extends Command
{
    protected $signature = 'withdrawals:process-pending';

    protected $description = 'Process one pending withdrawal per minute via M-Pesa B2C';

    public function __construct(
        private readonly MpesaService $mpesa,
    ) {
        parent::__construct();
    }

    /**
     * @throws \Throwable
     */
    public function handle(): int
    {
        $processedCount = 0;
        $successCount = 0;
        $failedCount = 0;
        $totalAmount = 0;
        $details = [];

        DB::transaction(function () use (&$processedCount, &$successCount, &$failedCount, &$totalAmount, &$details) {
            /** @var Withdraw|null $withdraw */
            $withdraw = Withdraw::query()
                ->where('status', TransactionStatus::PENDING)
                ->where('amount', '>=', 10)
                ->whereNotNull('phone')
                ->whereNotNull('wallet_id')
                ->whereNotNull('transaction_id')
                ->whereNull('conversation_id')
                ->orderBy('created_at')
                ->lockForUpdate()
                ->first();

            if (! $withdraw) {
                $this->info('No pending withdrawals to process.');

                return;
            }

            $this->info("Processing withdrawal ID {$withdraw->id} — KES {$withdraw->amount} to {$withdraw->phone}.");

            $userParams = [
                'Amount' => (int) $withdraw->amount,
                'PartyB' => $withdraw->phone,
                'Remarks' => 'Withdrawal '.$withdraw->transaction->transaction_no,
                'Occasion' => '',
            ];

            try {
                $response = $this->mpesa->b2c($userParams);
            } catch (MpesaApiException $e) {
                Log::channel('mpesa')->error("B2C request failed for withdrawal {$withdraw->id}: {$e->getMessage()}");
                $withdraw->status = TransactionStatus::FAILED;
                $withdraw->failure_reason = $e->getMessage();
                $withdraw->saveQuietly();

                $failedCount++;
                $details[] = [
                    'user' => $withdraw->wallet->user->name,
                    'phone' => $withdraw->phone,
                    'amount' => $withdraw->amount,
                    'status' => 'failed',
                    'reason' => $e->getMessage(),
                ];

                $withdraw->wallet->user->notify(
                    new WithdrawalFailedNotification($withdraw)
                );

                return;
            }

            $responseCode = $response['ResponseCode'] ?? null;

            $withdraw->response_code = $responseCode;
            $withdraw->response_message = $response['ResponseDescription'] ?? null;

            if ($responseCode == '0') {
                $withdraw->conversation_id = $response['ConversationID'] ?? null;
                Log::channel('mpesa')->info("B2C submitted for withdrawal {$withdraw->id}. ConversationID: {$withdraw->conversation_id}");
                $successCount++;
                $details[] = [
                    'user' => $withdraw->wallet->user->name,
                    'phone' => $withdraw->phone,
                    'amount' => $withdraw->amount,
                    'status' => 'submitted',
                    'conversation_id' => $withdraw->conversation_id,
                ];
            } else {
                $withdraw->status = TransactionStatus::FAILED;
                $withdraw->failure_reason = $withdraw->response_message;
                Log::channel('mpesa')->error("B2C failed for withdrawal {$withdraw->id}. Code: {$responseCode} — {$withdraw->response_message}");
                $failedCount++;
                $details[] = [
                    'user' => $withdraw->wallet->user->name,
                    'phone' => $withdraw->phone,
                    'amount' => $withdraw->amount,
                    'status' => 'failed',
                    'reason' => $withdraw->response_message,
                ];

                $withdraw->wallet->user->notify(
                    new WithdrawalFailedNotification($withdraw)
                );
            }

            $totalAmount += $withdraw->amount;
            $processedCount++;
            $withdraw->saveQuietly();
        });

        // Send summary notification to admins
        if ($processedCount > 0) {
            $notification = new WithdrawalsSummaryNotification(
                processedCount: $processedCount,
                successCount: $successCount,
                failedCount: $failedCount,
                totalAmount: $totalAmount,
                details: $details
            );

            AdminNotificationRouter::notifyAdmins($notification);
        }

        return self::SUCCESS;
    }
}
