<?php

namespace App\Filament\Resources\Wallets\Schemas;

use App\Enums\WalletStatus;
use App\Models\Wallet;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class WalletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Wallet Details')->schema([
                TextInput::make('wallet_no')
                    ->label('Wallet Identifier')
                    ->prefixIcon('hugeicons-left-to-right-list-number')
                    ->prefixIconColor('primary')
                    ->disabled()
                    ->dehydrated()
                    ->unique(ignoreRecord: true)
                    ->default(fn () => 'WT'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT))
                    ->required(),
                Select::make('user_id')
                    ->label('Wallet Owner')
                    ->prefixIcon('hugeicons-user-roadside')
                    ->prefixIconColor('primary')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query, ?Wallet $record) => $query
                            ->when($record === null, fn ($q) => $q->whereDoesntHave('wallet'))
                            ->limit(10)
                            ->latest()
                    )
                    ->native(false)
                    ->searchable()
                    ->rules([fn (?Wallet $record) => Rule::unique('wallets', 'user_id')->ignore($record?->id)])
                    ->validationMessages(['unique' => 'This user already has a wallet.'])
                    ->required(),
                TextInput::make('balance')
                    ->label('Wallet Balance')
                    ->prefixIcon('hugeicons-wallet-add-02')
                    ->prefixIconColor('primary')
                    ->prefix('Ksh. ')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('status')
                    ->label('Wallet Status')
                    ->prefixIcon('hugeicons-status')
                    ->prefixIconColor('primary')
                    ->options(WalletStatus::class)
                    ->default('active')
                    ->native(false)
                    ->required(),
            ])->columns(2)->columnSpan(['lg' => fn (?Wallet $record) => $record === null ? 3 : 2]),
            Section::make()->schema([
                TextEntry::make('created_at')->state(fn (Wallet $record): ?string => $record->created_at?->diffForHumans()),
                TextEntry::make('updated_at')->label('Last modified at')->state(fn (Wallet $record): ?string => $record->updated_at?->diffForHumans()),
            ])->columnSpan(['lg' => 1])->hidden(fn (?Wallet $record) => $record === null),
        ])->columns(3);
    }
}
