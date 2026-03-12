<?php

namespace App\Models;

use App\Enums\BugStatus;
use App\Traits\Auditable;
use Database\Factories\BugFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Bug extends Model implements HasMedia
{
    /** @use HasFactory<BugFactory> */
    use Auditable, HasFactory, InteractsWithMedia, SoftDeletes;

    protected $table = 'bugs';

    protected function casts(): array
    {
        return [
            'status' => BugStatus::class,
            'paid_at' => 'datetime',
            'base_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'is_paid' => 'boolean',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function severity(): BelongsTo
    {
        return $this->belongsTo(Severity::class);
    }

    /**
     * Get the labels associated with the bug.
     */
    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'bug_labels')
            ->withPivot('added_by', 'created_at')
            ->withTimestamps();
    }

    /**
     * Get the users who added labels associated with the bug.
     */
    public function labelAdditions(): HasMany
    {
        return $this->hasMany(BugLabel::class);
    }

    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(Bug::class, 'duplicate_of_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getBaseAmount(): float
    {
        $category = $this->category;

        return $category->base_min_amount;
    }

    public function getFinalAmount(): float
    {
        $category = $this->category;
        $severity = $this->severity;

        return $category->base_min_amount + ($severity->multiplier * $category->base_min_amount);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public')
            ->acceptsFile(fn ($file) => in_array($file->mimeType, [
                'image/jpeg', 'image/png', 'image/jpg', 'image/gif',
            ]))
            ->singleFile();

        $this->addMediaCollection('videos')
            ->useDisk('public')
            ->acceptsFile(fn ($file) => in_array($file->mimeType, [
                'video/mp4', 'video/mov', 'video/avi', 'video/wmv',
            ]))
            ->singleFile();
    }
}
