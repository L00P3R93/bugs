<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalsSummaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public int $processedCount,
        public int $successCount,
        public int $failedCount,
        public float $totalAmount,
        public array $details,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->prefersNotification('withdrawals_summary', 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->prefersNotification('withdrawals_summary', 'email')) {
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
            ->subject('Withdrawals Processing Summary')
            ->greeting('Withdrawals Processing Report')
            ->line("Total processed: {$this->processedCount}")
            ->line("Successful: {$this->successCount}")
            ->line("Failed: {$this->failedCount}")
            ->line('Total amount: KES '.number_format($this->totalAmount, 2))
            ->action('View Withdrawals', url('/admin/withdrawals'))
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
            'type' => 'withdrawals_summary',
            'processed_count' => $this->processedCount,
            'success_count' => $this->successCount,
            'failed_count' => $this->failedCount,
            'total_amount' => $this->totalAmount,
            'details' => $this->details,
            'icon' => 'heroicon-o-banknotes',
            'color' => 'success',
        ];
    }
}
