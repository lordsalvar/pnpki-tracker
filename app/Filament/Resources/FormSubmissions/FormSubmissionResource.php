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
use Illuminate\Support\Facades\Cache;

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
        $query = parent::getEloquentQuery()->with(['office']);
        $user = Auth::user();

        if ($user?->role === UserRole::REPRESENTATIVE->value) {
            $query->where('office_id', $user->office_id);
        }

        if ($user?->role === UserRole::ADMIN->value) {
            $query->whereIn('status', [
                FormSubmissionStatus::FINALIZED->value,
                FormSubmissionStatus::FOR_SUBMISSION->value,
            ]);
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

        $count = Cache::remember(
            static::navigationBadgeCacheKey(),
            now()->addSeconds(20),
            function () use ($user): int {
                return $user?->role === UserRole::REPRESENTATIVE->value
                    ? FormSubmission::query()
                        ->where('office_id', $user->office_id)
                        ->where('status', FormSubmissionStatus::PENDING->value)
                        ->count()
                    : FormSubmission::query()
                        ->whereIn('status', [
                            FormSubmissionStatus::FINALIZED->value,
                            FormSubmissionStatus::FOR_SUBMISSION->value,
                        ])
                        ->count();
            }
        );

        return $count > 0 ? (string) $count : null;
    }

    private static function navigationBadgeCacheKey(): string
    {
        $user = Auth::user();

        if ($user?->role === UserRole::REPRESENTATIVE->value) {
            return 'nav_badge:forms:rep:office:'.($user->office_id ?? 'none').':pending';
        }

        return 'nav_badge:forms:admin:finalized-for-submission';
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
