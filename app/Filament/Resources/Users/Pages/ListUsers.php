<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserStatus;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListUsers extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon('hugeicons-plus-sign-circle')->label('Create User')->color('teal'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return UserResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'Super Admin' => Tab::make('Super Admins')->query(fn ($query) => $query->role('Super Admin')),
            'Admin' => Tab::make('Admins')->query(fn ($query) => $query->role('Admin')),
            'Tester' => Tab::make('Testers')->query(fn ($query) => $query->role('Tester')),
            'Player' => Tab::make('Players')->query(fn ($query) => $query->role('Player')),
        ];
    }
}
