<?php

namespace App\Filament\Resources\Severities;

use App\Filament\Resources\Severities\Pages\CreateSeverity;
use App\Filament\Resources\Severities\Pages\EditSeverity;
use App\Filament\Resources\Severities\Pages\ListSeverities;
use App\Filament\Resources\Severities\Schemas\SeverityForm;
use App\Filament\Resources\Severities\Tables\SeveritiesTable;
use App\Models\Severity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class SeverityResource extends Resource
{
    protected static ?string $model = Severity::class;

    protected static string|BackedEnum|null $navigationIcon = 'hugeicons-text-indent-less';

    protected static string|UnitEnum|null $navigationGroup = 'Bug Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SeverityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeveritiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeverities::route('/'),
            'create' => CreateSeverity::route('/create'),
            'edit' => EditSeverity::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
