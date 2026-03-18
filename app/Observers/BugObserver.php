<?php

namespace App\Observers;

use App\Enums\BugStatus;
use App\Models\Bug;
use App\Models\Category;
use App\Models\Severity;

class BugObserver
{
    public function creating(Bug $bug): void
    {
        if (! $bug->bug_no) {
            do {
                $bugNo = 'BUG'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            } while (Bug::query()->where('bug_no', $bugNo)->exists());

            $bug->bug_no = $bugNo;
        }

        if (! app()->runningInConsole() || app()->environment('testing')) {
            $user = auth()->user();
            if ($user) {
                $bug->reporter_id = $user->id;
            }
        }
    }

    public function created(Bug $bug): void
    {
        $bug->base_amount = $bug->getBaseAmount();
        $bug->final_amount = $bug->getFinalAmount();
        $bug->remarks = 'Bug '.self::badge($bug->bug_no, 'primary')
            .' submitted by '.self::badge($bug->reporter->name, 'info')
            .' on '.self::badge(now()->toDateTimeString(), 'gray');
        $bug->saveQuietly();
    }

    public function updated(Bug $bug): void
    {
        $actor = self::badge(auth()->user()?->name ?? 'System', 'info');
        $timestamp = self::badge(now()->toDateTimeString(), 'gray');
        $newEntries = [];
        $recalculateAmounts = false;

        if ($bug->wasChanged('status')) {
            $oldStatus = BugStatus::from($bug->getRawOriginal('status'));
            $newEntries[] = 'Status changed from '.self::badge($oldStatus->getLabel(), 'gray')
                .' to '.self::badge($bug->status->getLabel(), (string) $bug->status->getColor())
                .' by '.$actor.' on '.$timestamp;
        }

        if ($bug->wasChanged('category_id')) {
            $oldCategory = Category::query()->find($bug->getOriginal('category_id'));
            $newEntries[] = 'Category changed from '.self::badge($oldCategory?->name ?? '—', 'gray')
                .' to '.self::badge($bug->category->name, 'primary')
                .' by '.$actor.' on '.$timestamp;
            $recalculateAmounts = true;
        }

        if ($bug->wasChanged('severity_id')) {
            $oldSeverity = Severity::query()->find($bug->getOriginal('severity_id'));
            $newEntries[] = 'Severity changed from '.self::badge($oldSeverity?->name ?? '—', 'gray')
                .' to '.self::badge($bug->severity->name, 'warning')
                .' by '.$actor.' on '.$timestamp;
            $recalculateAmounts = true;
        }

        if ($bug->wasChanged('is_paid') && $bug->is_paid) {
            $newEntries[] = 'Bug awarded and marked as paid by '.$actor.' on '.$timestamp;
        }

        if ($recalculateAmounts) {
            $bug->load(['category', 'severity']);
            $bug->base_amount = $bug->getBaseAmount();
            $bug->final_amount = $bug->getFinalAmount();
        }

        if (! empty($newEntries) || $recalculateAmounts) {
            if (! empty($newEntries)) {
                $existing = $bug->remarks ?? '';
                $separator = $existing !== '' ? "\n" : '';
                $bug->remarks = $existing.$separator.implode("\n", $newEntries);
            }

            $bug->saveQuietly();
        }
    }

    public function deleted(Bug $bug): void {}

    public function restored(Bug $bug): void {}

    public function forceDeleted(Bug $bug): void {}

    /**
     * Generate a badge using Filament's own fi-badge CSS classes, which are
     * always compiled into Filament's CSS and support dark mode out of the box.
     */
    private static function badge(string $text, string $color = 'gray'): string
    {
        $safeColor = in_array($color, ['primary', 'success', 'danger', 'warning', 'info', 'gray'], true)
            ? $color
            : 'gray';

        return '<span class="fi-color fi-color-'.$safeColor.' fi-text-color-700 dark:fi-text-color-300 fi-badge fi-size-sm">'.e($text).'</span>';
    }
}
