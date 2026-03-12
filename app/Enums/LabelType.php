<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum LabelType: string implements HasColor, HasIcon, HasLabel
{
    case TECHNICAL = 'technical';
    case PLATFORM = 'platform';
    case PROCESS = 'process';
    case SPECIAL = 'special';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::TECHNICAL => 'Technical',
            self::PLATFORM => 'Platform',
            self::PROCESS => 'Process',
            self::SPECIAL => 'Special',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::TECHNICAL => 'info',      // Blue
            self::PLATFORM => 'warning',     // Amber/Orange
            self::PROCESS => 'gray',         // Gray
            self::SPECIAL => 'success',      // Green
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::TECHNICAL => 'heroicon-o-code-bracket',        // Code bracket for technical
            self::PLATFORM => 'heroicon-o-computer-desktop',     // Computer for platform
            self::PROCESS => 'heroicon-o-arrow-path',            // Arrow path for process
            self::SPECIAL => 'heroicon-o-star',                  // Star for special
        };
    }
}
