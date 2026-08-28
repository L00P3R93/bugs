<?php

namespace App\Notifications;

use App\Models\Withdraw;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalCompletedNotification extends Notification implements ShouldQueue
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

        if ($notifiable->prefersNotification('withdrawal_completed', 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->prefersNotification('withdrawal_completed', 'email')) {
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
            ->subject('Withdrawal Completed: KES '.number_format($this->withdraw->amount, 2))
            ->greeting('Withdrawal Successful!')
            ->line('Amount: KES '.number_format($this->withdraw->amount, 2))
            ->line("Phone: {$this->withdraw->phone}")
            ->line("Transaction: {$this->withdraw->transaction->transaction_no}")
            ->line("Reference: {$this->withdraw->transaction_ref}")
            ->action('View Transaction', url("/admin/transactions/{$this->withdraw->transaction_id}"))
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
            'type' => 'withdrawal_completed',
            'withdraw_id' => $this->withdraw->id,
            'amount' => $this->withdraw->amount,
            'phone' => $this->withdraw->phone,
            'transaction_no' => $this->withdraw->transaction->transaction_no,
            'transaction_ref' => $this->withdraw->transaction_ref,
            'icon' => 'heroicon-o-check-circle',
            'color' => 'success',
        ];
    }
}
