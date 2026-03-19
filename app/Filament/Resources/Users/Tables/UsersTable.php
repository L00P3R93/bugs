<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->getStateUsing(fn ($record): ?string => $record->gravatar_url)
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('wallet.balance')
                    ->label('Wallet Balance')
                    ->numeric(
                        decimalPlaces: 2,
                        thousandsSeparator: ','
                    )
                    ->summarize(Sum::make('Total Balance'))
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Phone number')
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('email_verified_at')
                    ->icon(fn ($state): string => $state === null ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($state): string => $state === null ? 'danger' : 'success')
                    ->label('Verified')
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->badge()
                    ->label('Role')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('User Status')
                    ->sortable()
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles.name')
                    ->relationship('roles', 'name')
                    ->label('Role')
                    ->native(false)
                    ->preload(),

                SelectFilter::make('status')
                    ->label('User Status')
                    ->native(false)
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                        'deleted' => 'Deleted',
                        'banned' => 'Banned',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->icon(Heroicon::OutlinedPencilSquare)->color('warning')->tooltip('Edit User'),
                ViewAction::make()->iconButton()->icon(Heroicon::OutlinedEye)->color('primary')->tooltip('View User'),
                DeleteAction::make()->iconButton()->icon(Heroicon::OutlinedTrash)->color('danger')->tooltip('Delete User'),
            ])
            ->groups([
                Group::make('roles.name')->label('Role')->collapsible(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
