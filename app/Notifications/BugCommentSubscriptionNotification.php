<?php

namespace App\Notifications;

use App\Filament\Resources\Bugs\BugResource;
use App\Models\Bug;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Kirschbaum\Commentions\Comment;

class BugCommentSubscriptionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Comment $comment
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->prefersNotification('bug_comment', 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->prefersNotification('bug_comment', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $bug = $this->comment->commentable;

        return (new MailMessage)
            ->subject("New comment on bug {$bug->bug_no}")
            ->greeting('New Comment')
            ->line($this->comment->getAuthorName().' commented on a bug you\'re subscribed to')
            ->line('Comment: '.Str::limit(strip_tags($this->comment->getBody()), 200))
            ->action('View Comment', url("/admin/bugs/{$bug->id}"))
            ->line('Thank you for using our platform!');
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
