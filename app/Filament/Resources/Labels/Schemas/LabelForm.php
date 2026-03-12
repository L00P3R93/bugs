<?php

namespace App\Filament\Resources\Labels\Schemas;

use App\Enums\LabelType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LabelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('description'),
                Select::make('type')
                    ->options(LabelType::class)
                    ->default('technical')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
