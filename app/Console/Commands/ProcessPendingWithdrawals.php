<?php

namespace App\Console\Commands;

use App\Enums\TransactionStatus;
use App\Models\Withdraw;
use App\Mpesa\Init as MPESA;
use App\Notifications\WithdrawalFailedNotification;
use App\Notifications\WithdrawalsSummaryNotification;
use App\Services\AdminNotificationRouter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPendingWithdrawals extends Command
{
    protected $signature = 'withdrawals:process-pending';

    protected $description = 'Process one pending withdrawal per minute via M-Pesa B2C';

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
            $responseJson = MPESA::b2c($userParams);

            $response = json_decode($responseJson, true);

            if (! is_array($response)) {
                Log::channel('mpesa')->error("B2C invalid response for withdrawal {$withdraw->id}: {$responseJson}");
                $withdraw->status = TransactionStatus::FAILED;
                $withdraw->failure_reason = 'Invalid API response';
                $withdraw->saveQuietly();

                $failedCount++;
                $details[] = [
                    'user' => $withdraw->wallet->user->name,
                    'phone' => $withdraw->phone,
                    'amount' => $withdraw->amount,
                    'status' => 'failed',
                    'reason' => 'Invalid API response',
                ];

                // Notify user of failure
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

                // Notify user of failure
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
