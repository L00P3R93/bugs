<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->icon('hugeicons-file-view')->label('View Transaction')->color('teal'),
            DeleteAction::make()->icon('hugeicons-delete-04')->label('Delete Transaction')->color('danger'),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
