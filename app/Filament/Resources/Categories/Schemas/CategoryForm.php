<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('base_min_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('base_max_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('weight_multiplier')
                    ->required()
                    ->numeric()
                    ->default(1.0),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_featured')
                    ->required(),
            ]);
    }
}
