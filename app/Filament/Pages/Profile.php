<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Profile extends EditProfile
{
    protected static bool $isDiscovered = false;

    public static function isSimple(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make('Profile Photo')
                            ->description('Upload a photo to personalize your account.')
                            ->schema([
                                FileUpload::make('avatar')
                                    ->label(false)
                                    ->image()
                                    ->avatar()
                                    ->maxSize(5120)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                    ->disk('public')
                                    ->directory('users')
                                    ->visibility('public'),
                            ])
                            ->columnSpan(2),

                        Section::make('Account Details')
                            ->description('Update your name, email address, and password.')
                            ->schema([
                                $this->getNameFormComponent(),
                                $this->getEmailFormComponent(),
                                $this->getPasswordFormComponent(),
                                $this->getPasswordConfirmationFormComponent(),
                                $this->getCurrentPasswordFormComponent(),
                            ])
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
