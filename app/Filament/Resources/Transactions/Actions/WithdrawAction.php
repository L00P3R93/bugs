<?php

namespace App\Filament\Resources\Transactions\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Log;

class WithdrawAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $user = auth()->user();
        $wallet = $user->wallet;

        $this
            ->tooltip('Request Withdrawal')
            ->color('indigo')
            ->label('Request Withdrawal')
            ->icon('hugeicons-money-send-flow-02')
            ->visible(fn () => $wallet && $wallet->balance >= 150 && $wallet->hasReachedDailyTarget())
            ->slideOver()
            ->modalWidth('md')
            ->fillForm([
                'name' => $user->name,
                'phone' => $user->phone,
                'current_balance' => $wallet?->balance ?? 0,
                'amount' => null,
                'wallet_id' => $wallet?->id,
            ])
            ->schema([
                Hidden::make('wallet_id'),
                Section::make('Customer Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Customer Name')
                            ->readonly()
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->prefixIconColor('primary'),

                        TextInput::make('phone')
                            ->label('Receiver Phone')
                            ->readonly()
                            ->prefixIcon(Heroicon::OutlinedPhone)
                            ->prefixIconColor('primary'),

                        TextInput::make('current_balance')
                            ->label('Current Balance')
                            ->readonly()
                            ->numeric()
                            ->prefix('KES')
                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                            ->prefixIconColor('primary'),
                    ]),
                Section::make('Amount To Withdraw')
                    ->schema([
                        TextInput::make('amount')
                            ->label('Amount To Withdraw')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('KES')
                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                            ->prefixIconColor('primary')
                            ->placeholder('Enter amount to withdraw'),
                    ]),
            ])
            ->action(function (array $data, $livewire) use ($wallet, $user): void {
                if (blank($user->phone)) {
                    Notification::make()
                        ->title('Phone Number Required')
                        ->body('Please add a phone number to your profile before requesting a withdrawal.')
                        ->danger()
                        ->persistent()
                        ->send();

                    return;
                }

                try {
                    $wallet->transactions()->create([
                        'amount' => $data['amount'],
                        'type' => 'withdraw',
                    ]);
                    $livewire->resetTable();

                    Notification::make()
                        ->title('Withdrawal Request Submitted')
                        ->body('Your withdrawal request of KES '.$data['amount'].' has been submitted and is pending admin approval.')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Log::error('Withdraw Action failed: ', ['error' => $e->getMessage()]);

                    Notification::make()
                        ->title('Withdrawal Request Failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    throw $e;
                }
            });
    }
}
