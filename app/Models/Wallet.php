<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\WalletStatus;
use App\Traits\Auditable;
use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    /** @use HasFactory<WalletFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'wallets';

    protected $fillable = [
        'wallet_no',
        'user_id',
        'balance',
        'available_balance',
        'pending_balance',
        'total_earned',
        'daily_games_played',
        'daily_earned',
        'daily_target_reached',
        'last_daily_reset_at',
        'daily_withdrawal_limit',
        'monthly_withdrawal_limit',
        'status',
        'is_locked',
        'locked_reason',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'available_balance' => 'decimal:2',
            'pending_balance' => 'decimal:2',
            'total_earned' => 'decimal:2',
            'daily_earned' => 'decimal:2',
            'daily_withdrawal_limit' => 'decimal:2',
            'monthly_withdrawal_limit' => 'decimal:2',
            'daily_games_played' => 'integer',
            'daily_target_reached' => 'boolean',
            'last_daily_reset_at' => 'datetime',
            'status' => WalletStatus::class,
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function withdraws(): HasMany
    {
        return $this->hasMany(Withdraw::class);
    }

    /**
     * Check if the tester has reached the daily game target.
     */
    public function hasReachedDailyTarget(): bool
    {
        return $this->daily_target_reached;
    }

    /**
     * Reset daily tracking counters.
     */
    public function resetDailyStats(): void
    {
        $this->update([
            'daily_games_played' => 0,
            'daily_earned' => 0,
            'daily_target_reached' => false,
            'last_daily_reset_at' => now(),
        ]);
    }

    /**
     * Reset balance to zero for testers who missed the daily target.
     */
    public function resetDailyBalance(): void
    {
        $this->update([
            'balance' => 0,
            'available_balance' => 0,
        ]);
    }

    /**
     * Check if the wallet can withdraw a given amount.
     */
    public function canWithdraw(float $amount): bool
    {
        if ($this->is_locked) {
            return false;
        }

        if ($this->available_balance < $amount) {
            return false;
        }

        // Testers must reach daily target before withdrawing
        if ($this->user->isTester() && ! $this->daily_target_reached) {
            return false;
        }

        // Check daily limit
        $dailyWithdrawn = $this->withdraws()
            ->where('status', TransactionStatus::COMPLETED)
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount');

        if (($dailyWithdrawn + $amount) > $this->daily_withdrawal_limit) {
            return false;
        }

        // Check monthly limit
        $monthlyWithdrawn = $this->withdraws()
            ->where('status', TransactionStatus::COMPLETED)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        if (($monthlyWithdrawn + $amount) > $this->monthly_withdrawal_limit) {
            return false;
        }

        return true;
    }

    /**
     * Lock the wallet for security reasons.
     */
    public function lock(string $reason): void
    {
        $this->update([
            'is_locked' => true,
            'locked_reason' => $reason,
            'locked_at' => now(),
        ]);
    }

    /**
     * Unlock the wallet.
     */
    public function unlock(): void
    {
        $this->update([
            'is_locked' => false,
            'locked_reason' => null,
            'locked_at' => null,
        ]);
    }
}
