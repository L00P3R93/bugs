<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Traits\Auditable;
use Database\Factories\WithdrawFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Withdraw extends Model
{
    /** @use HasFactory<WithdrawFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'withdraws';

    protected $fillable = [
        'wallet_id',
        'transaction_id',
        'phone',
        'amount',
        'balance',
        'status',
        'response_code',
        'response_message',
        'conversation_id',
        'transaction_ref',
        'failure_reason',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'status' => TransactionStatus::class,
        'approved_at' => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Approve the withdrawal request.
     */
    public function approve(int $adminId): void
    {
        $this->update([
            'approved_by' => $adminId,
            'approved_at' => now(),
            'status' => TransactionStatus::PENDING,
        ]);
    }

    /**
     * Reject the withdrawal request.
     */
    public function reject(int $adminId, string $reason): void
    {
        $this->update([
            'approved_by' => $adminId,
            'rejection_reason' => $reason,
            'status' => TransactionStatus::FAILED,
        ]);
    }
}
