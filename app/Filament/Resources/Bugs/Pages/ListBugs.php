<?php

namespace App\Filament\Resources\Bugs\Pages;

use App\Filament\Resources\Bugs\BugResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBugs extends ListRecords
{
    protected static string $resource = BugResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
