<?php

namespace App\Filament\Resources\Bugs\Pages;

use App\Enums\BugStatus;
use App\Filament\Resources\Bugs\BugResource;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListBugs extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = BugResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon('hugeicons-plus-sign-circle')->label('Submit A Bug')->color('teal'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return BugResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'submitted' => Tab::make('Submitted')->query(fn ($query) => $query->status(BugStatus::SUBMITTED)),
            'under_review' => Tab::make('Under Review')->query(fn ($query) => $query->status(BugStatus::UNDER_REVIEW)),
            'fixed' => Tab::make('Fixed')->query(fn ($query) => $query->status(BugStatus::FIXED)),
            'paid' => Tab::make('Paid')->query(fn ($query) => $query->status(BugStatus::PAID)),
            'closed' => Tab::make('Closed')->query(fn ($query) => $query->status(BugStatus::CLOSED)),
        ];
    }
}
