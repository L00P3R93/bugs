<?php

namespace App\Filament\Resources\Bugs\Schemas;

use App\Enums\BugStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BugForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bug_no')
                    ->required(),
                Select::make('reporter_id')
                    ->relationship('reporter', 'name')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                Select::make('severity_id')
                    ->relationship('severity', 'name')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('environment')
                    ->columnSpanFull(),
                Textarea::make('steps_to_reproduce')
                    ->columnSpanFull(),
                Textarea::make('expected_result')
                    ->columnSpanFull(),
                Textarea::make('actual_result')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(BugStatus::class)
                    ->default('submitted')
                    ->required(),
                Textarea::make('remarks')
                    ->columnSpanFull(),
                Select::make('duplicate_of_id')
                    ->relationship('duplicateOf', 'title'),
                TextInput::make('base_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('final_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Toggle::make('is_paid')
                    ->required(),
                DateTimePicker::make('paid_at'),
            ]);
    }
}
