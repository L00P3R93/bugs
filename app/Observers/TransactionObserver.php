<?php

namespace App\Observers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
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
        try {
            DB::transaction(function () use ($transaction): void {
                $wallet = $transaction->wallet()->lockForUpdate()->first();

                if ($transaction->type === TransactionType::PAYOUT) {
                    $wallet->increment('balance', $transaction->amount);
                    $transaction->status = TransactionStatus::COMPLETED;
                    $transaction->saveQuietly();
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
                    // Transaction stays PENDING until B2C callback confirms the transfer
                }
            });
        } catch (\Exception $e) {
            $transaction->status = TransactionStatus::FAILED;
            $transaction->saveQuietly();

            throw $e;
        }
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
