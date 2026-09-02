<?php

namespace App\Filament\Resources\Transactions\Actions;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Withdraw;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
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
            ->visible(fn () => $wallet && $wallet->available_balance >= 50 && ! $wallet->is_locked)
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
                            ->required()
                            ->tel()
                            ->regex('/^(07\d{8}|01\d{8}|2547\d{8}|2541\d{8})$/')
                            ->helperText('Allowed formats: 07XXXXXXXX, 01XXXXXXXX, 2547XXXXXXXX, 2541XXXXXXXX')
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
            ->requiresConfirmation()
            ->modalHeading('Confirm Withdrawal')
            ->modalDescription(fn (?array $state): string => 'Are you sure you want to withdraw KES '.($state['amount'] ?? '').' to phone number '.($state['phone'] ?? '').'? Funds will be received in this number.')
            ->modalSubmitActionLabel('Yes, Submit Withdrawal')
            ->action(function (array $data, $livewire) use ($wallet, $user): void {
                $phone = $data['phone'];

                if (blank($phone)) {
                    Notification::make()
                        ->title('Phone Number Required')
                        ->body('Please enter a phone number to receive the withdrawal funds.')
                        ->danger()
                        ->persistent()
                        ->send();

                    return;
                }

                try {
                    $amount = $data['amount'];

                    DB::transaction(function () use ($amount, $phone, $wallet, $user, $livewire) {
                        // Re-fetch wallet inside transaction with row lock to prevent overdraw
                        $wallet = $wallet->lockForUpdate()->first();

                        if ($wallet->available_balance < $amount) {
                            Notification::make()
                                ->title('Insufficient Balance')
                                ->body('You do not have sufficient available balance for this withdrawal.')
                                ->danger()
                                ->persistent()
                                ->send();

                            return;
                        }

                        // Save phone to user account if it was blank
                        if (blank($user->phone)) {
                            $user->phone = $phone;
                            $user->save();
                        }

                        // Reserve funds: move from available to pending
                        $wallet->decrement('available_balance', $amount);
                        $wallet->increment('pending_balance', $amount);

                        // Create transaction
                        $transaction = $wallet->transactions()->create([
                            'transaction_no' => 'TRS'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
                            'user_id' => $user->id,
                            'amount' => $amount,
                            'net_amount' => $amount,
                            'currency' => 'KES',
                            'exchange_rate' => 1,
                            'type' => TransactionType::WITHDRAW,
                            'status' => TransactionStatus::PENDING_APPROVAL,
                        ]);

                        // Create the Withdraw record linked to the transaction
                        Withdraw::create([
                            'wallet_id' => $wallet->id,
                            'transaction_id' => $transaction->id,
                            'phone' => $phone,
                            'amount' => $amount,
                            'balance' => $wallet->balance,
                            'status' => TransactionStatus::PENDING_APPROVAL,
                        ]);

                        $livewire->resetTable();

                        Notification::make()
                            ->title('Withdrawal Request Submitted')
                            ->body('Your withdrawal request of KES '.$amount.' has been submitted and is pending admin approval.')
                            ->success()
                            ->send();
                    });
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
