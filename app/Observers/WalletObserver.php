<?php

namespace App\Observers;

use App\Models\Wallet;
use App\Notifications\WalletLockedNotification;
use Illuminate\Support\Str;

class WalletObserver
{
    public function creating(Wallet $wallet): void
    {
        $wallet->wallet_no = 'BUGW-'.strtoupper(uniqid());
        $wallet->balance = 0;
        $wallet->available_balance = 0;
        $wallet->pending_balance = 0;
        $wallet->total_earned = 0;
    }


    /**
     * Handle the Wallet "created" event.
     */
    public function created(Wallet $wallet): void
    {
        // Initialize balances if not set
        if (is_null($wallet->available_balance)) {
            $wallet->update([
                'available_balance' => $wallet->balance,
                'pending_balance' => 0,
                'total_earned' => 0,
            ]);
        }
    }

    /**
     * Handle the Wallet "updated" event.
     */
    public function updated(Wallet $wallet): void
    {
        if ($wallet->wasChanged('is_locked') && $wallet->is_locked) {
            // Notify user of wallet lock
            $user = $wallet->user;
            if ($user) {
                $user->notify(new WalletLockedNotification($wallet, $wallet->locked_reason ?? 'No reason provided'));
            }
        }
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
