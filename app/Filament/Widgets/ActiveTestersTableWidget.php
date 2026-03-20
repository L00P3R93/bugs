<?php

namespace App\Filament\Widgets;

use App\Enums\BugStatus;
use App\Models\User;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ActiveTestersTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Most Active Testers';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 6;

    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->role('Tester')
                    ->withCount([
                        'bugs as total_bugs_count',
                        'bugs as paid_bugs_count' => fn ($q) => $q->where('status', BugStatus::PAID),
                        'bugs as fixed_bugs_count' => fn ($q) => $q->where('status', BugStatus::FIXED),
                        'bugs as rejected_bugs_count' => fn ($q) => $q->whereIn('status', [BugStatus::REJECTED, BugStatus::WONT_FIX, BugStatus::DUPLICATE]),
                    ])
                    ->withSum(
                        ['bugs as total_earned' => fn ($q) => $q->where('status', BugStatus::PAID)],
                        'final_amount'
                    )
                    ->orderByDesc('total_bugs_count')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tester')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn (User $record): string => '@'.($record->username ?? $record->account_no)),

                Tables\Columns\TextColumn::make('account_no')
                    ->label('Account No')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('total_bugs_count')
                    ->label('Total Bugs')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('paid_bugs_count')
                    ->label('Paid')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('fixed_bugs_count')
                    ->label('Fixed')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('rejected_bugs_count')
                    ->label('Invalid')
                    ->sortable()
                    ->badge()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('total_earned')
                    ->label('Total Earned')
                    ->sortable()
                    ->numeric(decimalPlaces: 2)
                    ->prefix('KES ')
                    ->default('0.00'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('total_bugs_count', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }
}
