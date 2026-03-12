<?php

namespace App\Filament\Resources\Wallets\Schemas;

use App\Enums\WalletStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WalletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('wallet_no')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('status')
                    ->options(WalletStatus::class)
                    ->default('active')
                    ->required(),
            ]);
    }
}
