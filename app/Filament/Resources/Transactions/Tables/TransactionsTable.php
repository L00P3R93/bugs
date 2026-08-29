<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Filament\Actions\ApproveWithdrawalAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['wallet.user', 'bug']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('transaction_no')
                    ->searchable(),
                TextColumn::make('wallet.wallet_no')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('bug.title')
                    ->searchable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
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
                SelectFilter::make('type')
                    ->label('Transaction Types')
                    ->options(TransactionType::class)
                    ->native(false),
                SelectFilter::make('status')
                    ->label('Transaction Status')
                    ->options(TransactionStatus::class)
                    ->native(false),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ApproveWithdrawalAction::make('approve_withdrawal'),
                ViewAction::make()->iconButton()->icon('hugeicons-file-view')->color('primary')->tooltip('View Transaction Details'),
                EditAction::make()
                    ->iconButton()
                    ->icon('hugeicons-note-edit')
                    ->color('info')
                    ->tooltip('Edit Transaction')
                    ->visible(fn () => auth()->user()->isAdmin()),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->iconButton()
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->tooltip('Delete Transaction')
                    ->visible(fn () => auth()->user()->isAdmin()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn () => auth()->user()->isAdmin()),
                    ForceDeleteBulkAction::make()->visible(fn () => auth()->user()->isAdmin()),
                    RestoreBulkAction::make()->visible(fn () => auth()->user()->isAdmin()),
                ]),
            ]);
    }
}
