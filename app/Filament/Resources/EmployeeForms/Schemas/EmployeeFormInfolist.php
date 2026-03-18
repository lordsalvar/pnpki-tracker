<?php

namespace App\Filament\Resources\EmployeeForms\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EmployeeFormInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextEntry::make('name')
                    ->label('Form Name'),

                IconEntry::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                TextEntry::make('form_submissions_count')
                    ->label('Submissions')
                    ->state(fn ($record) => $record->formSubmissions()->count()),
            ]);
    }
}
