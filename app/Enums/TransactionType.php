<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TransactionType: string implements HasColor, HasIcon, HasLabel
{
    case PAYOUT = 'payout';
    case WITHDRAW = 'withdraw';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::PAYOUT => 'Payout',
            self::WITHDRAW => 'Withdraw',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PAYOUT => 'purple',
            self::WITHDRAW => 'orange',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::PAYOUT => 'hugeicons-money-receive-square',
            self::WITHDRAW => 'hugeicons-reverse-withdrawal-02',
        };
    }
}
