<?php

namespace App\Filament\Resources\Transactions\Widgets;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class TransactionStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalPayouts = Transaction::query()
            ->where('type', 'payout')
            ->where('status', TransactionStatus::COMPLETED)
            ->sum('amount');

        $totalWithdrawals = Transaction::query()
            ->where('type', 'withdraw')
            ->where('status', TransactionStatus::COMPLETED)
            ->sum('amount');

        $walletBalance = auth()->user()->wallet->balance;

        $totalPayoutsData = Trend::query(
            Transaction::query()
                ->where('type', 'payout')
                ->where('status', TransactionStatus::COMPLETED)
        )
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();

        $totalWithdrawalsData = Trend::query(
            Transaction::query()
                ->where('type', 'withdraw')
                ->where('status', TransactionStatus::COMPLETED)
        )
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();

        return [
            Stat::make('Wallet Balance', 'KES '.format_number($walletBalance))
                ->icon('hugeicons-wallet-02')
                ->color('primary'),

            Stat::make('Total Payouts', 'KES '.format_number($totalPayouts))
                ->icon('hugeicons-money-receive-02')
                ->description('Total Payouts')
                ->descriptionIcon('hugeicons-money-receive-flow-02')
                ->chart($totalPayoutsData->map(fn (TrendValue $value) => $value->aggregate)->toArray())
                ->color('teal'),

            Stat::make('Total Withdrawals', 'KES '.format_number($totalWithdrawals))
                ->icon('hugeicons-money-send-02')
                ->description('Total Withdrawals')
                ->descriptionIcon('hugeicons-money-send-flow-02')
                ->chart($totalWithdrawalsData->map(fn (TrendValue $value) => $value->aggregate)->toArray())
                ->color('danger'),
        ];
    }
}
