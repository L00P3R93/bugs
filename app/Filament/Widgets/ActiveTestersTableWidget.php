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

    protected ?string $pollingInterval = '1h';

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
                    ->leftJoin('wallets', 'wallets.user_id', '=', 'users.id')
                    ->withCount([
                        'bugs as total_bugs_count',
                    ])
                    ->withSum(
                        ['bugs as total_bug_earned' => fn ($q) => $q->where('status', BugStatus::PAID)],
                        'final_amount'
                    )
                    ->selectRaw('users.*, wallets.daily_games_played, wallets.total_earned as wallet_total_earned')
                    ->orderByDesc('wallet_total_earned')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tester')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn (User $record): string => '@'.($record->username ?? $record->account_no)),

                Tables\Columns\TextColumn::make('total_bugs_count')
                    ->label('Total Bugs')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('daily_games_played')
                    ->label('Total Played')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->default(0),

                Tables\Columns\TextColumn::make('wallet_total_earned')
                    ->label('Wallet Earned')
                    ->sortable()
                    ->numeric(decimalPlaces: 2)
                    ->prefix('KES ')
                    ->default('0.00'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('wallet_total_earned', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }
}
