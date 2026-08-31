<?php

namespace App\Filament\Widgets;

use App\Services\WalletService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyWalletStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected ?string $heading = 'Bugs Wallet & Transactions Summary';

    private const GAME_TARGETS = [
        '2_players' => 40,
        '3_players' => 29,
        '4_players' => 23,
    ];

    private const TOURNAMENT_TARGET = 3;

    private const JACKPOT_TARGET = 3;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['Tester']) ?? false;
    }

    protected function getStats(): array
    {
        $wallet = auth()->user()->wallet;

        if (! $wallet) {
            return [
                Stat::make('Wallet Balance', '0.00')
                    ->description('No wallet has been set up yet')
                    ->descriptionIcon('heroicon-m-wallet')
                    ->color('gray'),
            ];
        }

        $walletService = app(WalletService::class);
        $stats = $walletService->getStats($wallet);
        $snapshot = $stats['daily_stats_snapshot'];

        return [
            $this->makeTargetStat(
                '2 Player Games',
                $snapshot['games']['2_players'] ?? 0,
                self::GAME_TARGETS['2_players'],
                $stats['daily_2p_games_target_reached'],
                'heroicon-m-users'
            ),
            $this->makeTargetStat(
                '3 Player Games',
                $snapshot['games']['3_players'] ?? 0,
                self::GAME_TARGETS['3_players'],
                $stats['daily_3p_games_target_reached'],
                'heroicon-m-users'
            ),
            $this->makeTargetStat(
                '4 Player Games',
                $snapshot['games']['4_players'] ?? 0,
                self::GAME_TARGETS['4_players'],
                $stats['daily_4p_games_target_reached'],
                'heroicon-m-users'
            ),
            $this->makeTargetStat(
                'Tournaments',
                $snapshot['tournament']['total'] ?? 0,
                self::TOURNAMENT_TARGET,
                $stats['daily_tournament_target_reached'],
                'heroicon-m-trophy'
            ),
            $this->makeTargetStat(
                'Jackpots',
                $snapshot['jackpots']['total'] ?? 0,
                self::JACKPOT_TARGET,
                $stats['daily_jackpot_target_reached'],
                'heroicon-m-currency-dollar'
            ),

            Stat::make('Withdrawal Status', $stats['daily_target_reached'] ? 'Unlocked' : 'Locked')
                ->description($stats['daily_target_reached'] ? 'You can withdraw now' : 'Reach any target to unlock')
                ->descriptionIcon($stats['daily_target_reached'] ? 'heroicon-m-check-circle' : 'heroicon-m-lock-closed')
                ->color($stats['daily_target_reached'] ? 'success' : 'warning'),

            Stat::make('Available Balance', number_format($stats['available_balance'], 2))
                ->description('Ready to withdraw')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),

            Stat::make('Today\'s Earnings', number_format($stats['daily_earned'], 2))
                ->description('Earned today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make('Total Earned', number_format($stats['total_earned'], 2))
                ->description('Lifetime earnings')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make('Daily Remaining', number_format($stats['daily_remaining'], 2))
                ->description('Today\'s withdrawal limit')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Monthly Remaining', number_format($stats['monthly_remaining'], 2))
                ->description('This month\'s withdrawal limit')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Pending Withdrawals', $stats['pending_approval_withdrawals'] + $stats['pending_withdrawals'])
                ->description('Awaiting approval/processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Transactions', number_format($stats['completed_payouts'] + $stats['completed_withdrawals']))
                ->description('All-time completed')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('gray'),
        ];
    }

    private function makeTargetStat(string $label, int $current, int $target, bool $met, string $icon): Stat
    {
        return Stat::make($label, "{$current}/{$target}")
            ->description($met ? 'Target reached' : ($target - $current).' more to go')
            ->descriptionIcon($met ? 'heroicon-m-check-circle' : $icon)
            ->color($met ? 'success' : 'warning');
    }
}
