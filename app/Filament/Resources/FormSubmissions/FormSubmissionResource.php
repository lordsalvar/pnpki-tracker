<?php

namespace App\Filament\Resources\FormSubmissions;

use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Filament\Clusters\Forms\FormsCluster;
use App\Filament\Resources\FormSubmissions\Pages\CreateFormSubmission;
use App\Filament\Resources\FormSubmissions\Pages\EditFormSubmission;
use App\Filament\Resources\FormSubmissions\Pages\ListFormSubmissions;
use App\Filament\Resources\FormSubmissions\Pages\ViewFormSubmission;
use App\Filament\Resources\FormSubmissions\Schemas\FormSubmissionForm;
use App\Filament\Resources\FormSubmissions\Tables\FormSubmissionsTable;
use App\Filament\Resources\FormSubmissions\Widgets\FormSubmissionListStats;
use App\Models\FormSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FormSubmissionResource extends Resource
{
    protected static ?string $cluster = FormsCluster::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $model = FormSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'firstname',
            'lastname',
            'middlename',
            'email',
            'reference_number',
        ];
    }

    /**
     * Scope submissions to the representative's own office.
     * Admins see all submissions.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['office', 'employeeForm']);
        $user = Auth::user();

        if ($user?->role === UserRole::REPRESENTATIVE->value) {
            $query->where('office_id', $user->office_id);
        }

        if ($user?->role === UserRole::ADMIN->value) {
            $query->where('status', FormSubmissionStatus::FINALIZED->value);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return FormSubmissionForm::configure($schema);
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        $count = $user?->role === UserRole::REPRESENTATIVE->value
            ? FormSubmission::where('office_id', $user->office_id)->where('status', FormSubmissionStatus::PENDING->value)->count()
            : FormSubmission::where('status', FormSubmissionStatus::FINALIZED->value)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function table(Table $table): Table
    {
        return FormSubmissionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    /**
     * @return array<class-string<\Filament\Widgets\Widget>>
     */
    public static function getWidgets(): array
    {
        return [
            FormSubmissionListStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFormSubmissions::route('/'),
            'create' => CreateFormSubmission::route('/create'),
            'view' => ViewFormSubmission::route('/{record}'),
            'edit' => EditFormSubmission::route('/{record}/edit'),
        ];
    }
}
