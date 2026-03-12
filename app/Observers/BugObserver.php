<?php

namespace App\Observers;

use App\Models\Bug;

class BugObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(Bug $bug): void
    {
        // Generate unique bug number if not already set
        if (! $bug->bug_no) {
            do {
                $bugNo = 'BUG'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            } while (Bug::query()->where('bug_no', $bugNo)->exists());

            $bug->bug_no = $bugNo;
        } else {
            $bugNo = $bug->bug_no;
        }

        // Only set reporter_id and remarks if not seeding and user is authenticated
        if (! app()->runningInConsole() || app()->environment('testing')) {
            $user = auth()->user();
            if ($user) {
                $bug->reporter_id = $user->id;
            }
        }
    }

    /**
     * Handle the Bug "created" event.
     */
    public function created(Bug $bug): void
    {
        $bug->base_amount = $bug->getBaseAmount();
        $bug->final_amount = $bug->getFinalAmount();
        $reporterName = $bug->reporter->name;
        $bug->remarks = "Bug [{$bug->bug_no}] submitted by {$reporterName} on ".now()->toDateTimeString();
        $bug->saveQuietly();
    }

    /**
     * Handle the Bug "updated" event.
     */
    public function updated(Bug $bugs): void
    {
        //
    }

    /**
     * Handle the Bug "deleted" event.
     */
    public function deleted(Bug $bugs): void
    {
        //
    }

    /**
     * Handle the Bug "restored" event.
     */
    public function restored(Bug $bugs): void
    {
        //
    }

    /**
     * Handle the Bug "force deleted" event.
     */
    public function forceDeleted(Bug $bugs): void
    {
        //
    }
}
