<?php

namespace App\Filament\Resources\Batches\Tables;

use App\Enums\ApplicationStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch_name')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Representative')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('application_status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('office_id')

                    ->label('Office')
                    ->relationship('office', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => \Illuminate\Support\Facades\Auth::user()->role === \App\Enums\UserRole::ADMIN->value),

                \Filament\Tables\Filters\SelectFilter::make('application_status')
                    ->label('Application Status')
                    ->options(ApplicationStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
