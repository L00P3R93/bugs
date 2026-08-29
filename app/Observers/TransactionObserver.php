<?php

namespace App\Observers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\Withdraw;
use App\Notifications\WithdrawalRequestedNotification;
use App\Services\AdminNotificationRouter;

class TransactionObserver
{
    /**
     * @throws \Exception
     */
    public function creating(Transaction $transaction): void
    {
        if (! $transaction->transaction_no) {
            do {
                $transactionNo = 'TRS'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            } while (Transaction::query()->where('transaction_no', $transactionNo)->exists());

            $transaction->transaction_no = $transactionNo;
        }

        // Set user_id from wallet if not set
        if (! $transaction->user_id && $transaction->wallet_id) {
            $transaction->user_id = $transaction->wallet->user_id;
        }

        // Calculate net amount if not set
        if (is_null($transaction->net_amount)) {
            $transaction->net_amount = $transaction->amount - ($transaction->fee_amount ?? 0);
        }

        // Set default currency
        if (! $transaction->currency) {
            $transaction->currency = 'KES';
        }

        // WITHDRAW requests go to pending_approval for admin review
        if ($transaction->type === TransactionType::WITHDRAW) {
            $transaction->status = TransactionStatus::PENDING_APPROVAL;

            $wallet = $transaction->wallet;
            if ($wallet->balance < $transaction->amount) {
                throw new \Exception('Insufficient balance');
            }
        } else {
            $transaction->status = TransactionStatus::PENDING;
        }
    }

    /**
     * Handle the Transaction "created" event.
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function created(Transaction $transaction): void
    {
        // Log creation
        TransactionLog::create([
            'transaction_id' => $transaction->id,
            'action' => 'created',
            'new_status' => $transaction->status->value,
            'details' => [
                'amount' => $transaction->amount,
                'type' => $transaction->type->value,
            ],
            'performed_by' => auth()->id(),
        ]);

        // PAYOUT transactions are handled directly by ProcessDailyPayouts command
        // WITHDRAW transactions: create Withdraw record in pending_approval state
        if ($transaction->type === TransactionType::WITHDRAW) {
            $wallet = $transaction->wallet;
            $user = $wallet->user;

            $withdraw = Withdraw::query()->create([
                'transaction_id' => $transaction->id,
                'wallet_id' => $wallet->id,
                'phone' => $user->phone,
                'amount' => $transaction->amount,
                'balance' => $wallet->balance,
                'status' => TransactionStatus::PENDING_APPROVAL,
            ]);

            // Notify admins of new withdrawal request
            AdminNotificationRouter::notifyAdmins(new WithdrawalRequestedNotification($withdraw));
        }
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        if ($transaction->wasChanged('status')) {
            $previousStatus = $transaction->getOriginal('status');

            TransactionLog::create([
                'transaction_id' => $transaction->id,
                'action' => 'status_changed',
                'previous_status' => $previousStatus,
                'new_status' => $transaction->status->value,
                'performed_by' => auth()->id(),
            ]);

            // Handle status-specific logic
            match ($transaction->status) {
                TransactionStatus::COMPLETED => $transaction->update(['completed_at' => now()]),
                TransactionStatus::FAILED => $transaction->update(['cancelled_at' => now()]),
                default => null,
            };
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}
