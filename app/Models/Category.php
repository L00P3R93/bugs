<?php

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'categories';

    protected function casts(): array
    {
        return [
            'base_min_amount' => 'decimal:2',
            'base_max_amount' => 'decimal:2',
            'weight_multiplier' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function bugs(): HasMany
    {
        return $this->hasMany(Bug::class);
    }
}
