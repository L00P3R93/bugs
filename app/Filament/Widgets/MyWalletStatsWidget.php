<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyWalletStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()?->isTester() ?? false;
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

        $completedPayouts = (float) $wallet->transactions()
            ->where('type', TransactionType::PAYOUT)
            ->where('status', TransactionStatus::COMPLETED)
            ->sum('amount');

        $pendingPayouts = $wallet->transactions()
            ->where('type', TransactionType::PAYOUT)
            ->where('status', TransactionStatus::PENDING)
            ->count();

        $completedWithdrawals = (float) $wallet->withdraws()
            ->where('status', TransactionStatus::COMPLETED)
            ->sum('amount');

        $pendingWithdrawals = $wallet->withdraws()
            ->where('status', TransactionStatus::PENDING)
            ->count();

        $totalTransactions = $wallet->transactions()->count();

        return [
            Stat::make('Wallet Balance', number_format((float) $wallet->balance, 2))
                ->description('Available balance')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('primary'),

            Stat::make('Total Payouts Received', number_format($completedPayouts, 2))
                ->description('Bug bounty rewards credited')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Pending Payouts', number_format($pendingPayouts))
                ->description('Payouts being processed')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Withdrawn', number_format($completedWithdrawals, 2))
                ->description('Funds successfully withdrawn')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('info'),

            Stat::make('Pending Withdrawals', number_format($pendingWithdrawals))
                ->description('Withdrawal requests in queue')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Transactions', number_format($totalTransactions))
                ->description('All-time transaction count')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('gray'),
        ];
    }
}
