<?php

namespace App\Filament\Resources\Offices;

use App\Enums\UserRole;
use App\Filament\Resources\Offices\Pages\CreateOffice;
use App\Filament\Resources\Offices\Pages\EditOffice;
use App\Filament\Resources\Offices\Pages\ListOffices;
use App\Filament\Resources\Offices\Schemas\OfficeForm;
use App\Filament\Resources\Offices\Tables\OfficesTable;
use App\Filament\Resources\Offices\RelationManagers\EmployeesRelationManager;
use App\Models\Office;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OfficeResource extends Resource
{
    protected static ?string $model = Office::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'acronym';

    /**
     * Superadmin sees all offices.
     * Representatives see only their own office (scoped in getEloquentQuery).
     */
    public static function canViewAny(): bool
    {
        return in_array(Auth::user()?->role, [
            UserRole::ADMIN->value,
            UserRole::REPRESENTATIVE->value,
        ]);
    }

    /**
     * Only superadmin may create offices.
     */
    public static function canCreate(): bool
    {
        return Auth::user()?->role === UserRole::ADMIN->value;
    }

    /**
     * Only superadmin may edit offices.
     */
    public static function canEdit($record): bool
    {
        return Auth::user()?->role === UserRole::ADMIN->value;
    }

    /**
     * Only superadmin may delete offices.
     */
    public static function canDelete($record): bool
    {
        return Auth::user()?->role === UserRole::ADMIN->value;
    }

    /**
     * Scope the office list to the representative's own office only.
     * Superadmin sees all offices unfiltered.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = Auth::user();

        if ($user?->role === UserRole::REPRESENTATIVE->value) {
            $query->where('id', $user->office_id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return OfficeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfficesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            EmployeesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListOffices::route('/'),
            'create' => CreateOffice::route('/create'),
            'edit'   => EditOffice::route('/{record}/edit'),
        ];
    }
}