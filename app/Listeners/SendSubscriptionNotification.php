<?php

namespace App\Listeners;

use App\Notifications\BugCommentSubscriptionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Kirschbaum\Commentions\Events\UserIsSubscribedToCommentableEvent;

class SendSubscriptionNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserIsSubscribedToCommentableEvent $event): void
    {
        $event->user->notify(new BugCommentSubscriptionNotification($event->comment));
    }
}
