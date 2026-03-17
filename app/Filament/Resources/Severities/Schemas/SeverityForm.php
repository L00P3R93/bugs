<?php

namespace App\Filament\Resources\Severities\Schemas;

use App\Models\Severity;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class SeverityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Severity Details')->schema([
                TextInput::make('name')
                    ->label('Severity Name')
                    ->prefixIcon('hugeicons-file-script')
                    ->prefixIconColor('primary')
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('multiplier')
                    ->label('Weight Multiplier')
                    ->prefixIcon('hugeicons-multiplication-sign-square')
                    ->prefixIconColor('primary')
                    ->required()
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->step(0.50)
                    ->default(0.5),
                RichEditor::make('definition')
                    ->required()
                    ->helperText('Define the severity level and how it is used and which cases.')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Visible')
                    ->visible(fn (?Severity $record) => $record !== null)
                    ->required(),
            ])->columns(2)->columnSpan(fn (?Severity $record) => $record === null ? 3 : 2),
            Section::make()->schema([
                TextEntry::make('created_at')->state(fn (Severity $record): ?string => $record->created_at?->diffForHumans()),
                TextEntry::make('updated_at')->label('Last modified at')->state(fn (Severity $record): ?string => $record->updated_at?->diffForHumans()),
            ])->columnSpan(['lg' => 1])->hidden(fn (?Severity $record) => $record === null),
        ])->columns(3);
    }
}
