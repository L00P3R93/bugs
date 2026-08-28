<?php

namespace App\Models;

use App\Enums\FraudFlagStatus;
use App\Enums\FraudFlagType;
use App\Traits\Auditable;
use Database\Factories\FraudFlagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FraudFlag extends Model
{
    /** @use HasFactory<FraudFlagFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'fraud_flags';

    protected $fillable = [
        'user_id',
        'bug_id',
        'flag_type',
        'confidence_score',
        'detected_by',
        'status',
        'resolved_by',
        'resolution_notes',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'flag_type' => FraudFlagType::class,
            'confidence_score' => 'decimal:2',
            'status' => FraudFlagStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
