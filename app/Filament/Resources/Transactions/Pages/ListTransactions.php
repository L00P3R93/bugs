<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\Actions\WithdrawAction;
use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            WithdrawAction::make('withdraw_action'),
            CreateAction::make()->icon('hugeicons-plus-sign-circle')->label('New Transaction')->color('teal'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return TransactionResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'payouts' => Tab::make('Bug Payouts')->query(fn ($query) => $query->where('type', 'payout')),
            'withdrawals' => Tab::make('Withdrawals')->query(fn ($query) => $query->where('type', 'withdraw')),
        ];
    }
}
