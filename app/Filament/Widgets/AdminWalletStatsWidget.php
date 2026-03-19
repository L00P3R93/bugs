<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Withdraw;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminWalletStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getStats(): array
    {
        $totalBalance = (float) Wallet::sum('balance');
        $totalWallets = Wallet::count();

        $completedPayouts = Transaction::where('type', TransactionType::PAYOUT)
            ->where('status', TransactionStatus::COMPLETED)
            ->sum('amount');

        $pendingPayouts = Transaction::where('type', TransactionType::PAYOUT)
            ->where('status', TransactionStatus::PENDING)
            ->count();

        $completedWithdrawals = Withdraw::where('status', TransactionStatus::COMPLETED)
            ->sum('amount');

        $pendingWithdrawals = Withdraw::where('status', TransactionStatus::PENDING)
            ->count();

        $totalTransactions = Transaction::count();

        return [
            Stat::make('Total Wallet Balance', number_format($totalBalance, 2))
                ->description("{$totalWallets} active wallets")
                ->descriptionIcon('heroicon-m-wallet')
                ->color('primary'),

            Stat::make('Total Payouts Issued', number_format((float) $completedPayouts, 2))
                ->description('Completed bug reward payouts')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Pending Payouts', number_format($pendingPayouts))
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Withdrawals', number_format((float) $completedWithdrawals, 2))
                ->description('Completed tester withdrawals')
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
