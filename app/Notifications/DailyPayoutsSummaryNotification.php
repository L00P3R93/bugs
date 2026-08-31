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
     * @param  array<int, array{name: string, email: string, targets: array<int, string>}>  $targetsReached
     * @param  array<int, array{name: string, email: string, amount: float, games: int, total_games: int}>  $paidDetails
     */
    public function __construct(
        public string $date,
        public int $totalTesters,
        public int $paidCount,
        public int $skippedCount,
        public int $errorCount,
        public float $totalAmount,
        public array $paidDetails,
        public array $targetsReached = [],
    ) {}

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

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Daily Target Reached — {$this->date}")
            ->greeting("Targets Reached on {$this->date}")
            ->line(count($this->targetsReached).' tester(s) reached their daily target(s).');

        foreach ($this->targetsReached as $entry) {
            $targets = implode(', ', $entry['targets']);
            $mail->line("• {$entry['name']} — {$targets}");
        }

        if ($this->paidCount > 0) {
            $mail->line('')
                ->line("Payouts processed: {$this->paidCount} (KES ".number_format($this->totalAmount, 2).')');
        }

        if ($this->errorCount > 0) {
            $mail->line("Errors: {$this->errorCount}");
        }

        return $mail
            ->action('View Dashboard', url('/console'));
    }

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
            'targets_reached' => $this->targetsReached,
            'icon' => 'heroicon-o-trophy',
            'color' => 'success',
        ];
    }
}
