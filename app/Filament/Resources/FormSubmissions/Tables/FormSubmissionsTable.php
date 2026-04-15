<?php

namespace App\Filament\Resources\FormSubmissions\Tables;

use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Models\FormSubmission;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FormSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferFilters(false)
            ->columns([

                TextColumn::make('fullname')
                    ->label('Full Name')
                    ->description(function ($state, $record): ?string {
                        if ($record->flagged_by === Auth::user()?->role) {
                            $desc = '⚠️ Flagged for revision by '.ucfirst($record->flagged_by);
                            if ($record->flag_remarks) {
                                $preview = str($record->flag_remarks)->limit(100);
                                $desc .= "\n".$preview;
                            }

                            return $desc;
                        }

                        return null;
                    })
                    ->getStateUsing(fn ($record) => trim(
                        $record->firstname.' '.
                        (($record->middlename && $record->middlename !== 'N/A')
                            ? strtoupper(substr($record->middlename, 0, 1)).'. '
                            : '').
                        $record->lastname.
                        (($record->suffix && $record->suffix !== 'N/A')
                            ? ', '.$record->suffix
                            : '')
                    ))
                    ->searchable(query: function ($query, string $search) {
                        $query->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('middlename', 'like', "%{$search}%");
                    }),

                TextColumn::make('office.acronym')
                    ->label('Office')
                    ->searchable()
                    ->tooltip(fn ($record) => $record->office?->name)
                    ->visible(fn () => Auth::user()?->role === 'ADMIN'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('batch.batch_name')
                    ->label('Batch')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->batch?->batch_name ?? 'Unassigned')
                    ->color(fn ($record) => $record->batch_id ? 'success' : 'warning')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($record) => match ($record->status) {
                        FormSubmissionStatus::FINALIZED => 'info',
                        FormSubmissionStatus::NEEDS_REVISION => 'danger',
                        FormSubmissionStatus::FOR_SUBMISSION => 'success',
                        default => 'warning',
                    })
                    ->searchable(),

                TextColumn::make('organization')
                    ->label('Organization')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('organizational_unit')
                    ->label('Org. Unit')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('full_address')
                    ->label('Address')
                    ->getStateUsing(fn ($record) => implode(', ', array_filter([
                        $record->address?->house_no,
                        $record->address?->street,
                        $record->address?->barangay,
                        $record->address?->municipality,
                        $record->address?->province,
                        $record->address?->zip_code,
                    ])))
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('address', fn ($q) => $q
                            ->where('street', 'like', "%{$search}%")
                            ->orWhere('barangay', 'like', "%{$search}%")
                            ->orWhere('municipality', 'like', "%{$search}%")
                            ->orWhere('province', 'like', "%{$search}%")
                        );
                    })
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tin_number')
                    ->label('TIN')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(function (Model $record): ?string {
                if (! $record instanceof FormSubmission) {
                    return null;
                }

                if ($record->status === FormSubmissionStatus::NEEDS_REVISION) {
                    if (
                        Auth::user()?->role === UserRole::REPRESENTATIVE->value
                        && $record->batch_id !== null
                        && $record->batch?->status !== BatchStatus::NEEDS_REVISION
                    ) {
                        return FormSubmissionResource::getUrl('view', ['record' => $record]);
                    }

                    return FormSubmissionResource::getUrl('edit', ['record' => $record]);
                }

                if (in_array($record->status, [FormSubmissionStatus::FINALIZED, FormSubmissionStatus::FOR_SUBMISSION], true)) {
                    return FormSubmissionResource::getUrl('view', ['record' => $record]);
                }

                return FormSubmissionResource::getUrl('edit', ['record' => $record]);
            })
            ->filters([

                TrashedFilter::make(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'finalized' => 'Finalized',
                        'needs_revision' => 'Needs Revision',
                    ]),

                SelectFilter::make('office_id')
                    ->label('Office')
                    ->relationship('office', 'acronym')
                    ->visible(fn () => Auth::user()?->role === 'ADMIN'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn ($record) => FormSubmissionResource::getUrl('edit', ['record' => $record]))
                        ->hidden(function ($record) {
                            $user = Auth::user();

                            if (! $user) {
                                return true;
                            }

                            if (in_array($record->status, [FormSubmissionStatus::FINALIZED, FormSubmissionStatus::FOR_SUBMISSION], true)) {
                                return true;
                            }

                            if ($user->role === UserRole::REPRESENTATIVE->value
                                && $record->status === FormSubmissionStatus::NEEDS_REVISION
                                && $record->batch_id !== null
                                && $record->batch?->status !== BatchStatus::NEEDS_REVISION) {
                                return true;
                            }

                            return false;
                        }),
                    ViewAction::make()
                        ->url(fn ($record) => FormSubmissionResource::getUrl('view', ['record' => $record]))
                        ->visible(fn ($record) => in_array($record->status, [
                            FormSubmissionStatus::FINALIZED,
                            FormSubmissionStatus::FOR_SUBMISSION,
                            FormSubmissionStatus::NEEDS_REVISION,
                        ], true)),
                    RestoreAction::make()
                        ->visible(fn (FormSubmission $record) => $record->trashed()),
                    ForceDeleteAction::make()
                        ->visible(fn (FormSubmission $record) => $record->trashed()),
                ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
