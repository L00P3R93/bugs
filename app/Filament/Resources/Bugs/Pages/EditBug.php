<?php

namespace App\Filament\Resources\Bugs\Pages;

use App\Filament\Resources\Bugs\BugResource;
use App\Filament\Resources\Bugs\RelationManagers\MediaRelationManager;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBug extends EditRecord
{
    protected static string $resource = BugResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->dispatch('$refresh')->to(MediaRelationManager::class);
    }
}
