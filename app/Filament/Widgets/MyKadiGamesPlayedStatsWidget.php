<?php

namespace App\Filament\Widgets;

use App\Facades\KadiApi;
use Filament\Notifications\Notification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MyKadiGamesPlayedStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected ?string $heading = 'Games Played Today';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['Tester', 'Player']) ?? false;
    }

    protected function getStats(): array
    {
        $apiError = false;

        try {
            $linkedId = auth()->user()?->linked_id;
            $data = Cache::remember('gms_player_games_stats', 120,
                function () use ($linkedId) {
                    return KadiApi::getPlayerStats($linkedId, today()->toDateString());
                }
            );
        } catch (\Throwable $e) {
            $apiError = true;
            Log::error("Kadi API player {$linkedId} stats failed", ['error' => $e->getMessage()]);
            $data = [];
        }

        if ($apiError) {
            Notification::make()
                ->title('Game API Unavailable')
                ->body('Could not connect to the wallet API. Stats shown may be incomplete.')
                ->warning()
                ->send();
        }

        return [
            Stat::make('Total Games Played', number_format($data['total'] ?? 0))
                ->description('All time games played')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('primary'),

            Stat::make('Regular Games', number_format($data['games'] ?? 0))
                ->description('Single & multiplayer games')
                ->descriptionIcon('heroicon-m-puzzle-piece')
                ->color('info'),

            Stat::make('Tournaments', number_format($data['tournament'] ?? 0))
                ->description('Tournament games played')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make('Jackpots', number_format($data['jackpots'] ?? 0))
                ->description('Jackpot games played')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
