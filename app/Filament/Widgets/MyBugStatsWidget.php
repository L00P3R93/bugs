<?php

namespace App\Filament\Widgets;

use App\Enums\BugStatus;
use App\Models\Bug;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;

class MyBugStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 4;

    protected static ?int $sort = 1;

    protected ?string $heading = 'Bugs Summary';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['Tester', 'Player']) ?? false;
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
        $totalSubmitted = $submitted + $underReview;
        $fixed = $baseQuery()->where('status', BugStatus::FIXED)->count();
        $paid = $baseQuery()->where('status', BugStatus::PAID)->count();
        $closed = $baseQuery()->where('status', BugStatus::CLOSED)->count();
        $totalFixed = $fixed + $closed;

        $totalEarned = (float) Bug::query()
            ->where('reporter_id', $user->id)
            ->where('status', BugStatus::PAID)
            ->sum('final_amount');

        $bugsData = Trend::model(Bug::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();

        $submittedData = Trend::query(Bug::query()->whereIn('status', [BugStatus::SUBMITTED, BugStatus::UNDER_REVIEW]))
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->count();

        $fixedData = Trend::query(Bug::query()->whereIn('status', [BugStatus::FIXED, BugStatus::CLOSED]))
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->count();

        $paidData = Trend::query(Bug::query()->where('status', BugStatus::PAID))
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->count();

        return [
            Stat::make('Total Bugs', format_number($total))
                ->icon('hugeicons-bug-02')
                ->description('All bugs uploaded')
                ->descriptionIcon('heroicon-m-bug-ant')
                ->chart($bugsData->map(fn (TrendValue $value) => $value->aggregate)->toArray())
                ->color('primary'),

            Stat::make('Submitted', format_number($totalSubmitted))
                ->icon('heroicon-o-document')
                ->description('Submitted & Under Review Bugs')
                ->descriptionIcon('heroicon-m-document-text')
                ->chart($submittedData->map(fn (TrendValue $value) => $value->aggregate)->toArray())
                ->color('teal'),

            Stat::make('Fixed', format_number($totalFixed))
                ->icon('hugeicons-wrench-01')
                ->description('Successfully patched & Closed')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->chart($fixedData->map(fn (TrendValue $value) => $value->aggregate)->toArray())
                ->color('purple'),

            Stat::make('Paid', format_number($paid))
                ->icon('hugeicons-receipt-dollar')
                ->description('Rewarded to testers')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->chart($paidData->map(fn (TrendValue $value) => $value->aggregate)->toArray())
                ->color('success'),

            Stat::make('Total Earnings', number_format($totalEarned, 2))
                ->description('All-time earnings from paid bugs')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
