<?php

namespace App\Filament\Resources\Forms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FormsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Form Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('public_id')
                    ->label('Public Link')
                    ->formatStateUsing(fn (string $state): string => url('/p/forms/'.$state))
                    ->url(fn ($record): string => url('/p/forms/'.$record->public_id))
                    ->openUrlInNewTab()
                    ->copyable()
                    ->copyMessage('Link copied!')
                    ->limit(50),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
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
