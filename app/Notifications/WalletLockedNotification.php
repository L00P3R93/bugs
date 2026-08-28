<?php

namespace App\Notifications;

use App\Models\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletLockedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Wallet $wallet,
        public string $reason
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->prefersNotification('wallet_locked', 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->prefersNotification('wallet_locked', 'email')) {
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
            ->subject('Your Wallet Has Been Locked')
            ->greeting('Your wallet has been locked')
            ->line("Reason: {$this->reason}")
            ->line('Please contact support for assistance.')
            ->action('Contact Support', url('/support'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'wallet_locked',
            'wallet_id' => $this->wallet->id,
            'wallet_no' => $this->wallet->wallet_no,
            'reason' => $this->reason,
            'icon' => 'heroicon-o-lock-closed',
            'color' => 'danger',
        ];
    }
}
