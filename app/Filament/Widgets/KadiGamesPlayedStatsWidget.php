<?php

namespace App\Filament\Widgets;

use App\Facades\KadiApi;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class KadiGamesPlayedStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 5;

    protected ?string $heading = 'Games Played';

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getStats(): array
    {
        $data = Cache::remember('kadi_stats_played', now()->addMinutes(5), function (): array {
            try {
                $response = KadiApi::get('stats/played');

                return $response['data'] ?? [];
            } catch (RequestException $e) {
                Log::error('Kadi API played stats failed', ['error' => $e->getMessage()]);

                return [];
            }
        });

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
