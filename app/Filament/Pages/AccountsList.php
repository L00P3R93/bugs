<?php

namespace App\Filament\Pages;

use App\Facades\KadiApi;
use App\Filament\Actions\KadiWalletAction;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Http\Client\RequestException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use UnitEnum;

class AccountsList extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'hugeicons-account-setting-01';

    protected static string|UnitEnum|null $navigationGroup = 'Kadi Games';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.accounts-list';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (int $page, int $recordsPerPage): LengthAwarePaginator {
                $search = $this->getTableSearch();
                $sortColumn = $this->getTableSortColumn() ?? 'name';
                $sortDirection = $this->getTableSortDirection() ?? 'asc';

                $accounts = self::fetchAccounts();

                if ($search) {
                    $term = strtolower($search);
                    $accounts = $accounts->filter(function (array $account) use ($term): bool {
                        return str_contains(strtolower($account['name'] ?? ''), $term)
                            || str_contains(strtolower($account['account_no'] ?? ''), $term)
                            || str_contains(strtolower($account['phone_no'] ?? ''), $term);
                    });
                }

                if ($sortColumn === 'total_played') {
                    $accounts = $accounts->map(fn (array $a): array => array_merge(
                        $a,
                        ['total_played' => ($a['single_played'] ?? 0) + ($a['competition_played'] ?? 0)]
                    ));
                }

                $accounts = match ($sortDirection) {
                    'desc' => $accounts->sortByDesc($sortColumn)->values(),
                    default => $accounts->sortBy($sortColumn)->values(),
                };

                return new LengthAwarePaginator(
                    items: $accounts->forPage($page, $recordsPerPage),
                    total: $accounts->count(),
                    perPage: $recordsPerPage,
                    currentPage: $page,
                );
            })
            ->paginated([10, 25, 50, 100])
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('id')
                    ->label('Kadi Bugs Account')
                    ->formatStateUsing(function ($state) {
                        $user = User::query()->where('linked_id', $state)->first();

                        return $user ? $user->name : 'Unknown';
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('account_no')
                    ->label('Account No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone_no')
                    ->label('Phone No')
                    ->formatStateUsing(function (string $state): string {
                        $cleanNumber = preg_replace('/[^0-9]/', '', $state);
                        if (str_starts_with($cleanNumber, '254')) {
                            return '254'.Str::mask(substr($cleanNumber, 3), '*', 1, 5);
                        } elseif (str_starts_with($cleanNumber, '0')) {
                            return '0'.Str::mask(substr($cleanNumber, 2), '*', 1, 4);
                        }

                        return Str::mask($state, '*', 3, 4);
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_played')
                    ->label('Played')
                    ->numeric()
                    ->alignment('end')
                    ->state(fn ($record) => ($record['single_played'] ?? 0) + ($record['competition_played'] ?? 0))
                    ->summarize([
                        Summarizer::make()
                            ->label('Total')
                            ->using(fn ($query): int => self::fetchAccounts()->sum(fn ($a) => ($a['single_played'] ?? 0) + ($a['competition_played'] ?? 0)))
                            ->numeric(),
                    ]),

                TextColumn::make('deposits')
                    ->numeric()
                    ->alignment('end')
                    ->default(0)
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->summarize([
                        Summarizer::make()
                            ->label('')
                            ->using(fn ($query): int => self::fetchAccounts()->sum('deposits'))
                            ->numeric(),
                    ]),

                TextColumn::make('withdraws')
                    ->label('Withdraws')
                    ->numeric()
                    ->alignment('end')
                    ->default(0)
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->summarize([
                        Summarizer::make()
                            ->label('')
                            ->using(fn ($query): int => self::fetchAccounts()->sum('withdraws'))
                            ->numeric(),
                    ]),

                TextColumn::make('balance')
                    ->label('Wallet Balance')
                    ->numeric()
                    ->alignment('end')
                    ->default(0)
                    ->formatStateUsing(fn ($state) => number_format((int) $state)),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('refresh')
                    ->label('Refresh Data')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->action(function ($livewire) {
                        Cache::forget('kadi_accounts');
                        $livewire->resetTable();
                    }),
            ])
            ->recordActions([
                KadiWalletAction::make('kadi_wallet'),
                ViewAction::make()->iconButton()->icon(Heroicon::OutlinedEye)->color('primary')->tooltip('View User'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No accounts found')
            ->emptyStateDescription('Unable to load accounts from Kadi API.')
            ->emptyStateIcon(Heroicon::OutlinedUserGroup)
            ->striped();
    }

    /**
     * Fetch accounts from Kadi API
     */
    protected static function fetchAccounts(): Collection
    {
        return Cache::remember('kadi_accounts', now()->addMinutes(5), function () {
            try {
                $response = KadiApi::get('customers');

                return collect($response)->map(fn ($item) => array_merge([
                    'deposits' => 0,
                    'withdraws' => 0,
                    'balance' => 0,
                    'wallet_id' => 0,
                    'single_played' => 0,
                    'competition_played' => 0,
                ], $item));

            } catch (RequestException $e) {
                Log::error('Kadi API fetch failed', ['error' => $e->getMessage()]);

                return collect();
            }
        });
    }
}
