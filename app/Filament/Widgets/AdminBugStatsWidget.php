<?php

namespace App\Filament\Widgets;

use App\Enums\BugStatus;
use App\Models\Bug;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class AdminBugStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function applyPeriodFilter(Builder $query, string $column = 'created_at'): Builder
    {
        return match ($this->pageFilters['period'] ?? 'all_time') {
            'today' => $query->whereDate($column, today()),
            'this_week' => $query->whereBetween($column, [now()->startOfWeek(), now()->endOfWeek()]),
            'this_month' => $query->whereMonth($column, now()->month)->whereYear($column, now()->year),
            default => $query,
        };
    }

    protected function getPeriodLabel(): string
    {
        return match ($this->pageFilters['period'] ?? 'all_time') {
            'today' => 'Today',
            'this_week' => 'This Week',
            'this_month' => 'This Month',
            default => 'All Time',
        };
    }

    protected function getStats(): array
    {
        $label = $this->getPeriodLabel();

        $total = $this->applyPeriodFilter(Bug::query())->count();
        $submitted = $this->applyPeriodFilter(Bug::query())->where('status', BugStatus::SUBMITTED)->count();
        $underReview = $this->applyPeriodFilter(Bug::query())
            ->whereIn('status', [BugStatus::UNDER_REVIEW, BugStatus::TRIAGED, BugStatus::VALIDATED])
            ->count();
        $fixed = $this->applyPeriodFilter(Bug::query())->where('status', BugStatus::FIXED)->count();
        $paid = $this->applyPeriodFilter(Bug::query())->where('status', BugStatus::PAID)->count();
        $closed = $this->applyPeriodFilter(Bug::query())->where('status', BugStatus::CLOSED)->count();
        $invalid = $this->applyPeriodFilter(Bug::query())
            ->whereIn('status', [BugStatus::REJECTED, BugStatus::WONT_FIX, BugStatus::DUPLICATE])
            ->count();

        return [
            Stat::make("Total Bugs ({$label})", number_format($total))
                ->description('All bugs uploaded')
                ->descriptionIcon('heroicon-m-bug-ant')
                ->color('primary'),

            Stat::make("Submitted ({$label})", number_format($submitted))
                ->description('Awaiting initial review')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),

            Stat::make("Under Review ({$label})", number_format($underReview))
                ->description('Triaged, under review & validated')
                ->descriptionIcon('heroicon-m-eye')
                ->color('warning'),

            Stat::make("Fixed ({$label})", number_format($fixed))
                ->description('Successfully patched')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('success'),

            Stat::make("Paid ({$label})", number_format($paid))
                ->description('Rewarded to testers')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make("Closed ({$label})", number_format($closed))
                ->description('Closed without fix')
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('gray'),

            Stat::make("Invalid ({$label})", number_format($invalid))
                ->description("Rejected, duplicate or won't fix")
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
