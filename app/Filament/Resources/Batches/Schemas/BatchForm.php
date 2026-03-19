<?php

namespace App\Filament\Resources\Batches\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use App\Enums\BatchStatus;

class BatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('office_id')
                    ->relationship('office', 'name')
                    ->default(fn() => Auth::user()->office_id)
                    ->disabled()
                    ->dehydrated()  // ensures value is still submitted even when disabled
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->default(fn() => Auth::user()->id)
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                TextInput::make('batch_name')
                    ->required(),
                Select::make('status')
                    ->options(BatchStatus::class)
                    ->default(BatchStatus::PENDING->value)
                    ->disabled()
                    ->dehydrated()
                    ->required(),
            ]);
    }
}
