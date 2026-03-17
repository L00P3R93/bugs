<?php

namespace App\Filament\Resources\Labels\Schemas;

use App\Enums\LabelType;
use App\Models\Label;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LabelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Label Details')->schema([
                TextInput::make('name')
                    ->label('Label Name')
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
                Select::make('type')
                    ->label('Label Type')
                    ->prefixIcon('hugeicons-cursor-circle-selection-02')
                    ->prefixIconColor('primary')
                    ->options(LabelType::class)
                    ->default('technical')
                    ->native(false)
                    ->searchable()
                    ->required(),
                Toggle::make('is_active')
                    ->label('Visible')
                    ->visible(fn (?Label $record) => $record !== null)
                    ->required(),
            ])->columns(3)->columnSpanFull(),
            Section::make('Category Description')->schema([
                RichEditor::make('description')
                    ->columnSpanFull(),
            ])->columnSpanFull(),
        ]);
    }
}
