<?php

namespace App\Notifications;

use App\Enums\BugStatus;
use App\Models\Bug;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BugStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Bug $bug,
        public string $oldStatus,
        public string $newStatus,
        public string $actor,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->prefersNotification('bug_status_changed', 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->prefersNotification('bug_status_changed', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $newStatusLabel = BugStatus::from($this->newStatus)?->getLabel() ?? $this->newStatus;

        return (new MailMessage)
            ->subject("Bug Status Updated: {$this->bug->bug_no}")
            ->greeting('Your bug report status has been updated')
            ->line("Bug Number: {$this->bug->bug_no}")
            ->line("Title: {$this->bug->title}")
            ->line("Status: {$newStatusLabel}")
            ->line("Updated by: {$this->actor}")
            ->action('View Bug', url("/admin/bugs/{$this->bug->id}"))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'bug_status_changed',
            'bug_id' => $this->bug->id,
            'bug_no' => $this->bug->bug_no,
            'title' => $this->bug->title,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'actor' => $this->actor,
            'icon' => 'heroicon-o-arrow-path',
            'color' => 'warning',
        ];
    }
}
