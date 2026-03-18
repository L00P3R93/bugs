<?php

namespace App\Filament\Resources\Bugs\Pages;

use App\Filament\Resources\Bugs\Actions\ClosedBugAction;
use App\Filament\Resources\Bugs\Actions\DuplicateBugAction;
use App\Filament\Resources\Bugs\Actions\FixedBugAction;
use App\Filament\Resources\Bugs\Actions\PaidBugAction;
use App\Filament\Resources\Bugs\Actions\RejectedBugAction;
use App\Filament\Resources\Bugs\Actions\SubmittedBugAction;
use App\Filament\Resources\Bugs\Actions\UnderReviewBugAction;
use App\Filament\Resources\Bugs\BugResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBug extends ViewRecord
{
    protected static string $resource = BugResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DuplicateBugAction::make('duplicate_bug'),
            SubmittedBugAction::make('submitted_bug'),
            UnderReviewBugAction::make('under_review_bug'),
            RejectedBugAction::make('rejected_bug'),
            ClosedBugAction::make('closed_bug'),
            FixedBugAction::make('fixed_bug'),
            PaidBugAction::make('paid_bug'),
            EditAction::make(),
        ];
    }
}
