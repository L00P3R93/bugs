<?php

namespace App\Models;

use App\Enums\LabelType;
use App\Traits\Auditable;
use Database\Factories\LabelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Label extends Model
{
    /** @use HasFactory<LabelFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'labels';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'type' => LabelType::class,
        ];
    }

    /**
     * Get the bugs associated with the label.
     */
    public function bugs(): BelongsToMany
    {
        return $this->belongsToMany(Bug::class, 'bug_labels')
            ->withPivot('added_by', 'created_at')
            ->withTimestamps();
    }

    /**
     * Get the bug label assignments associated with the label.
     */
    public function bugAssignments(): HasMany
    {
        return $this->hasMany(BugLabel::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
