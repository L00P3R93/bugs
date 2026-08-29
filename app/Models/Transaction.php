<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Traits\Auditable;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'transactions';

    protected $fillable = [
        'transaction_no',
        'wallet_id',
        'user_id',
        'bug_id',
        'amount',
        'fee_amount',
        'net_amount',
        'currency',
        'exchange_rate',
        'type',
        'status',
        'payout_method',
        'payout_details',
        'processed_at',
        'completed_at',
        'cancelled_at',
        'description',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'status' => TransactionStatus::class,
            'type' => TransactionType::class,
            'payout_details' => 'encrypted:array',
            'processed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class);
    }

    public function withdraw(): HasOne
    {
        return $this->hasOne(Withdraw::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TransactionLog::class);
    }
}
