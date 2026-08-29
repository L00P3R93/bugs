<?php

namespace App\Notifications;

use App\Models\Withdraw;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Withdraw $withdraw, public string $reason) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->prefersNotification('withdrawal_rejected', 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->prefersNotification('withdrawal_rejected', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Withdrawal Rejected: KES '.number_format($this->withdraw->amount, 2))
            ->greeting('Withdrawal Rejected')
            ->line('Amount: KES '.number_format($this->withdraw->amount, 2))
            ->line("Transaction: {$this->withdraw->transaction->transaction_no}")
            ->line("Reason: {$this->reason}")
            ->action('View Transaction', url("/admin/transactions/{$this->withdraw->transaction_id}"))
            ->line('If you believe this is an error, please contact support.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'withdrawal_rejected',
            'withdraw_id' => $this->withdraw->id,
            'amount' => $this->withdraw->amount,
            'transaction_no' => $this->withdraw->transaction->transaction_no,
            'rejection_reason' => $this->reason,
            'icon' => 'heroicon-o-x-circle',
            'color' => 'danger',
        ];
    }
}
