<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum TransactionStatus: string implements HasColor, HasIcon, HasLabel
{
    case PENDING_APPROVAL = 'pending_approval';
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::PENDING_APPROVAL => 'Pending Approval',
            self::PENDING => 'Pending',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING_APPROVAL => 'warning',
            self::PENDING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::PENDING_APPROVAL => Heroicon::OutlinedClock,
            self::PENDING => Heroicon::OutlinedClock,
            self::COMPLETED => Heroicon::OutlinedCheckCircle,
            self::FAILED => Heroicon::OutlinedXCircle,
        };
    }
}
