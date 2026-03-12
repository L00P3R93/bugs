<?php

namespace App\Filament\Resources\Bugs\Pages;

use App\Filament\Resources\Bugs\BugResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBug extends ViewRecord
{
    protected static string $resource = BugResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
