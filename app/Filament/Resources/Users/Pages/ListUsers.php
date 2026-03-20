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
            'active' => Tab::make('Active')->query(fn ($query) => $query->status(UserStatus::Active)),
            'inactive' => Tab::make('Inactive')->query(fn ($query) => $query->status(UserStatus::Inactive)),
            'suspended' => Tab::make('Suspended')->query(fn ($query) => $query->status(UserStatus::Suspended)),
            'banned' => Tab::make('Banned')->query(fn ($query) => $query->status(UserStatus::Banned)),
        ];
    }
}
