<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Facades\KadiApi;
use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([

                    Section::make('Profile')
                        ->icon(Heroicon::OutlinedUser)
                        ->schema([
                            ImageEntry::make('avatar')
                                ->hiddenLabel()
                                ->state(fn (User $record) => $record->getFirstMediaUrl('avatars', 'medium')
                                    ?: 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&background=random&size=200')
                                ->circular()
                                ->width(80)
                                ->height(80)
                                ->columnSpan(1),
                            Group::make()->schema([
                                TextEntry::make('name')
                                    ->label('Full Name')
                                    ->icon(Heroicon::OutlinedUser)
                                    ->iconColor('primary')
                                    ->weight(FontWeight::Bold)
                                    ->size('lg'),
                                TextEntry::make('account_no')
                                    ->label('Account Number')
                                    ->icon('hugeicons-left-to-right-list-number')
                                    ->iconColor('primary')
                                    ->copyable()
                                    ->copyMessage('Account number copied!')
                                    ->color('primary')
                                    ->weight(FontWeight::SemiBold),
                            ])->columns(2)->columnSpan(3),
                        ])->columns(4)->columnSpanFull(),

                    Section::make('Contact Details')
                        ->icon(Heroicon::OutlinedIdentification)
                        ->schema([
                            TextEntry::make('email')
                                ->label('Email Address')
                                ->icon(Heroicon::OutlinedEnvelope)
                                ->iconColor('primary')
                                ->copyable()
                                ->copyMessage('Email copied!'),
                            TextEntry::make('phone')
                                ->label('Phone Number')
                                ->icon(Heroicon::OutlinedPhone)
                                ->iconColor('primary')
                                ->placeholder('—')
                                ->copyable()
                                ->copyMessage('Phone number copied!'),
                            TextEntry::make('username')
                                ->label('Username')
                                ->icon(Heroicon::OutlinedAtSymbol)
                                ->iconColor('gray')
                                ->placeholder('—'),
                        ])->columns(3)->columnSpanFull(),

                    Section::make('Kadi Play Statistics')
                        ->icon('hugeicons-game-controller-03')
                        ->schema(function (User $record) {
                            if (! $record->isLinked()) {
                                return [
                                    ViewEntry::make('kadi_unlinked')
                                        ->hiddenLabel()
                                        ->view('filament.infolists.entries.kadi-unlinked')
                                        ->columnSpanFull(),
                                ];
                            }

                            try {
                                $stats = KadiApi::getPlayerStats($record->linked_id, today()->toDateString());

                                return [
                                    TextEntry::make('kadi_total')
                                        ->label('Total')
                                        ->icon('hugeicons-chart-02')
                                        ->iconColor('primary')
                                        ->state($stats['total'] ?? 0),
                                    TextEntry::make('kadi_games')
                                        ->label('Games')
                                        ->icon('hugeicons-game-controller-03')
                                        ->iconColor('info')
                                        ->state($stats['games'] ?? 0),
                                    TextEntry::make('kadi_tournament')
                                        ->label('Tournament')
                                        ->icon('heroicon-o-trophy')
                                        ->iconColor('warning')
                                        ->state($stats['tournament'] ?? 0),
                                    TextEntry::make('kadi_jackpots')
                                        ->label('Jackpots')
                                        ->icon('hugeicons-stack-star')
                                        ->iconColor('success')
                                        ->state($stats['jackpots'] ?? 0),
                                ];
                            } catch (RequestException|ConnectionException $e) {
                                return [
                                    ViewEntry::make('kadi_error')
                                        ->hiddenLabel()
                                        ->view('filament.infolists.entries.kadi-error')
                                        ->columnSpanFull(),
                                ];
                            }
                        })->columns(2)->columnSpanFull(),

                ])->columnSpan(['lg' => 2]),

                Group::make()->schema([

                    Section::make('Account')
                        ->icon(Heroicon::OutlinedShieldCheck)
                        ->schema([
                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->columnSpanFull(),
                            IconEntry::make('email_verified_at')
                                ->label('Email Verified')
                                ->state(fn (User $record) => $record->hasVerifiedEmail())
                                ->boolean()
                                ->trueIcon(Heroicon::OutlinedCheckBadge)
                                ->falseIcon(Heroicon::OutlinedXCircle)
                                ->trueColor('success')
                                ->falseColor('danger'),
                            IconEntry::make('two_factor_confirmed_at')
                                ->label('2FA Enabled')
                                ->state(fn (User $record) => ! empty($record->two_factor_confirmed_at))
                                ->boolean()
                                ->trueIcon(Heroicon::OutlinedShieldCheck)
                                ->falseIcon(Heroicon::OutlinedShieldExclamation)
                                ->trueColor('success')
                                ->falseColor('gray'),
                            TextEntry::make('roles')
                                ->label('Roles')
                                ->state(fn (User $record) => $record->roles->pluck('name'))
                                ->badge()
                                ->color('primary')
                                ->separator(',')
                                ->columnSpanFull(),
                            TextEntry::make('bugs_count')
                                ->label('Bugs Submitted')
                                ->icon('hugeicons-bug-02')
                                ->iconColor('warning')
                                ->state(fn (User $record) => $record->bugs()->count())
                                ->suffix(fn (User $record) => $record->bugs()->count() === 1 ? ' bug' : ' bugs')
                                ->columnSpanFull(),
                        ])->columns(2),

                    Section::make('Wallet')
                        ->icon('hugeicons-wallet-add-02')
                        ->schema([
                            TextEntry::make('wallet.wallet_no')
                                ->label('Wallet Number')
                                ->icon('hugeicons-left-to-right-list-number')
                                ->iconColor('primary')
                                ->copyable()
                                ->copyMessage('Wallet number copied!')
                                ->placeholder('No wallet assigned.'),
                            TextEntry::make('wallet.status')
                                ->label('Wallet Status')
                                ->badge()
                                ->placeholder('—'),
                            TextEntry::make('wallet.balance')
                                ->label('Balance')
                                ->icon('hugeicons-money-send-01')
                                ->iconColor('success')
                                ->prefix('Ksh. ')
                                ->numeric(decimalPlaces: 2)
                                ->weight(FontWeight::Bold)
                                ->color('success')
                                ->placeholder('0.00')
                                ->columnSpanFull(),
                        ])->columns(2),

                    Section::make('Timeline')
                        ->icon('hugeicons-time-02')
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('Joined')
                                ->icon('hugeicons-clock-01')
                                ->iconColor('primary')
                                ->dateTime('d M Y, H:i'),
                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->icon('hugeicons-system-update-01')
                                ->iconColor('gray')
                                ->dateTime('d M Y, H:i'),
                        ])->columns(1),

                ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
