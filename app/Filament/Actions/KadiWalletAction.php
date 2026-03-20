<?php

namespace App\Filament\Actions;

use App\Facades\KadiApi;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class KadiWalletAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->tooltip('Manage Wallet')
            ->color('yellow')
            ->iconButton()
            ->icon(Heroicon::OutlinedWallet)
            ->visible(fn (array $record): bool => ($record['wallet_id'] ?? 0) > 0)
            ->slideOver()
            ->modalWidth('md')
            ->fillForm(function (array $record): array {
                return [
                    'id' => $record['wallet_id'],
                    'name' => $record['name'] ?? 'Unknown',
                    'current_balance' => $record['balance'] ?? 0,
                    'balance' => null,
                ];
            })
            ->schema([
                Hidden::make('id'),
                Section::make('Customer Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Customer Name')
                            ->readonly()
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->prefixIconColor('primary'),

                        TextInput::make('current_balance')
                            ->label('Current Balance')
                            ->readonly()
                            ->numeric()
                            ->prefix('KES')
                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                            ->prefixIconColor('primary')
                            ->formatStateUsing(fn ($state) => $state),
                    ]),

                Section::make('Update Balance')
                    ->schema([
                        TextInput::make('balance')
                            ->label('New Balance')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('KES')
                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                            ->prefixIconColor('primary')
                            ->placeholder('Enter new balance amount'),
                    ]),
            ])
            ->action(function (array $data, $livewire): void {
                try {
                    KadiApi::put('wallets/'.encryptOpenSSL($data['id']), [
                        'balance' => $data['balance'],
                    ]);

                    Cache::forget('kadi_accounts');
                    $livewire->resetTable();

                    Notification::make()
                        ->title('Wallet Updated')
                        ->body('Wallet balance updated successfully.')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Log::error('Kadi API wallet update failed', ['error' => $e->getMessage()]);

                    Notification::make()
                        ->title('Wallet Update Failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    throw $e;
                }
            });
    }
}
