<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Wallets\WalletResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class WalletRelationManager extends RelationManager
{
    protected static string $relationship = 'wallet';

    protected static ?string $relatedResource = WalletResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
