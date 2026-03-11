<?php

namespace App\Filament\Resources\Offices\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OfficeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->placeholder('Enter Office Name')
                    ->extraInputAttributes(['oninput' => 'this.value = this.value.toUpperCase()']),
                    
                TextInput::make('acronym')
                    ->required()
                    ->placeholder('Enter Acronym')
                    ->extraInputAttributes(['oninput' => 'this.value = this.value.toUpperCase()']),
            ]);
    }
}
