<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('period')
                ->label('Time Period')
                ->options([
                    'all_time' => 'All Time',
                    'today' => 'Today',
                    'this_week' => 'This Week',
                    'this_month' => 'This Month',
                ])
                ->default('all_time')
                ->selectablePlaceholder(false),
        ]);
    }

    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }
}
