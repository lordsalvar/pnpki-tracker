<?php

namespace App\Filament\Resources\Forms\Tables;

use App\Enums\UserRole;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class FormsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $user = Auth::user();

                if ($user->role === UserRole::REPRESENTATIVE->value) {
                    $query->where('office_id', $user->office_id);
                }

                $query->withCount('employees');
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Form Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employees_count')
                    ->label('Submissions')
                    ->counts('employees')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('public_id')
                    ->label('Public Link')
                    ->formatStateUsing(fn (string $state): string => url('/p/forms/'.$state))
                    ->url(fn ($record): string => url('/p/forms/'.$record->public_id))
                    ->openUrlInNewTab()
                    ->copyable()
                    ->copyMessage('Link copied!')
                    ->limit(50),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

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
