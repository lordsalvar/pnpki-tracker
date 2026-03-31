<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                            ->maxSize(5120) // 5MB
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->disk('public')
                            ->directory('users')
                            ->visibility('public'),
                    ])
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->required()
                    ->unique(
                        table: 'users',
                        column: 'name',
                        ignoreRecord: true,
                    )
                    ->validationMessages([
                        'unique' => 'This name is already taken.',
                    ]),

                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->unique(
                        table: 'users',
                        column: 'email',
                        ignoreRecord: true,
                    )
                    ->validationMessages([
                        'unique' => 'This email address is already taken.',
                    ]),
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
