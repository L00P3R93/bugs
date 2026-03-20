<?php

namespace App\Filament\Widgets;

use App\Facades\KadiApi;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class KadiCustomerStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 4;

    protected ?string $heading = 'Players Joined';

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getStats(): array
    {
        $data = Cache::remember('kadi_stats_customers', now()->addMinutes(5), function (): array {
            try {
                $response = KadiApi::get('stats/customers');

                return $response['data'] ?? [];
            } catch (RequestException $e) {
                Log::error('Kadi API customers stats failed', ['error' => $e->getMessage()]);

                return [];
            }
        });

        return [
            Stat::make('Joined Today', number_format($data['today'] ?? 0))
                ->description('New accounts today')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary'),

            Stat::make('Joined This Week', number_format($data['this_week'] ?? 0))
                ->description('New accounts this week')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Joined This Month', number_format($data['this_month'] ?? 0))
                ->description('New accounts this month')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),

            Stat::make('Joined This Year', number_format($data['this_year'] ?? 0))
                ->description('New accounts this year')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
        ];
    }
}
