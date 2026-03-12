<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum BugStatus: string implements HasColor, HasIcon, HasLabel
{
    case SUBMITTED = 'submitted';
    case TRIAGED = 'triaged';
    case UNDER_REVIEW = 'under_review';
    case VALIDATED = 'validated';
    case REJECTED = 'rejected';
    case DUPLICATE = 'duplicate';
    case WONT_FIX = 'wont_fix';
    case PAID = 'paid';
    case FIXED = 'fixed';
    case CLOSED = 'closed';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::SUBMITTED => 'Submitted',
            self::TRIAGED => 'Triaged',
            self::UNDER_REVIEW => 'Under Review',
            self::VALIDATED => 'Validated',
            self::REJECTED => 'Rejected',
            self::DUPLICATE => 'Duplicate',
            self::WONT_FIX => "Won't Fix",
            self::PAID => 'Paid',
            self::FIXED => 'Fixed',
            self::CLOSED => 'Closed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::SUBMITTED, self::DUPLICATE, self::CLOSED => 'gray',
            self::TRIAGED => 'info',
            self::UNDER_REVIEW => 'warning',
            self::VALIDATED, self::PAID, self::FIXED => 'success',
            self::REJECTED, self::WONT_FIX => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::SUBMITTED => 'heroicon-o-document',
            self::TRIAGED => 'heroicon-o-arrows-right-left',
            self::UNDER_REVIEW => 'heroicon-o-eye',
            self::VALIDATED => 'heroicon-o-check-badge',
            self::REJECTED => 'heroicon-o-x-circle',
            self::DUPLICATE => 'heroicon-o-document-duplicate',
            self::WONT_FIX => 'heroicon-o-no-symbol',
            self::PAID => 'heroicon-o-currency-dollar',
            self::FIXED => 'heroicon-o-wrench-screwdriver',
            self::CLOSED => 'heroicon-o-lock-closed',
        };
    }
}
