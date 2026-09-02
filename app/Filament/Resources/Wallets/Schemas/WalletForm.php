<?php

namespace App\Filament\Resources\Wallets\Schemas;

use App\Enums\WalletStatus;
use App\Models\Wallet;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                    ->label('Total Balance')
                    ->prefixIcon('hugeicons-wallet-add-02')
                    ->prefixIconColor('primary')
                    ->prefix('KES ')
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
            ])->columns(2)->columnSpan(['lg' => 3]),

            Section::make('Enhanced Wallet Details')->schema([
                TextInput::make('available_balance')
                    ->label('Available Balance')
                    ->prefixIcon('hugeicons-wallet-add-02')
                    ->prefixIconColor('success')
                    ->prefix('KES ')
                    ->readonly()
                    ->numeric(),
                TextInput::make('pending_balance')
                    ->label('Pending Balance (7-day hold)')
                    ->prefixIcon('hugeicons-clock-01')
                    ->prefixIconColor('warning')
                    ->prefix('KES ')
                    ->readonly()
                    ->numeric(),
                TextInput::make('total_earned')
                    ->label('Total Earned')
                    ->prefixIcon('hugeicons-money-receive-square')
                    ->prefixIconColor('primary')
                    ->prefix('KES ')
                    ->readonly()
                    ->numeric(),
                TextInput::make('daily_withdrawal_limit')
                    ->label('Daily Withdrawal Limit')
                    ->prefixIcon('hugeicons-calendar-01')
                    ->prefixIconColor('info')
                    ->prefix('KES ')
                    ->numeric()
                    ->default(50000),
                TextInput::make('monthly_withdrawal_limit')
                    ->label('Monthly Withdrawal Limit')
                    ->prefixIcon('hugeicons-calendar-03')
                    ->prefixIconColor('info')
                    ->prefix('KES ')
                    ->numeric()
                    ->default(500000),
            ])->columns(3)->columnSpan(['lg' => 3]),

            Section::make('Security')->schema([
                Toggle::make('is_locked')
                    ->label('Wallet Locked')
                    ->reactive()
                    ->afterStateUpdated(fn ($state, $set) => $set('locked_reason', $state ? null : null)),
                TextInput::make('locked_reason')
                    ->label('Lock Reason')
                    ->placeholder('Enter reason for locking this wallet')
                    ->visible(fn ($get) => $get('is_locked'))
                    ->columnSpanFull(),
                DatePicker::make('locked_at')
                    ->label('Locked At')
                    ->visible(fn ($get) => $get('is_locked'))
                    ->readonly(),
            ])->columns(2)->columnSpan(['lg' => 3]),
        ])->columns(3);
    }
}
