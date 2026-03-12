<?php

namespace App\Filament\Resources\Severities\Pages;

use App\Filament\Resources\Severities\SeverityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeverities extends ListRecords
{
    protected static string $resource = SeverityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
