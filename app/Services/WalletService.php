<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Wallet;

class WalletService
{
    /**
     * Calculate fee based on amount and payout method.
     */
    public function calculateFee(float $amount, ?string $payoutMethod = null): float
    {
        return match ($payoutMethod) {
            'mpesa' => round($amount * 0.01, 2), // 1%
            'paypal' => round($amount * 0.029 + 30, 2), // 2.9% + 30
            'bank_transfer' => 100.0, // Fixed 100 KES
            default => 0.0,
        };
    }

    /**
     * Check if wallet can make a withdrawal.
     */
    public function canWithdraw(Wallet $wallet, float $amount): array
    {
        $reasons = [];

        if ($wallet->is_locked) {
            $reasons[] = 'Wallet is locked';
        }

        if ($wallet->available_balance < $amount) {
            $reasons[] = 'Insufficient available balance';
        }

        // Testers must reach daily target before withdrawing
        if ($wallet->user->isTester() && ! $wallet->daily_target_reached) {
            $reasons[] = 'Daily target of 30 games not reached';
        }

        // Check daily limit
        $dailyWithdrawn = $wallet->withdraws()
            ->where('status', TransactionStatus::COMPLETED)
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount');

        if (($dailyWithdrawn + $amount) > $wallet->daily_withdrawal_limit) {
            $reasons[] = 'Daily withdrawal limit exceeded';
        }

        // Check monthly limit
        $monthlyWithdrawn = $wallet->withdraws()
            ->where('status', TransactionStatus::COMPLETED)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        if (($monthlyWithdrawn + $amount) > $wallet->monthly_withdrawal_limit) {
            $reasons[] = 'Monthly withdrawal limit exceeded';
        }

        return [
            'can_withdraw' => empty($reasons),
            'reasons' => $reasons,
        ];
    }

    /**
     * Get wallet statistics.
     */
    public function getStats(Wallet $wallet): array
    {
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

        $pendingApprovalWithdrawals = $wallet->withdraws()
            ->where('status', TransactionStatus::PENDING_APPROVAL)
            ->count();

        $dailyWithdrawn = (float) $wallet->withdraws()
            ->where('status', TransactionStatus::COMPLETED)
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount');

        $monthlyWithdrawn = (float) $wallet->withdraws()
            ->where('status', TransactionStatus::COMPLETED)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        return [
            'balance' => (float) $wallet->balance,
            'available_balance' => (float) $wallet->available_balance,
            'pending_balance' => (float) $wallet->pending_balance,
            'total_earned' => (float) $wallet->total_earned,
            'daily_games_played' => $wallet->daily_games_played,
            'daily_earned' => (float) $wallet->daily_earned,
            'daily_target_reached' => $wallet->daily_target_reached,
            'completed_payouts' => $completedPayouts,
            'pending_payouts' => $pendingPayouts,
            'completed_withdrawals' => $completedWithdrawals,
            'pending_withdrawals' => $pendingWithdrawals,
            'pending_approval_withdrawals' => $pendingApprovalWithdrawals,
            'daily_withdrawn' => $dailyWithdrawn,
            'monthly_withdrawn' => $monthlyWithdrawn,
            'daily_remaining' => max(0, $wallet->daily_withdrawal_limit - $dailyWithdrawn),
            'monthly_remaining' => max(0, $wallet->monthly_withdrawal_limit - $monthlyWithdrawn),
        ];
    }
}
