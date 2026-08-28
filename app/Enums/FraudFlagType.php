<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum FraudFlagType: string implements HasColor, HasIcon, HasLabel
{
    case DUPLICATE_PATTERN = 'duplicate_pattern';
    case SUSPICIOUS_IP = 'suspicious_ip';
    case RATE_LIMIT = 'rate_limit';
    case SUSPICIOUS_AMOUNT = 'suspicious_amount';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::DUPLICATE_PATTERN => 'Duplicate Pattern',
            self::SUSPICIOUS_IP => 'Suspicious IP',
            self::RATE_LIMIT => 'Rate Limit',
            self::SUSPICIOUS_AMOUNT => 'Suspicious Amount',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DUPLICATE_PATTERN => 'warning',
            self::SUSPICIOUS_IP => 'danger',
            self::RATE_LIMIT => 'info',
            self::SUSPICIOUS_AMOUNT => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::DUPLICATE_PATTERN => 'heroicon-o-document-duplicate',
            self::SUSPICIOUS_IP => 'heroicon-o-globe-alt',
            self::RATE_LIMIT => 'heroicon-o-clock',
            self::SUSPICIOUS_AMOUNT => 'heroicon-o-exclamation-triangle',
        };
    }
}
