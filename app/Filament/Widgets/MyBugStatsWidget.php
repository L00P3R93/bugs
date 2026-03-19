<?php

namespace App\Filament\Widgets;

use App\Enums\BugStatus;
use App\Models\Bug;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class MyBugStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->isTester() ?? false;
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
        $user = auth()->user();
        $label = $this->getPeriodLabel();

        $baseQuery = fn () => $this->applyPeriodFilter(
            Bug::query()->where('reporter_id', $user->id)
        );

        $total = $baseQuery()->count();
        $submitted = $baseQuery()->where('status', BugStatus::SUBMITTED)->count();
        $underReview = $baseQuery()
            ->whereIn('status', [BugStatus::UNDER_REVIEW, BugStatus::TRIAGED, BugStatus::VALIDATED])
            ->count();
        $fixed = $baseQuery()->where('status', BugStatus::FIXED)->count();
        $paid = $baseQuery()->where('status', BugStatus::PAID)->count();
        $closed = $baseQuery()->where('status', BugStatus::CLOSED)->count();
        $invalid = $baseQuery()
            ->whereIn('status', [BugStatus::REJECTED, BugStatus::WONT_FIX, BugStatus::DUPLICATE])
            ->count();
        $totalEarned = (float) Bug::query()
            ->where('reporter_id', $user->id)
            ->where('status', BugStatus::PAID)
            ->sum('final_amount');

        return [
            Stat::make("My Bugs ({$label})", number_format($total))
                ->description('Total bugs you have reported')
                ->descriptionIcon('heroicon-m-bug-ant')
                ->color('primary'),

            Stat::make("Submitted ({$label})", number_format($submitted))
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),

            Stat::make("Under Review ({$label})", number_format($underReview))
                ->description('Being reviewed by the team')
                ->descriptionIcon('heroicon-m-eye')
                ->color('warning'),

            Stat::make("Fixed ({$label})", number_format($fixed))
                ->description('Your bugs that were fixed')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('success'),

            Stat::make("Paid ({$label})", number_format($paid))
                ->description('Bugs you were rewarded for')
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

            Stat::make('Total Earnings', number_format($totalEarned, 2))
                ->description('All-time earnings from paid bugs')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
