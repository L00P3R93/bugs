<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('user.name')->label('User'),
            TextEntry::make('auditable_type')->label('Model'),
            TextEntry::make('event')->label('Event'),
            TextEntry::make('old_values')->label('Old Values')->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT)),
            TextEntry::make('new_values')->label('New Values')->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT)),
            TextEntry::make('ip_address')->label('IP'),
            TextEntry::make('created_at')->label('Created At'),
        ]);
    }
}
