<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('User')->searchable()->sortable(),
                TextColumn::make('auditable_type')->label('Model')->sortable()->toggleable(),
                TextColumn::make('event')->badge()->colors([
                    'success' => 'created',
                    'danger' => 'deleted',
                    'warning' => 'updated',
                ])->sortable(),
                TextColumn::make('ip_address')->label('IP')->sortable(),
                TextColumn::make('created_at')->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')->label('Event Type')->options([
                    'created' => 'Created',
                    'updated' => 'Updated',
                    'deleted' => 'Deleted',
                ]),

                SelectFilter::make('user_id')->label('User')->relationship('user', 'name')->searchable()->preload(),
            ])
            ->recordActions([
                ViewAction::make()->icon(Heroicon::OutlinedEye)->iconButton()->tooltip('View Audit Log'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
