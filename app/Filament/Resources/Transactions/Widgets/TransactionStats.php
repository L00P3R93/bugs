<?php

namespace App\Filament\Resources\Transactions\Widgets;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Models\Wallet;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class TransactionStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        return auth()->user()->isAdmin()
            ? $this->getAdminStats()
            : $this->getUserStats();
    }

    private function getAdminStats(): array
    {
        $totalPayouts = Transaction::query()
            ->where('type', 'payout')
            ->where('status', TransactionStatus::COMPLETED)
            ->sum('amount');

        $totalWithdrawals = Transaction::query()
            ->where('type', 'withdraw')
            ->where('status', TransactionStatus::COMPLETED)
            ->sum('amount');

        $pendingWithdrawals = Transaction::query()
            ->where('type', 'withdraw')
            ->whereIn('status', [TransactionStatus::PENDING, TransactionStatus::PENDING_APPROVAL])
            ->sum('amount');

        $totalTransactions = Transaction::count();

        $totalWallets = Wallet::count();

        $totalWalletBalance = Wallet::sum('balance');

        $payoutsTrend = Trend::query(
            Transaction::query()
                ->where('type', 'payout')
                ->where('status', TransactionStatus::COMPLETED)
        )
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->count();

        $withdrawalsTrend = Trend::query(
            Transaction::query()
                ->where('type', 'withdraw')
                ->where('status', TransactionStatus::COMPLETED)
        )
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->count();

        return [
            Stat::make('Total Payouts', 'KES '.number_format($totalPayouts, 2))
                ->description('All users combined')
                ->descriptionIcon('hugeicons-money-receive-02')
                ->chart($payoutsTrend->map(fn (TrendValue $value) => $value->aggregate)->toArray())
                ->color('success'),

            Stat::make('Total Withdrawals', 'KES '.number_format($totalWithdrawals, 2))
                ->description('All users combined')
                ->descriptionIcon('hugeicons-money-send-02')
                ->chart($withdrawalsTrend->map(fn (TrendValue $value) => $value->aggregate)->toArray())
                ->color('danger'),

            Stat::make('Pending Withdrawals', 'KES '.number_format($pendingWithdrawals, 2))
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Wallet Balance', 'KES '.number_format($totalWalletBalance, 2))
                ->description("Across {$totalWallets} wallets")
                ->descriptionIcon('hugeicons-wallet-02')
                ->color('primary'),

            Stat::make('Total Transactions', number_format($totalTransactions))
                ->description('All-time')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('gray'),
        ];
    }

    private function getUserStats(): array
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        if (! $wallet) {
            return [
                Stat::make('Wallet Balance', 'KES 0.00')
                    ->description('No wallet set up')
                    ->descriptionIcon('hugeicons-wallet-02')
                    ->color('gray'),
            ];
        }

        $myPayouts = Transaction::where('user_id', $user->id)
            ->where('type', 'payout')
            ->where('status', TransactionStatus::COMPLETED)
            ->sum('amount');

        $myWithdrawals = Transaction::where('user_id', $user->id)
            ->where('type', 'withdraw')
            ->where('status', TransactionStatus::COMPLETED)
            ->sum('amount');

        $pendingWithdrawals = Transaction::where('user_id', $user->id)
            ->where('type', 'withdraw')
            ->whereIn('status', [TransactionStatus::PENDING, TransactionStatus::PENDING_APPROVAL])
            ->sum('amount');

        $myTransactionCount = Transaction::where('user_id', $user->id)
            ->whereIn('status', [TransactionStatus::PENDING, TransactionStatus::PENDING_APPROVAL, TransactionStatus::COMPLETED])
            ->count();

        $payoutsTrend = Trend::query(
            Transaction::where('user_id', $user->id)
                ->where('type', 'payout')
                ->where('status', TransactionStatus::COMPLETED)
        )
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->count();

        return [
            Stat::make('Wallet Balance', 'KES '.number_format((float) $wallet->balance, 2))
                ->description($wallet->is_locked ? 'Wallet locked' : 'Available: KES '.number_format((float) $wallet->available_balance, 2))
                ->descriptionIcon($wallet->is_locked ? 'heroicon-m-lock-closed' : 'hugeicons-wallet-02')
                ->color($wallet->is_locked ? 'danger' : 'primary'),

            Stat::make('Total Earned', 'KES '.number_format((float) $wallet->total_earned, 2))
                ->description('Lifetime earnings')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('My Payouts', 'KES '.number_format($myPayouts, 2))
                ->description('Completed game payouts')
                ->descriptionIcon('hugeicons-money-receive-02')
                ->chart($payoutsTrend->map(fn (TrendValue $value) => $value->aggregate)->toArray())
                ->color('teal'),

            Stat::make('My Withdrawals', 'KES '.number_format($myWithdrawals, 2))
                ->description('Completed withdrawals')
                ->descriptionIcon('hugeicons-money-send-02')
                ->color('info'),

            Stat::make('Pending Withdrawals', 'KES '.number_format($pendingWithdrawals, 2))
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingWithdrawals > 0 ? 'warning' : 'gray'),

            Stat::make('Total Transactions', number_format($myTransactionCount))
                ->description('All-time')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('gray'),
        ];
    }
}
