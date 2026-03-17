<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Bugs\BugResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class BugsRelationManager extends RelationManager
{
    protected static string $relationship = 'bugs';

    protected static ?string $relatedResource = BugResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()->icon('hugeicons-new-releases')->label('Submit A Bug')->color('teal'),
            ]);
    }
}
