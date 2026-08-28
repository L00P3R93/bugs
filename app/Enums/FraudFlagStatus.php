<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum FraudFlagStatus: string implements HasColor, HasIcon, HasLabel
{
    case OPEN = 'open';
    case INVESTIGATING = 'investigating';
    case CLEARED = 'cleared';
    case CONFIRMED = 'confirmed';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::INVESTIGATING => 'Investigating',
            self::CLEARED => 'Cleared',
            self::CONFIRMED => 'Confirmed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::OPEN => 'warning',
            self::INVESTIGATING => 'info',
            self::CLEARED => 'success',
            self::CONFIRMED => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::OPEN => 'heroicon-o-exclamation-circle',
            self::INVESTIGATING => 'heroicon-magnifying-glass',
            self::CLEARED => 'heroicon-o-check-circle',
            self::CONFIRMED => 'heroicon-o-x-circle',
        };
    }
}
