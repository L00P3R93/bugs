<?php

namespace App\Observers;

use App\Models\Severity;

class SeverityObserver
{
    /**
     * Handle the Severity "created" event.
     */
    public function created(Severity $severity): void
    {
        //
    }

    /**
     * Handle the Severity "updated" event.
     */
    public function updated(Severity $severity): void
    {
        //
    }

    /**
     * Handle the Severity "deleted" event.
     */
    public function deleted(Severity $severity): void
    {
        //
    }

    /**
     * Handle the Severity "restored" event.
     */
    public function restored(Severity $severity): void
    {
        //
    }

    /**
     * Handle the Severity "force deleted" event.
     */
    public function forceDeleted(Severity $severity): void
    {
        //
    }
}
