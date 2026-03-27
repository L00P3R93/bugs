<?php

namespace App\Notifications;

use App\Filament\Resources\Bugs\BugResource;
use App\Models\Bug;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Kirschbaum\Commentions\Comment;

class BugCommentMentionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Comment $comment,
        protected array $channels
    ) {}

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toArray(object $notifiable): array
    {
        $bug = $this->comment->commentable;
        $url = $bug instanceof Bug
            ? BugResource::getUrl('view', ['record' => $bug])
            : null;

        return [
            'title' => 'You were mentioned in a comment',
            'body' => $this->comment->getAuthorName().' mentioned you: '.strip_tags($this->comment->getBody()),
            'icon' => 'heroicon-o-at-symbol',
            'iconColor' => 'info',
            'actions' => $url ? [['label' => 'View bug', 'url' => $url]] : [],
        ];
    }
}
