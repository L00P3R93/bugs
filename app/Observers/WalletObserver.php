<?php

namespace App\Observers;

use App\Models\Wallet;

class WalletObserver
{
    public function creating(Wallet $wallet): void
    {
        // Generate unique account number if not already set
        if (! $wallet->wallet_no) {
            do {
                $walletNo = 'WT'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            } while (Wallet::query()->where('wallet_no', $walletNo)->exists());

            $wallet->wallet_no = $walletNo;
        }
        $wallet->balance = 0;
        $wallet->status = 'active';
    }

    /**
     * Handle the Wallet "created" event.
     */
    public function created(Wallet $wallet): void
    {
        //
    }

    /**
     * Handle the Wallet "updated" event.
     */
    public function updated(Wallet $wallet): void
    {
        //
    }

    /**
     * Handle the Wallet "deleted" event.
     */
    public function deleted(Wallet $wallet): void
    {
        //
    }

    /**
     * Handle the Wallet "restored" event.
     */
    public function restored(Wallet $wallet): void
    {
        //
    }

    /**
     * Handle the Wallet "force deleted" event.
     */
    public function forceDeleted(Wallet $wallet): void
    {
        //
    }
}
