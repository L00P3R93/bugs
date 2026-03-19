<?php

namespace App\Filament\Widgets;

use App\Enums\BugStatus;
use App\Filament\Resources\Bugs\BugResource;
use App\Models\Bug;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyRecentBugsWidget extends BaseWidget
{
    protected static ?string $heading = 'My Recent Bugs';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return auth()->user()?->isTester() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Bug::query()
                    ->where('reporter_id', auth()->id())
                    ->with(['category', 'severity'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('bug_no')
                    ->label('Bug #')
                    ->searchable()
                    ->copyable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                Tables\Columns\TextColumn::make('severity.name')
                    ->label('Severity')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('final_amount')
                    ->label('Reward')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('KES ')
                    ->placeholder('—')
                    ->sortable()
                    ->visible(fn (?Bug $record): bool => $record?->status === BugStatus::PAID),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->recordUrl(
                fn (Bug $record): string => BugResource::getUrl('view', ['record' => $record])
            );
    }
}
