<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->schema([
                    FileUpload::make('avatar')
                    ->label('Avatar')
                    ->alignCenter()
                    ->image()
                    ->avatar()
                    ->maxSize(5120)// 5MB
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                    ->disk('local')
                    ->directory('users')
                    ->visibility('private'),
                    ])
                    ->columnSpanFull(),
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
