<?php

namespace App\Observers;

use App\Enums\TransactionStatus;
use App\Models\Withdraw;
use App\Notifications\WithdrawalCompletedNotification;
use App\Notifications\WithdrawalFailedNotification;
use App\Services\AdminNotificationRouter;

class WithdrawObserver
{
    /**
     * Handle the Withdraw "created" event.
     */
    public function created(Withdraw $withdraw): void
    {
        //
    }

    /**
     * Handle the Withdraw "updated" event.
     */
    public function updated(Withdraw $withdraw): void
    {
        if ($withdraw->wasChanged('status')) {
            $user = $withdraw->wallet?->user;

            if ($withdraw->status === TransactionStatus::COMPLETED) {
                // Notify user of successful withdrawal
                if ($user) {
                    $notification = new WithdrawalCompletedNotification($withdraw);
                    $user->notify($notification);
                }
            } elseif ($withdraw->status === TransactionStatus::FAILED) {
                // Notify user of failed withdrawal
                if ($user) {
                    $notification = new WithdrawalFailedNotification($withdraw);
                    $user->notify($notification);
                }

                // Notify admins of failure
                $adminNotification = new WithdrawalFailedNotification($withdraw);
                AdminNotificationRouter::notifyAdmins($adminNotification);
            }
        }
    }

    /**
     * Handle the Withdraw "deleted" event.
     */
    public function deleted(Withdraw $withdraw): void
    {
        //
    }

    /**
     * Handle the Withdraw "restored" event.
     */
    public function restored(Withdraw $withdraw): void
    {
        //
    }

    /**
     * Handle the Withdraw "force deleted" event.
     */
    public function forceDeleted(Withdraw $withdraw): void
    {
        //
    }
}
