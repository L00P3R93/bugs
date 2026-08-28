<?php

namespace App\Filament\Resources\Wallets\Tables;

use App\Enums\WalletStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class WalletsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('wallet_no')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('balance')
                    ->numeric()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('available_balance')
                    ->label('Available')
                    ->numeric()
                    ->sortable()
                    ->color('success'),
                TextColumn::make('pending_balance')
                    ->label('Pending')
                    ->numeric()
                    ->sortable()
                    ->color('warning'),
                TextColumn::make('total_earned')
                    ->label('Total Earned')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_locked')
                    ->label('Locked')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('daily_withdrawal_limit')
                    ->label('Daily Limit')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('monthly_withdrawal_limit')
                    ->label('Monthly Limit')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(WalletStatus::class)
                    ->native(false)
                    ->searchable(),
                SelectFilter::make('is_locked')
                    ->label('Lock Status')
                    ->options([
                        1 => 'Locked',
                        0 => 'Unlocked',
                    ])
                    ->native(false),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->icon('hugeicons-wallet-add-01')->color('warning')->tooltip('Edit Wallet'),
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
