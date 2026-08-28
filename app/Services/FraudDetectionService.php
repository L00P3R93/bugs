<?php

namespace App\Services;

use App\Enums\FraudFlagStatus;
use App\Enums\FraudFlagType;
use App\Models\FraudFlag;
use App\Models\Withdraw;

class FraudDetectionService
{
    /**
     * Check a withdrawal for potential fraud.
     */
    public function checkWithdrawal(Withdraw $withdraw): ?FraudFlag
    {
        // Check for rapid-fire withdrawals (rate limiting)
        $recentWithdrawals = Withdraw::where('wallet_id', $withdraw->wallet_id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentWithdrawals > 3) {
            return $this->createFlag(
                userId: $withdraw->wallet->user_id,
                flagType: FraudFlagType::RATE_LIMIT,
                confidence: 0.8,
                details: [
                    'withdrawal_count_last_hour' => $recentWithdrawals,
                    'withdrawal_id' => $withdraw->id,
                ]
            );
        }

        // Check for suspicious amounts (very high withdrawals)
        if ($withdraw->amount > 100000) {
            return $this->createFlag(
                userId: $withdraw->wallet->user_id,
                flagType: FraudFlagType::SUSPICIOUS_AMOUNT,
                confidence: 0.6,
                details: [
                    'amount' => $withdraw->amount,
                    'withdrawal_id' => $withdraw->id,
                ]
            );
        }

        // Check for duplicate withdrawal patterns (same amount, short timeframe)
        $duplicatePattern = Withdraw::where('wallet_id', $withdraw->wallet_id)
            ->where('amount', $withdraw->amount)
            ->where('created_at', '>=', now()->subDay())
            ->where('id', '!=', $withdraw->id)
            ->count();

        if ($duplicatePattern > 0) {
            return $this->createFlag(
                userId: $withdraw->wallet->user_id,
                flagType: FraudFlagType::DUPLICATE_PATTERN,
                confidence: 0.7,
                details: [
                    'amount' => $withdraw->amount,
                    'duplicate_count_last_day' => $duplicatePattern,
                    'withdrawal_id' => $withdraw->id,
                ]
            );
        }

        return null;
    }

    /**
     * Create a fraud flag.
     */
    private function createFlag(
        int $userId,
        FraudFlagType $flagType,
        float $confidence,
        array $details = [],
    ): FraudFlag {
        return FraudFlag::create([
            'user_id' => $userId,
            'flag_type' => $flagType,
            'confidence_score' => $confidence,
            'detected_by' => 'system',
            'status' => FraudFlagStatus::OPEN,
            'details' => $details,
        ]);
    }

    /**
     * Get fraud statistics for a user.
     */
    public function getUserStats(int $userId): array
    {
        $totalFlags = FraudFlag::where('user_id', $userId)->count();
        $openFlags = FraudFlag::where('user_id', $userId)
            ->where('status', FraudFlagStatus::OPEN)
            ->count();
        $confirmedFlags = FraudFlag::where('user_id', $userId)
            ->where('status', FraudFlagStatus::CONFIRMED)
            ->count();

        return [
            'total_flags' => $totalFlags,
            'open_flags' => $openFlags,
            'confirmed_flags' => $confirmedFlags,
            'risk_level' => $this->calculateRiskLevel($totalFlags, $confirmedFlags),
        ];
    }

    /**
     * Calculate risk level based on flags.
     */
    private function calculateRiskLevel(int $totalFlags, int $confirmedFlags): string
    {
        if ($confirmedFlags >= 3) {
            return 'high';
        }

        if ($confirmedFlags >= 1 || $totalFlags >= 5) {
            return 'medium';
        }

        if ($totalFlags >= 2) {
            return 'low';
        }

        return 'none';
    }

    /**
     * Get all open fraud flags.
     */
    public function getOpenFlags()
    {
        return FraudFlag::where('status', FraudFlagStatus::OPEN)
            ->with(['user', 'bug'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Resolve a fraud flag.
     */
    public function resolveFlag(FraudFlag $flag, string $status, int $resolvedBy, ?string $notes = null): void
    {
        $flag->update([
            'status' => $status,
            'resolved_by' => $resolvedBy,
            'resolution_notes' => $notes,
            'resolved_at' => now(),
        ]);
    }
}
