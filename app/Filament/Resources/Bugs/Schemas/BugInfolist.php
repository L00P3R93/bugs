<?php

namespace App\Filament\Resources\Bugs\Schemas;

use App\Models\Bug;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BugInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('bug_no'),
                TextEntry::make('reporter.name')
                    ->label('Reporter'),
                TextEntry::make('category.name')
                    ->label('Category'),
                TextEntry::make('severity.name')
                    ->label('Severity'),
                TextEntry::make('title'),
                TextEntry::make('description')
                    ->columnSpanFull(),
                TextEntry::make('environment')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('steps_to_reproduce')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('expected_result')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('actual_result')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('remarks')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('duplicateOf.title')
                    ->label('Duplicate of')
                    ->placeholder('-'),
                TextEntry::make('base_amount')
                    ->numeric(),
                TextEntry::make('final_amount')
                    ->numeric(),
                IconEntry::make('is_paid')
                    ->boolean(),
                TextEntry::make('paid_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Bug $record): bool => $record->trashed()),
            ]);
    }
}
