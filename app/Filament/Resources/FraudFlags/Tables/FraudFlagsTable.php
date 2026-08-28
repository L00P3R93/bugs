<?php

namespace App\Filament\Resources\FraudFlags\Tables;

use App\Enums\FraudFlagStatus;
use App\Enums\FraudFlagType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class FraudFlagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('bug.bug_no')
                    ->label('Bug')
                    ->searchable(),
                TextColumn::make('flag_type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('confidence_score')
                    ->label('Confidence')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('detected_by')
                    ->label('Detected By')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('resolvedBy.name')
                    ->label('Resolved By')
                    ->searchable(),
                TextColumn::make('resolved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('flag_type')
                    ->options(FraudFlagType::class)
                    ->native(false),
                SelectFilter::make('status')
                    ->options(FraudFlagStatus::class)
                    ->native(false),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->icon('heroicon-o-pencil')->color('warning'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
