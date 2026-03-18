<?php

namespace App\Filament\Resources\EmployeeForms;

use App\Filament\Resources\EmployeeForms\Pages\CreateEmployeeForm;
use App\Filament\Resources\EmployeeForms\Pages\EditEmployeeForm;
use App\Filament\Resources\EmployeeForms\Pages\ListEmployeeForms;
use App\Filament\Resources\EmployeeForms\Pages\ViewEmployeeForm;
use App\Filament\Resources\EmployeeForms\Schemas\EmployeeFormForm;
use App\Filament\Resources\EmployeeForms\Tables\EmployeeFormsTable;
use App\Models\EmployeeForm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmployeeFormResource extends Resource
{
    protected static ?string $model = EmployeeForm::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static ?string $navigationLabel = 'Shareable Forms';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EmployeeFormForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeFormsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeForms::route('/'),
            'create' => CreateEmployeeForm::route('/create'),
            'view' => ViewEmployeeForm::route('/{record}'),
            'edit' => EditEmployeeForm::route('/{record}/edit'),
        ];
    }
}
