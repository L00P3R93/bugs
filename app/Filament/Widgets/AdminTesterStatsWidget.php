<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminTesterStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getStats(): array
    {
        $total = User::role('Tester')->count();
        $today = User::role('Tester')->whereDate('created_at', today())->count();
        $thisWeek = User::role('Tester')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        $thisMonth = User::role('Tester')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $verified = User::role('Tester')->whereNotNull('email_verified_at')->count();
        $unverified = $total - $verified;

        return [
            Stat::make('Total Testers', number_format($total))
                ->description('All registered testers')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Joined Today', number_format($today))
                ->description('New sign-ups today')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),

            Stat::make('Joined This Week', number_format($thisWeek))
                ->description('New sign-ups this week')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),

            Stat::make('Joined This Month', number_format($thisMonth))
                ->description('New sign-ups this month')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('warning'),

            Stat::make('Verified Testers', number_format($verified))
                ->description('Email-verified accounts')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Unverified Testers', number_format($unverified))
                ->description('Pending email verification')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('danger'),
        ];
    }
}
