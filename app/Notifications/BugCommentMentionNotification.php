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

class BugCommentMentionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Comment $comment,
        protected array $channels
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->prefersNotification('bug_mention', 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->prefersNotification('bug_mention', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $bug = $this->comment->commentable;

        return (new MailMessage)
            ->subject('You were mentioned in a comment')
            ->greeting('You were mentioned!')
            ->line($this->comment->getAuthorName().' mentioned you in a comment on bug '.$bug->bug_no)
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
            'title' => 'You were mentioned in a comment',
            'body' => $this->comment->getAuthorName().' mentioned you: '.strip_tags($this->comment->getBody()),
            'icon' => 'heroicon-o-at-symbol',
            'iconColor' => 'info',
            'actions' => $url ? [['label' => 'View bug', 'url' => $url]] : [],
        ];
    }
}
