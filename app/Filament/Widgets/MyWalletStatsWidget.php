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

        return [
            Stat::make('Daily Progress', "{$stats['daily_games_played']}/30 games")
                ->description($stats['daily_target_reached'] ? 'Target reached - withdrawals unlocked' : 'Reach 30 games to unlock withdrawals')
                ->descriptionIcon($stats['daily_target_reached'] ? 'heroicon-m-check-circle' : 'heroicon-m-lock-closed')
                ->color($stats['daily_target_reached'] ? 'success' : 'warning'),

            Stat::make('Available Balance', number_format($stats['available_balance'], 2))
                ->description('Ready to withdraw')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),

            Stat::make('Pending Balance', number_format($stats['pending_balance'], 2))
                ->description('Holding period')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

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
}
