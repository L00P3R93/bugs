<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Traits\Auditable;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Kirschbaum\Commentions\Contracts\Commenter;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements Commenter, FilamentUser, HasAvatar, HasMedia, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, HasRoles, InteractsWithMedia, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'notification_preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'notification_preferences' => 'array',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $uploaded = $this->getFirstMediaUrl('avatars', 'thumb');

        if ($uploaded) {
            return $uploaded;
        }

        $hash = md5(strtolower(trim($this->email ?? '')));

        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=150";
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->hasVerifiedEmail()) {
            return false;
        }

        if ($panel->getId() === 'admin') {
            return $this->hasAnyRole(['Super Admin', 'Admin', 'Tester', 'Player']);
        }

        return false;
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function bugs(): HasMany
    {
        return $this->hasMany(Bug::class, 'reporter_id');
    }

    /**
     * Get the bug labels associated with the user.
     */
    public function bugLabels(): HasMany
    {
        return $this->hasMany(BugLabel::class);
    }

    /**
     * Get the bugs associated with the user.
     */
    public function labeledBugs(): BelongsToMany
    {
        return $this->belongsToMany(Bug::class, 'bug_labels', 'added_by', 'bug_id')
            ->withPivot('label_id', 'created_at')
            ->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['Admin', 'Super Admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function isTester(): bool
    {
        return $this->hasRole('Tester') && ! $this->isAdmin();
    }

    public function isLinked(): bool
    {
        return $this->linked_id !== null;
    }

    /**
     * Check if user prefers a specific notification type on a channel.
     */
    public function prefersNotification(string $type, string $channel = 'email'): bool
    {
        $prefs = $this->notification_preferences ?? $this->getDefaultPreferences();

        return $prefs[$type][$channel] ?? true;
    }

    /**
     * Get default notification preferences.
     *
     * @return array<string, array<string, bool>>
     */
    public function getDefaultPreferences(): array
    {
        return [
            'daily_payouts_summary' => ['email' => true, 'database' => true],
            'withdrawals_summary' => ['email' => true, 'database' => true],
            'bug_submitted' => ['email' => true, 'database' => true],
            'bug_status_changed' => ['email' => true, 'database' => true],
            'bug_comment' => ['email' => true, 'database' => true],
            'bug_mention' => ['email' => true, 'database' => true],
            'payment_received' => ['email' => true, 'database' => true],
            'withdrawal_completed' => ['email' => true, 'database' => true],
            'withdrawal_failed' => ['email' => true, 'database' => true],
            'wallet_locked' => ['email' => true, 'database' => true],
            'withdrawal_requested' => ['email' => true, 'database' => true],
            'withdrawal_approved' => ['email' => true, 'database' => true],
            'withdrawal_rejected' => ['email' => true, 'database' => true],
            'daily_target_reached' => ['email' => true, 'database' => true],
        ];
    }

    /**
     * Set the phone attribute - convert 0 prefix to 254 for storage
     */
    public function setPhoneAttribute($value): void
    {
        $phone = trim($value);

        // Strip all non-digits
        $phone = preg_replace('/\D/', '', $phone);

        // If phone starts with +254 or 254, ensure it starts with 254
        if (str_starts_with($phone, '254')) {
            // Already in correct format
        } elseif (str_starts_with($phone, '0')) {
            $phone = '254'.substr($phone, 1);
        }

        $this->attributes['phone'] = $phone;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars')
            ->useDisk('public')
            ->acceptsFile(fn ($file) => in_array($file->mimeType, [
                'image/jpeg', 'image/png', 'image/jpg', 'image/gif',
            ]))
            ->singleFile() // Only allow one file per collection
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumb')
                    ->width(150)
                    ->height(150)
                    ->sharpen(10)
                    ->nonQueued();

                $this->addMediaConversion('medium')
                    ->width(500)
                    ->height(500)
                    ->nonQueued();

                $this->addMediaConversion('large')
                    ->width(1200)
                    ->height(1200)
                    ->nonQueued();
            });
    }
}
