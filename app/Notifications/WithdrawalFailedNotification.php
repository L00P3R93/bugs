<?php

namespace App\Notifications;

use App\Models\Withdraw;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Withdraw $withdraw) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->prefersNotification('withdrawal_failed', 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->prefersNotification('withdrawal_failed', 'email')) {
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
            ->subject('Withdrawal Failed: KES '.number_format($this->withdraw->amount, 2))
            ->greeting('Withdrawal Failed')
            ->line('Amount: KES '.number_format($this->withdraw->amount, 2))
            ->line("Phone: {$this->withdraw->phone}")
            ->line("Reason: {$this->withdraw->failure_reason}")
            ->line('Your wallet has been refunded.')
            ->action('View Transaction', url("/admin/transactions/{$this->withdraw->transaction_id}"))
            ->line('Please contact support if you need assistance.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'withdrawal_failed',
            'withdraw_id' => $this->withdraw->id,
            'amount' => $this->withdraw->amount,
            'phone' => $this->withdraw->phone,
            'failure_reason' => $this->withdraw->failure_reason,
            'icon' => 'heroicon-o-x-circle',
            'color' => 'danger',
        ];
    }
}
