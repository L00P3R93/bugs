<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum UserStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
    case Deleted = 'deleted';
    case Banned = 'banned';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Suspended => 'Suspended',
            self::Deleted => 'Deleted',
            self::Banned => 'Banned',
        };
    }

    public function getIcon(): BackedEnum
    {
        return match ($this) {
            self::Active => Heroicon::OutlinedCheckCircle,
            self::Inactive => Heroicon::OutlinedPauseCircle,
            self::Suspended => Heroicon::OutlinedExclamationCircle,
            self::Deleted => Heroicon::OutlinedTrash,
            self::Banned => Heroicon::OutlinedXCircle,
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'gray',
            self::Suspended => 'warning',
            self::Deleted => 'orange',
            self::Banned => 'danger',
        };
    }
}
