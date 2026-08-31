<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\TransactionType;
use App\Filament\Resources\Bugs\BugResource;
use App\Models\Transaction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([

                    Section::make('Transaction Details')
                        ->icon('hugeicons-money-receive-square')
                        ->schema([
                            TextEntry::make('transaction_no')
                                ->label('Transaction No')
                                ->icon('hugeicons-left-to-right-list-number')
                                ->iconColor('primary')
                                ->weight(FontWeight::Bold)
                                ->copyable()
                                ->copyMessage('Transaction number copied!')
                                ->color('primary'),
                            TextEntry::make('type')
                                ->label('Type')
                                ->badge()
                                ->columnSpanFull(),
                            TextEntry::make('source')
                                ->label('Source')
                                ->state(function ($record): string {
                                    if ($record->bug_id) {
                                        return 'Bug Bounty';
                                    }

                                    return match ($record->type) {
                                        TransactionType::PAYOUT => 'Games Played',
                                        TransactionType::WITHDRAW => 'Withdrawal',
                                        default => '-',
                                    };
                                })
                                ->badge()
                                ->color(function ($record): string {
                                    if ($record->bug_id) {
                                        return 'success';
                                    }

                                    return match ($record->type) {
                                        TransactionType::PAYOUT => 'info',
                                        TransactionType::WITHDRAW => 'warning',
                                        default => 'gray',
                                    };
                                }),
                            TextEntry::make('bug.title')
                                ->label('Bug')
                                ->icon('hugeicons-bug-02')
                                ->iconColor('success')
                                ->placeholder('—')
                                ->url(function (Transaction $record): ?string {
                                    return $record->bug_id
                                        ? BugResource::getUrl('view', ['record' => $record->bug_id])
                                        : null;
                                })
                                ->openUrlInNewTab(),
                        ])->columns(2)->columnSpanFull(),

                    Section::make('Amount')
                        ->icon('hugeicons-wallet-add-02')
                        ->schema([
                            TextEntry::make('amount')
                                ->label('Amount')
                                ->icon('hugeicons-money-receive-01')
                                ->iconColor('success')
                                ->prefix('KES ')
                                ->numeric(decimalPlaces: 2)
                                ->weight(FontWeight::Bold)
                                ->color('success'),
                            TextEntry::make('fee_amount')
                                ->label('Fee')
                                ->icon('hugeicons-money-send-01')
                                ->iconColor('warning')
                                ->prefix('KES ')
                                ->numeric(decimalPlaces: 2)
                                ->placeholder('0.00'),
                            TextEntry::make('net_amount')
                                ->label('Net Amount')
                                ->icon('hugeicons-checkmark-circle-02')
                                ->iconColor('primary')
                                ->prefix('KES ')
                                ->numeric(decimalPlaces: 2)
                                ->weight(FontWeight::SemiBold),
                        ])->columns(3)->columnSpanFull(),

                ])->columnSpan(['lg' => 2]),

                Group::make()->schema([

                    Section::make('Status')
                        ->icon('heroicon-m-information-circle')
                        ->schema([
                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->columnSpanFull(),
                            TextEntry::make('payout_method')
                                ->label('Payout Method')
                                ->icon('hugeicons-money-exchange-02')
                                ->iconColor('primary')
                                ->placeholder('—'),
                        ])->columns(1),

                    Section::make('Owner')
                        ->icon('hugeicons-user-circle')
                        ->schema([
                            TextEntry::make('user.name')
                                ->label('Name')
                                ->icon('hugeicons-user-account')
                                ->iconColor('primary'),
                            TextEntry::make('user.email')
                                ->label('Email')
                                ->icon('hugeicons-mail-01')
                                ->iconColor('info')
                                ->placeholder('—'),
                            TextEntry::make('wallet.wallet_no')
                                ->label('Wallet No')
                                ->icon('hugeicons-wallet-01')
                                ->iconColor('warning')
                                ->placeholder('—'),
                        ])->columns(1),

                    Section::make('Withdrawal Details')
                        ->icon('hugeicons-reverse-withdrawal-02')
                        ->schema([
                            TextEntry::make('withdraw.phone')
                                ->label('Phone')
                                ->icon('hugeicons-smart-phone-01')
                                ->iconColor('info')
                                ->placeholder('—'),
                            TextEntry::make('withdraw.transaction_ref')
                                ->label('M-Pesa Ref')
                                ->icon('hugeicons-copy-01')
                                ->iconColor('success')
                                ->placeholder('—')
                                ->copyable(),
                            TextEntry::make('withdraw.failure_reason')
                                ->label('Failure Reason')
                                ->icon('hugeicons-cancel-circle')
                                ->iconColor('danger')
                                ->placeholder('—')
                                ->visible(fn (Transaction $record): bool => filled($record->withdraw?->failure_reason)),
                        ])->columns(1)
                        ->visible(fn (Transaction $record): bool => $record->type === TransactionType::WITHDRAW),

                    Section::make('Timeline')
                        ->icon('hugeicons-time-02')
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('Created')
                                ->icon('hugeicons-clock-01')
                                ->iconColor('primary')
                                ->dateTime('d M Y, H:i'),
                            TextEntry::make('completed_at')
                                ->label('Completed')
                                ->icon('hugeicons-checkmark-circle-02')
                                ->iconColor('success')
                                ->dateTime('d M Y, H:i')
                                ->placeholder('—'),
                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->icon('hugeicons-system-update-01')
                                ->iconColor('gray')
                                ->dateTime('d M Y, H:i'),
                            TextEntry::make('deleted_at')
                                ->label('Deleted At')
                                ->icon('hugeicons-delete-02')
                                ->iconColor('danger')
                                ->dateTime('d M Y, H:i')
                                ->visible(fn (Transaction $record): bool => $record->trashed()),
                        ])->columns(1)->collapsed(),

                ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
