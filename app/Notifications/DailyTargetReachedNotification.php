<?php

namespace App\Notifications;

use App\Models\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyTargetReachedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Wallet $wallet) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->prefersNotification('daily_target_reached', 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->prefersNotification('daily_target_reached', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Daily Target Reached!')
            ->greeting('Congratulations!')
            ->line("You've played {$this->wallet->daily_games_played} games today and reached your daily target.")
            ->line('Your balance of KES '.number_format($this->wallet->available_balance, 2).' is now available for withdrawal.')
            ->action('Withdraw Now', url('/admin/transactions'))
            ->line('Keep up the great work!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'daily_target_reached',
            'wallet_id' => $this->wallet->id,
            'daily_games_played' => $this->wallet->daily_games_played,
            'available_balance' => $this->wallet->available_balance,
            'icon' => 'heroicon-o-trophy',
            'color' => 'success',
        ];
    }
}
