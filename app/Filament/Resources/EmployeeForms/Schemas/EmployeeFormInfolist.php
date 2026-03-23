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
            ->components([
                TextEntry::make('name')
                    ->label('Form Name'),

                TextEntry::make('public_id')
                    ->label('Public URL')
                    ->state(fn ($record) => request()->getSchemeAndHttpHost().'/p/forms/'.$record->public_id)
                    ->url(fn ($record) => request()->getSchemeAndHttpHost().'/p/forms/'.$record->public_id)
                    ->openUrlInNewTab(),

                IconEntry::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ]);
    }
}
