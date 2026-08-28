<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyPayoutsSummaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $date,
        public int $totalTesters,
        public int $paidCount,
        public int $skippedCount,
        public int $errorCount,
        public float $totalAmount,
        public array $paidDetails,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->prefersNotification('daily_payouts_summary', 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->prefersNotification('daily_payouts_summary', 'email')) {
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
            ->subject("Daily Payouts Summary - {$this->date}")
            ->greeting("Daily Payouts Report for {$this->date}")
            ->line("Total testers processed: {$this->totalTesters}")
            ->line("Successfully paid: {$this->paidCount}")
            ->line("Skipped: {$this->skippedCount}")
            ->line("Errors: {$this->errorCount}")
            ->line('Total amount disbursed: KES '.number_format($this->totalAmount, 2))
            ->action('View Transactions', url('/admin/transactions'))
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
            'type' => 'daily_payouts_summary',
            'date' => $this->date,
            'total_testers' => $this->totalTesters,
            'paid_count' => $this->paidCount,
            'skipped_count' => $this->skippedCount,
            'error_count' => $this->errorCount,
            'total_amount' => $this->totalAmount,
            'paid_details' => $this->paidDetails,
            'icon' => 'heroicon-o-currency-dollar',
            'color' => 'success',
        ];
    }
}
