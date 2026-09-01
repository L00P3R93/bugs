<?php

namespace App\Filament\Actions;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Models\Withdraw;
use App\Notifications\WithdrawalApprovedNotification;
use App\Notifications\WithdrawalRejectedNotification;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

class ApproveWithdrawalAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Review Withdrawal')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('warning')
            ->slideOver()
            ->modalWidth('md')
            ->visible(fn (Transaction $record): bool => auth()->user()->isAdmin() && $record->type->value === 'withdraw' && $record->status === TransactionStatus::PENDING_APPROVAL)
            ->fillForm(function (Transaction $record): array {
                $withdraw = $record->withdraw;

                return [
                    'transaction_id' => $record->id,
                    'withdraw_id' => $withdraw?->id,
                    'user_name' => $record->wallet->user->name ?? 'Unknown',
                    'amount' => $record->amount,
                    'phone' => $withdraw?->phone ?? '-',
                    'wallet_balance' => $record->wallet->balance ?? 0,
                    'daily_games_played' => $record->wallet->daily_games_played ?? 0,
                    'action' => null,
                    'rejection_reason' => null,
                ];
            })
            ->schema([
                Hidden::make('transaction_id'),
                Hidden::make('withdraw_id'),
                Section::make('Withdrawal Details')
                    ->schema([
                        TextInput::make('user_name')
                            ->label('Tester')
                            ->readonly()
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->prefixIconColor('primary'),
                        TextInput::make('amount')
                            ->label('Amount (KES)')
                            ->readonly()
                            ->numeric()
                            ->prefix('KES')
                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                            ->prefixIconColor('primary'),
                        TextInput::make('phone')
                            ->label('M-Pesa Phone')
                            ->readonly()
                            ->prefixIcon(Heroicon::OutlinedPhone)
                            ->prefixIconColor('primary'),
                        TextInput::make('wallet_balance')
                            ->label('Current Wallet Balance')
                            ->readonly()
                            ->numeric()
                            ->prefix('KES')
                            ->prefixIcon(Heroicon::OutlinedWallet)
                            ->prefixIconColor('primary'),
                        TextInput::make('daily_games_played')
                            ->label('Games Played Today')
                            ->readonly()
                            ->prefixIcon(Heroicon::OutlinedCalculator)
                            ->prefixIconColor('primary'),
                    ]),
                Section::make('Decision')
                    ->schema([
                        Select::make('action')
                            ->label('Action')
                            ->options([
                                'approve' => 'Approve',
                                'reject' => 'Reject',
                            ])
                            ->required()
                            ->live(),
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->visible(fn (Get $get): bool => $get('action') === 'reject')
                            ->placeholder('Enter reason for rejection'),
                    ]),
            ])
            ->action(function (array $data, $livewire): void {
                $withdraw = Withdraw::find($data['withdraw_id']);
                $transaction = Transaction::find($data['transaction_id']);

                if (! $withdraw || ! $transaction) {
                    Notification::make()
                        ->title('Error')
                        ->body('Withdrawal record not found.')
                        ->danger()
                        ->send();

                    return;
                }

                if ($data['action'] === 'approve') {
                    // Approve: set Withdraw to PENDING (triggers M-Pesa queue)
                    $withdraw->approve(auth()->id());

                    // Lock wallet and finalize withdrawal
                    $wallet = $transaction->wallet()->lockForUpdate()->first();
                    $wallet->decrement('balance', $transaction->amount);
                    $wallet->decrement('pending_balance', $transaction->amount);

                    // Update transaction status to PENDING
                    $transaction->update([
                        'status' => TransactionStatus::PENDING,
                    ]);

                    // Notify tester
                    $transaction->wallet->user->notify(new WithdrawalApprovedNotification($withdraw));

                    Notification::make()
                        ->title('Withdrawal Approved')
                        ->body("KES {$withdraw->amount} withdrawal for {$withdraw->wallet->user->name} has been approved.")
                        ->success()
                        ->send();
                } else {
                    // Reject
                    $withdraw->reject(auth()->id(), $data['rejection_reason']);

                    // Lock wallet and restore funds
                    $wallet = $transaction->wallet()->lockForUpdate()->first();
                    $wallet->increment('available_balance', $transaction->amount);
                    $wallet->decrement('pending_balance', $transaction->amount);

                    // Mark transaction as failed
                    $transaction->update([
                        'status' => TransactionStatus::FAILED,
                        'cancelled_at' => now(),
                    ]);

                    // Notify tester
                    $transaction->wallet->user->notify(new WithdrawalRejectedNotification($withdraw, $data['rejection_reason']));

                    Notification::make()
                        ->title('Withdrawal Rejected')
                        ->body("KES {$withdraw->amount} withdrawal for {$withdraw->wallet->user->name} has been rejected.")
                        ->danger()
                        ->send();
                }

                $livewire->resetTable();
            });
    }
}
