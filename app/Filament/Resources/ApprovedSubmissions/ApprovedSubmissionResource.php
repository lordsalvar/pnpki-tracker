<?php

namespace App\Filament\Resources\ApprovedSubmissions;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Filament\Resources\ApprovedSubmissions\Pages\ListApprovedSubmissions;
use App\Filament\Resources\ApprovedSubmissions\Pages\ViewApprovedSubmission;
use App\Filament\Resources\ApprovedSubmissions\Tables\ApprovedSubmissionsTable;
use App\Filament\Resources\Batches\RelationManagers\FormSubmissionsRelationManager;
use App\Filament\Resources\Batches\Schemas\BatchInfolist;
use App\Models\Batch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ApprovedSubmissionResource extends Resource
{
    protected static ?string $model = Batch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static ?string $navigationLabel = 'Approved Batches';

    protected static ?string $slug = 'approved-submissions';

    protected static ?string $recordTitleAttribute = 'batch_name';

    public static function infolist(Schema $schema): Schema
    {
        return BatchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApprovedSubmissionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            FormSubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApprovedSubmissions::route('/'),
            'view' => ViewApprovedSubmission::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('application_status', ApplicationStatus::APPROVED_SUBMISSION->value);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->role === UserRole::ADMIN->value;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === UserRole::ADMIN->value;
    }
}
