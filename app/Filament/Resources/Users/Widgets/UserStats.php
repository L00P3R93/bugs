<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Enums\UserStatus;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class UserStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListUsers::class;
    }

    protected function getStats(): array
    {
        $total = User::query()->count();
        $active = User::query()->where('status', UserStatus::Active)->count();
        $inactive = User::query()->where('status', UserStatus::Inactive)->count();
        $suspended = User::query()->where('status', UserStatus::Suspended)->count();
        $banned = User::query()->where('status', UserStatus::Banned)->count();
        $totalInactive = $inactive + $suspended + $banned;

        $testers = User::query()->role('Tester')->count();
        $activeTesters = User::query()->role('Tester')->where('status', UserStatus::Active)->count();

        $players = User::query()->role('Player')->count();
        $activePlayers = User::query()->role('Player')->where('status', UserStatus::Active)->count();

        $userData = Trend::model(User::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();

        $activeUserData = Trend::query(User::query()->where('status', UserStatus::Active))
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->count();

        // Tester users trend
        $testerData = Trend::query(User::query()->role('Tester'))
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->count();

        // Player users trend
        $playerData = Trend::query(User::query()->role('Player'))
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->count();

        return [
            Stat::make('Total Users', format_number($total))
                ->icon('hugeicons-user-group-03')
                ->description('All users joined')
                ->descriptionIcon('hugeicons-user-group-02')
                ->chart($userData->map(fn (TrendValue $value) => $value->aggregate)->toArray())
                ->color('primary'),

            Stat::make('Active Users', format_number($active))
                ->icon('hugeicons-user-check-01')
                ->description('Active users')
                ->descriptionIcon('hugeicons-user-check-02')
                ->chart($activeUserData->map(fn (TrendValue $value) => $value->aggregate)->toArray())
                ->color('success'),

            Stat::make('Players', format_number($activePlayers))
                ->icon('hugeicons-user-star-01')
                ->description('Active users with Player Role')
                ->descriptionIcon('hugeicons-user-star-02')
                ->chart($playerData->map(fn (TrendValue $value) => $value->aggregate)->toArray())
                ->color('purple'),

            Stat::make('Testers', format_number($activeTesters))
                ->icon('hugeicons-user-shield-01')
                ->description('Active users with Tester Role')
                ->descriptionIcon('hugeicons-user-shield-02')
                ->chart($testerData->map(fn (TrendValue $value) => $value->aggregate)->toArray())
                ->color('orange'),
        ];
    }
}
