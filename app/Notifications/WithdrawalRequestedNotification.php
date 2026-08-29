<?php

namespace App\Notifications;

use App\Models\Withdraw;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Withdraw $withdraw) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->prefersNotification('withdrawal_requested', 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->prefersNotification('withdrawal_requested', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Withdrawal Request: KES '.number_format($this->withdraw->amount, 2))
            ->greeting('New Withdrawal Request')
            ->line("Tester: {$this->withdraw->wallet->user->name}")
            ->line('Amount: KES '.number_format($this->withdraw->amount, 2))
            ->line("Phone: {$this->withdraw->phone}")
            ->line("Transaction: {$this->withdraw->transaction->transaction_no}")
            ->action('Review Withdrawal', url("/admin/transactions/{$this->withdraw->transaction_id}"))
            ->line('Please review and approve or reject this withdrawal request.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'withdrawal_requested',
            'withdraw_id' => $this->withdraw->id,
            'transaction_id' => $this->withdraw->transaction_id,
            'user_name' => $this->withdraw->wallet->user->name,
            'amount' => $this->withdraw->amount,
            'phone' => $this->withdraw->phone,
            'transaction_no' => $this->withdraw->transaction->transaction_no,
            'icon' => 'heroicon-o-clock',
            'color' => 'warning',
        ];
    }
}
