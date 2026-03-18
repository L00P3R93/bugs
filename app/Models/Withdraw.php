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

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'status' => TransactionStatus::class,
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
