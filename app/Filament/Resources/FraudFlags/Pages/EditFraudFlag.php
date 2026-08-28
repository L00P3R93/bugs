<?php

namespace App\Filament\Resources\FraudFlags\Pages;

use App\Filament\Resources\FraudFlags\FraudFlagResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditFraudFlag extends EditRecord
{
    protected static string $resource = FraudFlagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
