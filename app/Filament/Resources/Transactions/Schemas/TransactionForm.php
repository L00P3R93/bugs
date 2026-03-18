<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\BugStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Bug;
use App\Models\Wallet;
use Closure;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::getFormSchema())->columns(3);
    }

    public static function getFormSchema(?Bug $bug = null): array
    {
        $isInActionContext = $bug !== null;
        $amount = $isInActionContext ? $bug->final_amount : 0.0;
        $wallet = $isInActionContext ? $bug->reporter->wallet : null;

        return [
            Group::make()->schema([
                Section::make('Transaction Details')->schema([
                    TextInput::make('transaction_no')
                        ->label('Transaction Ref')
                        ->prefixIcon(Heroicon::OutlinedHashtag)
                        ->prefixIconColor('primary')
                        ->disabled()
                        ->dehydrated()
                        ->default(fn () => 'TRS'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT))
                        ->required(),
                    TextInput::make('amount')
                        ->label('Amount')
                        ->prefixIcon('hugeicons-money-receive-01')
                        ->prefixIconColor('primary')
                        ->prefix('Ksh.')
                        ->required()
                        ->numeric()
                        ->default($amount),
                    Select::make('type')
                        ->label('Transaction Type')
                        ->prefixIcon('hugeicons-cursor-circle-selection-02')
                        ->prefixIconColor('primary')
                        ->native(false)
                        ->options(TransactionType::class)
                        ->default('payout')
                        ->required(),
                ])->columnSpanFull(),
            ])->columnSpan(['lg' => 2]),
            Group::make()->schema([
                Section::make('Associations')->schema([
                    Select::make('status')
                        ->label('Transaction Status')
                        ->prefixIcon('hugeicons-status')
                        ->prefixIconColor('primary')
                        ->native(false)
                        ->searchable()
                        ->options(TransactionStatus::class)
                        ->default('pending')
                        ->required(),
                    $isInActionContext
                        ? Hidden::make('wallet_id')->default($wallet->id)
                        : Select::make('wallet_id')
                            ->label('User Wallet')
                            ->prefixIcon('hugeicons-wallet-02')
                            ->prefixIconColor('primary')
                            ->native(false)
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search): array {
                                return Wallet::query()
                                    ->with('user')
                                    ->where(function ($query) use ($search): void {
                                        $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                                            ->orWhere('wallet_no', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn (Wallet $wallet): array => [
                                        $wallet->id => "{$wallet->user->name} — {$wallet->wallet_no}",
                                    ])
                                    ->all();
                            })
                            ->getOptionLabelUsing(function (mixed $value): ?string {
                                $wallet = Wallet::with('user')->find($value);

                                return $wallet ? "{$wallet->user->name} — {$wallet->wallet_no}" : null;
                            })
                            ->required(),
                    $isInActionContext
                        ? Hidden::make('bug_id')->default($bug->id)
                        : Select::make('bug_id')
                            ->label('Submitted Bug')
                            ->prefixIcon('hugeicons-file-bitcoin')
                            ->prefixIconColor('primary')
                            ->native(false)
                            ->searchable()
                            ->placeholder('No bug linked (optional)')
                            ->relationship(
                                name: 'bug',
                                titleAttribute: 'title',
                                modifyQueryUsing: fn ($query) => $query
                                    ->whereIn('status', [BugStatus::FIXED, BugStatus::CLOSED])
                                    ->where('is_paid', false)
                                    ->latest()
                                    ->limit(10)
                            )
                            ->getOptionLabelFromRecordUsing(fn (Bug $record): string => "[{$record->bug_no}] {$record->title}")
                            ->rules([
                                fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                    if (blank($value)) {
                                        return;
                                    }

                                    $bug = Bug::find($value);

                                    if (! $bug) {
                                        $fail('The selected bug does not exist.');

                                        return;
                                    }

                                    if (! in_array($bug->status, [BugStatus::FIXED, BugStatus::CLOSED])) {
                                        $fail('The bug must have a status of Fixed or Closed before it can be linked to a transaction.');
                                    }

                                    if ($bug->is_paid) {
                                        $fail('This bug has already been paid out.');
                                    }
                                },
                            ]),
                ])->columnSpanFull(),
            ])->columnSpan(['lg' => 1])->visible(fn () => auth()->user()->isSuperAdmin()),
        ];
    }
}
