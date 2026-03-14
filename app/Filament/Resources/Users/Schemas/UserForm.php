<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Enums\UserRole;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),

                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required(),
                    
                Select::make('role')
                    ->label('User Role')
                    ->options(UserRole::class)    
                    ->required(),

                TextInput::make('password')
                    ->password()
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => bcrypt($state)),
                Select::make('office_id')
                    ->label('Office')
                    ->relationship('office', 'name')
                    ->preload()
                    ->searchable(),

                
            ]);
    }
}