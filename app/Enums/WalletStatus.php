<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum WalletStatus: string implements HasColor, HasIcon, HasLabel
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case BLOCKED = 'blocked';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
            self::BLOCKED => 'Blocked',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::SUSPENDED => 'warning',
            self::BLOCKED => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::ACTIVE => Heroicon::OutlinedCheckCircle,
            self::SUSPENDED => Heroicon::OutlinedExclamationCircle,
            self::BLOCKED => Heroicon::OutlinedXCircle,
        };
    }
}
