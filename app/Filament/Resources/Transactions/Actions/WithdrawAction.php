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
use Illuminate\Validation\ValidationException;

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

            ->visible(fn () => $wallet
                && $wallet->available_balance >= 50
                && ! $wallet->is_locked
            )

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
                            ->helperText(
                                'Allowed formats: 07XXXXXXXX, 01XXXXXXXX, 2547XXXXXXXX, 2541XXXXXXXX'
                            )
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
                            ->minValue(50)
                            ->maxValue(fn () => $wallet?->available_balance ?? 0)
                            ->prefix('KES')
                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                            ->prefixIconColor('primary')
                            ->placeholder('Enter amount to withdraw')
                            ->helperText(
                                fn () => 'Available balance: KES ' .
                                    number_format(
                                        $wallet?->available_balance ?? 0,
                                        2
                                    )
                            ),
                    ]),
            ])

            /*
             * The parent action must NOT submit the withdrawal directly.
             *
             * Instead, the custom "Submit Withdrawal" child action below
             * opens the confirmation modal.
             */
            ->modalSubmitAction(false)

            ->extraModalFooterActions(function (Action $action): array {
                return [
                    Action::make('submitWithdrawal')
                        ->label('Submit Withdrawal')
                        ->color('primary')

                        /*
                         * This is now a REAL child Action.
                         *
                         * Therefore requiresConfirmation() opens a separate
                         * confirmation modal instead of submitting the parent.
                         */
                        ->requiresConfirmation()

                        ->modalHeading('Confirm Withdrawal')

                        ->modalDescription(
                            function (array $mountedActions): string {
                                /*
                                 * The first mounted action is the parent
                                 * WithdrawAction containing the form.
                                 */
                                $parentAction = $mountedActions[0] ?? null;

                                if (! $parentAction) {
                                    return 'Are you sure you want to submit this withdrawal?';
                                }

                                /*
                                 * The parent form has not been submitted yet,
                                 * so use getRawData().
                                 */
                                $data = $parentAction->getRawData();

                                $amount = $data['amount'] ?? null;
                                $phone = $data['phone'] ?? null;

                                $formattedAmount = filled($amount)
                                    ? number_format((float) $amount, 2)
                                    : '0.00';

                                return "Are you sure you want to withdraw KES {$formattedAmount} to phone number {$phone}? Funds will be received in this number.";
                            }
                        )

                        ->modalSubmitActionLabel('Yes, Submit Withdrawal')
                        ->modalCancelActionLabel('Go Back')

                        /*
                         * Keep the original withdrawal form mounted underneath
                         * the confirmation modal.
                         */
                        ->overlayParentActions()

                        /*
                         * Once the confirmed child action executes, cancel the
                         * parent action as well.
                         */
                        ->cancelParentActions()

                        ->action(
                            function (array $mountedActions): void {
                                /*
                                 * Get the parent WithdrawAction.
                                 */
                                $parentAction = $mountedActions[0] ?? null;

                                if (! $parentAction) {
                                    Notification::make()
                                        ->title('Withdrawal Failed')
                                        ->body(
                                            'Unable to retrieve the withdrawal form data.'
                                        )
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                /*
                                 * Read the values entered in the parent form.
                                 */
                                $data = $parentAction->getRawData();

                                $phone = $data['phone'] ?? null;
                                $amount = $data['amount'] ?? null;

                                /*
                                 * Because the parent action itself is not being
                                 * submitted, its normal form validation does not
                                 * automatically run.
                                 *
                                 * Validate the required values here before
                                 * processing the withdrawal.
                                 */
                                validator(
                                    [
                                        'phone' => $phone,
                                        'amount' => $amount,
                                    ],
                                    [
                                        'phone' => [
                                            'required',
                                            'regex:/^(07\d{8}|01\d{8}|2547\d{8}|2541\d{8})$/',
                                        ],
                                        'amount' => [
                                            'required',
                                            'numeric',
                                            'min:50',
                                        ],
                                    ],
                                    [
                                        'phone.required' =>
                                            'Please enter a phone number.',
                                        'phone.regex' =>
                                            'Please enter a valid Kenyan phone number.',
                                        'amount.required' =>
                                            'Please enter an amount to withdraw.',
                                        'amount.numeric' =>
                                            'The withdrawal amount must be a number.',
                                        'amount.min' =>
                                            'The minimum withdrawal amount is KES 50.',
                                    ]
                                )->validate();

                                $amount = (float) $amount;

                                $user = auth()->user();
                                $wallet = $user->wallet;

                                if (! $wallet) {
                                    Notification::make()
                                        ->title('Withdrawal Failed')
                                        ->body(
                                            'Your wallet could not be found.'
                                        )
                                        ->danger()
                                        ->persistent()
                                        ->send();

                                    return;
                                }

                                try {
                                    DB::transaction(
                                        function () use (
                                            $amount,
                                            $phone,
                                            $wallet,
                                            $user
                                        ): void {
                                            /*
                                             * Re-query the wallet and lock the
                                             * row to prevent concurrent
                                             * withdrawals from over-drawing it.
                                             */
                                            $lockedWallet = $wallet
                                                ->newQuery()
                                                ->lockForUpdate()
                                                ->find($wallet->id);

                                            if (! $lockedWallet) {
                                                throw new \RuntimeException(
                                                    'Unable to lock the wallet for withdrawal.'
                                                );
                                            }

                                            /*
                                             * Always check the balance again
                                             * after acquiring the database lock.
                                             */
                                            if (
                                                $lockedWallet->available_balance
                                                < $amount
                                            ) {
                                                throw ValidationException::withMessages([
                                                    'amount' =>
                                                        'You do not have sufficient available balance for this withdrawal.',
                                                ]);
                                            }

                                            /*
                                             * Save the phone number to the user
                                             * account if it was previously blank.
                                             */
                                            if (blank($user->phone)) {
                                                $user->phone = $phone;
                                                $user->save();
                                            }

                                            /*
                                             * Reserve the withdrawal funds.
                                             */
                                            $lockedWallet->decrement(
                                                'available_balance',
                                                $amount
                                            );

                                            $lockedWallet->increment(
                                                'pending_balance',
                                                $amount
                                            );

                                            /*
                                             * Create the transaction.
                                             */
                                            $transaction = $lockedWallet
                                                ->transactions()
                                                ->create([
                                                    'transaction_no' =>
                                                        'TRS' . str_pad(
                                                            mt_rand(
                                                                1,
                                                                999999
                                                            ),
                                                            6,
                                                            '0',
                                                            STR_PAD_LEFT
                                                        ),

                                                    'user_id' => $user->id,

                                                    'amount' => $amount,

                                                    'net_amount' => $amount,

                                                    'currency' => 'KES',

                                                    'exchange_rate' => 1,

                                                    'type' =>
                                                        TransactionType::WITHDRAW,

                                                    'status' =>
                                                        TransactionStatus::PENDING_APPROVAL,
                                                ]);

                                            /*
                                             * Create the withdrawal record.
                                             */
                                            Withdraw::create([
                                                'wallet_id' =>
                                                    $lockedWallet->id,

                                                'transaction_id' =>
                                                    $transaction->id,

                                                'phone' => $phone,

                                                'amount' => $amount,

                                                'balance' =>
                                                    $lockedWallet->balance,

                                                'status' =>
                                                    TransactionStatus::PENDING_APPROVAL,
                                            ]);
                                        }
                                    );

                                    /*
                                     * Refresh the wallet after the transaction.
                                     */
                                    $wallet->refresh();

                                    Notification::make()
                                        ->title(
                                            'Withdrawal Request Submitted'
                                        )
                                        ->body(
                                            'Your withdrawal request of KES ' .
                                            number_format($amount, 2) .
                                            ' has been submitted and is pending admin approval.'
                                        )
                                        ->success()
                                        ->send();
                                } catch (ValidationException $e) {
                                    throw $e;
                                } catch (\Throwable $e) {
                                    Log::error(
                                        'Withdraw Action failed',
                                        [
                                            'user_id' => $user->id,
                                            'wallet_id' => $wallet->id,
                                            'amount' => $amount,
                                            'phone' => $phone,
                                            'error' => $e->getMessage(),
                                            'trace' =>
                                                $e->getTraceAsString(),
                                        ]
                                    );

                                    Notification::make()
                                        ->title(
                                            'Withdrawal Request Failed'
                                        )
                                        ->body($e->getMessage())
                                        ->danger()
                                        ->send();

                                    throw $e;
                                }
                            }
                        ),
                ];
            });
    }
}
