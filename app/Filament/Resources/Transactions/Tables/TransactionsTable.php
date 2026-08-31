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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['wallet.user', 'bug', 'user']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('transaction_no')
                    ->label('Transaction')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->description(fn ($record) => $record->wallet?->wallet_no ?? '-'),

                TextColumn::make('bug.title')
                    ->label('Bug')
                    ->searchable()
                    ->placeholder('-')
                    ->limit(30),

                TextColumn::make('source')
                    ->label('Source')
                    ->state(function ($record): string {
                        if ($record->bug_id) {
                            return 'Bug Bounty';
                        }

                        return match ($record->type) {
                            TransactionType::PAYOUT => 'Games Played',
                            TransactionType::WITHDRAW => 'Withdrawal',
                            default => '-',
                        };
                    })
                    ->badge()
                    ->color(function ($record): string {
                        if ($record->bug_id) {
                            return 'success';
                        }

                        return match ($record->type) {
                            TransactionType::PAYOUT => 'info',
                            TransactionType::WITHDRAW => 'warning',
                            default => 'gray',
                        };
                    }),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('KES ')
                    ->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
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
