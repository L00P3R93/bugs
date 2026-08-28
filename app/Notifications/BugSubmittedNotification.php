<?php

namespace App\Notifications;

use App\Models\Bug;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BugSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Bug $bug) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->prefersNotification('bug_submitted', 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->prefersNotification('bug_submitted', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Bug Submitted: {$this->bug->bug_no}")
            ->greeting('New Bug Report')
            ->line("Bug Number: {$this->bug->bug_no}")
            ->line("Title: {$this->bug->title}")
            ->line("Reporter: {$this->bug->reporter->name}")
            ->line("Category: {$this->bug->category->name}")
            ->line("Severity: {$this->bug->severity->name}")
            ->line('Potential Payout: KES '.number_format($this->bug->final_amount, 2))
            ->action('View Bug', url("/admin/bugs/{$this->bug->id}"))
            ->line('Please review this bug report.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'bug_submitted',
            'bug_id' => $this->bug->id,
            'bug_no' => $this->bug->bug_no,
            'title' => $this->bug->title,
            'reporter' => $this->bug->reporter->name,
            'category' => $this->bug->category->name,
            'severity' => $this->bug->severity->name,
            'final_amount' => $this->bug->final_amount,
            'icon' => 'heroicon-o-bug-ant',
            'color' => 'info',
        ];
    }
}
