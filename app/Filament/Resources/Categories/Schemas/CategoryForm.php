<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Category Details')->schema([
                TextInput::make('name')
                    ->label('Category Name')
                    ->prefixIcon('hugeicons-file-script')
                    ->prefixIconColor('primary')
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->prefixIcon('hugeicons-link-05')
                    ->prefixIconColor('primary')
                    ->disabled()
                    ->dehydrated()
                    ->required(),
            ])->columns(2)->columnSpanFull(),
            Section::make('Category Description')->schema([
                RichEditor::make('description')
                    ->columnSpanFull(),
            ])->columnSpanFull()->collapsed(),
            Section::make('Category Calculations')->schema([
                TextInput::make('base_min_amount')
                    ->label('Base Minimum Amount')
                    ->prefixIcon('hugeicons-minus-sign-square')
                    ->prefixIconColor('primary')
                    ->prefix('Ksh. ')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('base_max_amount')
                    ->label('Base Maximum Amount')
                    ->prefixIcon('hugeicons-plus-sign-square')
                    ->prefixIconColor('primary')
                    ->prefix('Ksh. ')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('weight_multiplier')
                    ->label('Weight Multiplier')
                    ->prefixIcon('hugeicons-multiplication-sign-square')
                    ->prefixIconColor('primary')
                    ->required()
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->step(0.50)
                    ->default(1.0),
            ])->columns(3)->columnSpanFull(),
            Section::make('Category State')->schema([
                Toggle::make('is_active')
                    ->label('Visibility')
                    ->required(),
                Toggle::make('is_featured')
                    ->required(),
            ])->columns(2)->columnSpanFull()->visible(fn (?Category $record) => $record !== null),
        ]);
    }
}
