<?php

namespace App\Filament\Resources\FormSubmissions\Tables;

use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class FormSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fullname')
                    ->label('Full Name')
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
                    ->tooltip(fn ($record) => $record->office?->name),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('phone_number')
                    ->label('Phone')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($record) => match ($record->status) {
                        FormSubmissionStatus::FINALIZED => 'success',
                        FormSubmissionStatus::NEEDS_REVISION => 'danger',
                        default => 'warning',
                    })
                    ->searchable(),

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
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'finalized' => 'Finalized',
                        'needs_revision' => 'Needs Revision',
                    ]),

                \Filament\Tables\Filters\SelectFilter::make('office_id')
                    ->label('Office')
                    ->relationship('office', 'acronym')
                    ->visible(fn () => \Illuminate\Support\Facades\Auth::user()?->role === 'ADMIN'),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn ($record) => FormSubmissionResource::getUrl('edit', ['record' => $record]))
                    ->hidden(function ($record) {
                        $user = Auth::user();

                        if (! $user) {
                            return true;
                        }

                        if ($record->status === FormSubmissionStatus::FINALIZED) {
                            return true;
                        }

                        if ($user->role === UserRole::REPRESENTATIVE->value
                            && $record->status === FormSubmissionStatus::NEEDS_REVISION
                            && $record->batch?->status !== BatchStatus::NEEDS_REVISION) {
                            return true;
                        }

                        return false;
                    }),
                ViewAction::make()
                    ->url(fn ($record) => FormSubmissionResource::getUrl('view', ['record' => $record]))
                    ->visible(fn ($record) => in_array($record->status, [FormSubmissionStatus::FINALIZED, FormSubmissionStatus::NEEDS_REVISION])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
