<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Traits\Auditable;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'transactions';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => TransactionStatus::class,
            'type' => TransactionType::class,
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class);
    }
}
