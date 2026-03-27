<?php

namespace App\Notifications;

use App\Filament\Resources\Bugs\BugResource;
use App\Models\Bug;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Kirschbaum\Commentions\Comment;

class BugCommentSubscriptionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Comment $comment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $bug = $this->comment->commentable;
        $url = $bug instanceof Bug
            ? BugResource::getUrl('view', ['record' => $bug])
            : null;

        return [
            'title' => 'New comment on a bug you follow',
            'body' => $this->comment->getAuthorName().' commented: '.strip_tags($this->comment->getBody()),
            'icon' => 'heroicon-o-chat-bubble-left-ellipsis',
            'iconColor' => 'success',
            'actions' => $url ? [['label' => 'View bug', 'url' => $url]] : [],
        ];
    }
}
