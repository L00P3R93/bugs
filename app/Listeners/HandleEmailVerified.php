<?php

namespace App\Listeners;

use App\Jobs\ProcessVerifiedUser;
use Illuminate\Auth\Events\Verified;

class HandleEmailVerified
{
    public function handle(Verified $event): void
    {
        ProcessVerifiedUser::dispatch($event->user);
    }
}
