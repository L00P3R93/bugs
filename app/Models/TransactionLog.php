<?php

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\TransactionLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionLog extends Model
{
    /** @use HasFactory<TransactionLogFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'transaction_logs';

    protected $fillable = [
        'transaction_id',
        'action',
        'previous_status',
        'new_status',
        'details',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
