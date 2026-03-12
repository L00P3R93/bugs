<?php

namespace App\Observers;

use App\Models\Label;

class LabelObserver
{
    /**
     * Handle the Label "created" event.
     */
    public function created(Label $label): void
    {
        //
    }

    /**
     * Handle the Label "updated" event.
     */
    public function updated(Label $label): void
    {
        //
    }

    /**
     * Handle the Label "deleted" event.
     */
    public function deleted(Label $label): void
    {
        //
    }

    /**
     * Handle the Label "restored" event.
     */
    public function restored(Label $label): void
    {
        //
    }

    /**
     * Handle the Label "force deleted" event.
     */
    public function forceDeleted(Label $label): void
    {
        //
    }
}
