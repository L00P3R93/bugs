<?php

namespace App\Observers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\Withdraw;
use Illuminate\Support\Facades\DB;

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

        $transaction->status = TransactionStatus::PENDING;

        if ($transaction->type === TransactionType::WITHDRAW) {
            $wallet = $transaction->wallet;
            if ($wallet->balance < $transaction->amount) {
                throw new \Exception('Insufficient balance');
            }
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

        try {
            DB::transaction(function () use ($transaction): void {
                $wallet = $transaction->wallet()->lockForUpdate()->first();

                if ($transaction->type === TransactionType::PAYOUT) {
                    // Hold payout for 7-day pending period
                    $wallet->holdPayout($transaction->net_amount);
                    $transaction->status = TransactionStatus::PENDING;
                    $transaction->saveQuietly();
                } elseif ($transaction->type === TransactionType::WITHDRAW) {
                    if ($wallet->balance < $transaction->amount) {
                        throw new \Exception('Insufficient balance');
                    }
                    $wallet->decrement('balance', $transaction->amount);
                    $wallet->decrement('available_balance', $transaction->amount);
                    $user = $wallet->user;
                    Withdraw::query()->create([
                        'transaction_id' => $transaction->id,
                        'wallet_id' => $wallet->id,
                        'phone' => $user->phone,
                        'amount' => $transaction->amount,
                        'balance' => $wallet->balance,
                    ]);
                    // Transaction stays PENDING until B2C callback confirms the transfer
                }
            });
        } catch (\Exception $e) {
            $transaction->status = TransactionStatus::FAILED;
            $transaction->cancelled_at = now();
            $transaction->saveQuietly();

            // Log failure
            TransactionLog::create([
                'transaction_id' => $transaction->id,
                'action' => 'failed',
                'previous_status' => TransactionStatus::PENDING->value,
                'new_status' => TransactionStatus::FAILED->value,
                'details' => ['error' => $e->getMessage()],
                'performed_by' => auth()->id(),
            ]);

            throw $e;
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
