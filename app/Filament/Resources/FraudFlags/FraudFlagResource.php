<?php

namespace App\Filament\Resources\FraudFlags;

use App\Filament\Resources\FraudFlags\Pages\CreateFraudFlag;
use App\Filament\Resources\FraudFlags\Pages\EditFraudFlag;
use App\Filament\Resources\FraudFlags\Pages\ListFraudFlags;
use App\Filament\Resources\FraudFlags\Schemas\FraudFlagForm;
use App\Filament\Resources\FraudFlags\Tables\FraudFlagsTable;
use App\Models\FraudFlag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class FraudFlagResource extends Resource
{
    protected static ?string $model = FraudFlag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static string|UnitEnum|null $navigationGroup = 'Security';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'flag_type';

    public static function form(Schema $schema): Schema
    {
        return FraudFlagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FraudFlagsTable::configure($table);
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
            'index' => ListFraudFlags::route('/'),
            'create' => CreateFraudFlag::route('/create'),
            'edit' => EditFraudFlag::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->isAdmin();
    }
}
