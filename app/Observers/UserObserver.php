<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        // Generate unique account number if not already set
        if (! $user->account_no) {
            do {
                $accountNo = 'ACC-'.strtoupper(uniqid());
            } while (User::where('account_no', $accountNo)->exists());

            $user->account_no = $accountNo;
        }
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Once user is created and email is verified, create a wallet for them
        $user->wallet()->create();
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
