<?php

namespace App\Notifications;

use App\Models\Withdraw;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Withdraw $withdraw) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->prefersNotification('withdrawal_approved', 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->prefersNotification('withdrawal_approved', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Withdrawal Approved: KES '.number_format($this->withdraw->amount, 2))
            ->greeting('Withdrawal Approved!')
            ->line('Amount: KES '.number_format($this->withdraw->amount, 2))
            ->line("Phone: {$this->withdraw->phone}")
            ->line("Transaction: {$this->withdraw->transaction->transaction_no}")
            ->action('View Transaction', url("/admin/transactions/{$this->withdraw->transaction_id}"))
            ->line('Your withdrawal is now being processed via M-Pesa.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'withdrawal_approved',
            'withdraw_id' => $this->withdraw->id,
            'amount' => $this->withdraw->amount,
            'phone' => $this->withdraw->phone,
            'transaction_no' => $this->withdraw->transaction->transaction_no,
            'icon' => 'heroicon-o-check-circle',
            'color' => 'success',
        ];
    }
}
