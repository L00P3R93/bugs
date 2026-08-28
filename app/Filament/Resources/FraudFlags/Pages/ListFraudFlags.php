<?php

namespace App\Filament\Resources\FraudFlags\Pages;

use App\Filament\Resources\FraudFlags\FraudFlagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFraudFlags extends ListRecords
{
    protected static string $resource = FraudFlagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
