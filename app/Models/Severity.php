<?php

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\SeverityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Severity extends Model
{
    /** @use HasFactory<SeverityFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'severities';

    protected function casts(): array
    {
        return [
            'multiplier' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function bugs(): HasMany
    {
        return $this->hasMany(Bug::class);
    }
}
