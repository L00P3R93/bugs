<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Transaction $transaction) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->prefersNotification('payment_received', 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->prefersNotification('payment_received', 'email')) {
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
            ->subject('Payment Received: KES '.number_format($this->transaction->amount, 2))
            ->greeting('Payment Received!')
            ->line("Transaction Number: {$this->transaction->transaction_no}")
            ->line('Amount: KES '.number_format($this->transaction->amount, 2))
            ->line('Bug: '.$this->transaction->bug->bug_no)
            ->line('New Wallet Balance: KES '.number_format($this->transaction->wallet->balance, 2))
            ->action('View Transaction', url("/admin/transactions/{$this->transaction->id}"))
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
            'type' => 'payment_received',
            'transaction_id' => $this->transaction->id,
            'transaction_no' => $this->transaction->transaction_no,
            'amount' => $this->transaction->amount,
            'bug_no' => $this->transaction->bug->bug_no ?? null,
            'wallet_balance' => $this->transaction->wallet->balance,
            'icon' => 'heroicon-o-currency-dollar',
            'color' => 'success',
        ];
    }
}
