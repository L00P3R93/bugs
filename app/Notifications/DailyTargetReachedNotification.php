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

    public function __construct(
        public Wallet $wallet,
        public array $targetTypes = [],
    ) {}

    private const TARGET_LABELS = [
        'daily_2p_games_target_reached' => '2 Player Games',
        'daily_3p_games_target_reached' => '3 Player Games',
        'daily_4p_games_target_reached' => '4 Player Games',
        'daily_tournament_target_reached' => 'Tournaments',
        'daily_jackpot_target_reached' => 'Jackpots',
        'daily_target_reached' => 'Daily Target',
    ];

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
        $labels = collect($this->targetTypes)
            ->map(fn (string $type) => self::TARGET_LABELS[$type] ?? $type)
            ->implode(', ');

        return (new MailMessage)
            ->subject('Daily Target Reached!')
            ->greeting('Congratulations!')
            ->line("You've reached your {$labels} target with {$this->wallet->daily_games_played} games today.")
            ->line('Your balance of KES '.number_format($this->wallet->available_balance, 2).' is now available for withdrawal.')
            ->action('Withdraw Now', url('/admin/transactions'))
            ->line('Keep up the great work!');
    }

    public function toArray(object $notifiable): array
    {
        $labels = collect($this->targetTypes)
            ->map(fn (string $type) => self::TARGET_LABELS[$type] ?? $type)
            ->implode(', ');

        return [
            'type' => 'daily_target_reached',
            'wallet_id' => $this->wallet->id,
            'targets_reached' => $this->targetTypes,
            'labels' => $labels,
            'daily_games_played' => $this->wallet->daily_games_played,
            'available_balance' => $this->wallet->available_balance,
            'icon' => 'heroicon-o-trophy',
            'color' => 'success',
        ];
    }
}
