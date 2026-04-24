<?php

namespace App\Filament\Resources\ApprovedSubmissions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApprovedSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch_name')
                    ->searchable(),
                // TextColumn::make('office.name')
                //     ->label('Office')
                //     ->searchable(),
                TextColumn::make('user.name')
                    ->label('Representative')
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
                SelectFilter::make('office_id')
                    ->label('Office')
                    ->relationship('office', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
