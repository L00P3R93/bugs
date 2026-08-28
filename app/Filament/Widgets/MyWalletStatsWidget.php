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
            Stat::make('Available Balance', number_format($stats['available_balance'], 2))
                ->description('Ready to withdraw')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),

            Stat::make('Pending Balance', number_format($stats['pending_balance'], 2))
                ->description('7-day holding period')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

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

            Stat::make('Total Transactions', number_format($stats['completed_payouts'] + $stats['completed_withdrawals']))
                ->description('All-time completed')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('gray'),
        ];
    }
}
