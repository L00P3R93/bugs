<?php

namespace App\Filament\Resources\FraudFlags\Schemas;

use App\Enums\FraudFlagStatus;
use App\Enums\FraudFlagType;
use App\Models\Bug;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FraudFlagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Fraud Flag Details')->schema([
                    Select::make('user_id')
                        ->label('Flagged User')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Select::make('bug_id')
                        ->label('Related Bug')
                        ->options(Bug::pluck('bug_no', 'id'))
                        ->searchable()
                        ->nullable(),
                    Select::make('flag_type')
                        ->label('Flag Type')
                        ->options(FraudFlagType::class)
                        ->required(),
                    TextInput::make('confidence_score')
                        ->label('Confidence Score')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(1)
                        ->step(0.1)
                        ->required(),
                    Select::make('detected_by')
                        ->label('Detected By')
                        ->options([
                            'system' => 'System',
                            'manual' => 'Manual',
                            'ml_model' => 'ML Model',
                        ])
                        ->default('system')
                        ->required(),
                ])->columns(2),

                Section::make('Resolution')->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options(FraudFlagStatus::class)
                        ->required(),
                    Select::make('resolved_by')
                        ->label('Resolved By')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),
                    Textarea::make('resolution_notes')
                        ->label('Resolution Notes')
                        ->rows(3)
                        ->nullable(),
                    DatePicker::make('resolved_at')
                        ->label('Resolved At')
                        ->nullable(),
                ])->columns(2),
            ]);
    }
}
