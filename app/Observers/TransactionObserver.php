<?php

namespace App\Observers;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Withdraw;

class TransactionObserver
{
    /**
     * @throws \Exception
     */
    public function creating(Transaction $transaction): void
    {
        // Generate unique account number if not already set
        if (! $transaction->transaction_no) {
            do {
                $transactionNo = 'TRS'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            } while (Transaction::query()->where('transaction_no', $transactionNo)->exists());

            $transaction->transaction_no = $transactionNo;
        }
        $transaction->status = 'pending';

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
     */
    public function created(Transaction $transaction): void
    {
        $wallet = $transaction->wallet;
        if ($transaction->type === TransactionType::PAYOUT) {
            $wallet->increment('balance', $transaction->amount);
        } elseif ($transaction->type === TransactionType::WITHDRAW) {
            if ($wallet->balance < $transaction->amount) {
                throw new \Exception('Insufficient balance');
            }
            $wallet->decrement('balance', $transaction->amount);
            $user = $wallet->user;
            Withdraw::query()->create([
                'transaction_id' => $transaction->id,
                'wallet_id' => $wallet->id,
                'phone' => $user->phone,
                'amount' => $transaction->amount,
                'balance' => $wallet->balance,
            ]);
        }
        $transaction->status = 'completed';
        $transaction->saveQuietly();
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        //
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
