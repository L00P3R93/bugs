<?php

namespace App\Filament\Resources\Bugs\Widgets;

use App\Enums\BugStatus;
use App\Filament\Resources\Bugs\Pages\ListBugs;
use App\Models\Bug;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class BugStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListBugs::class;
    }

    protected function getStats(): array
    {
        $total = Bug::query()->count();
        $submitted = Bug::query()->where('status', BugStatus::SUBMITTED)->count();
        $underReview = Bug::query()
            ->whereIn('status', [BugStatus::UNDER_REVIEW, BugStatus::TRIAGED, BugStatus::VALIDATED])
            ->count();
        $totalSubmitted = $submitted + $underReview;
        $fixed = Bug::query()->where('status', BugStatus::FIXED)->count();
        $paid = Bug::query()->where('status', BugStatus::PAID)->count();
        $closed = Bug::query()->where('status', BugStatus::CLOSED)->count();
        $totalFixed = $fixed + $closed;

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
        ];
    }
}
