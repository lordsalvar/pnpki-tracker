<?php

namespace App\Filament\Resources\EmployeeForms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class EmployeeFormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Form Name')
                    ->default(fn () => auth()->user()?->office?->name)
                    ->disabled()
                    ->dehydrated(false),

                Toggle::make('is_active')
                    ->label('Active')
                    ->helperText('Only one form per office can be active at a time. Activating this will deactivate all others.')
                    ->default(true)
                    ->visibleOn('edit'),

                TextEntry::make('public_id')
                    ->label('Public Link')
                    ->state(function ($record): HtmlString {
                        if (! $record?->public_id) {
                            return new HtmlString('<span class="text-gray-400 text-sm">Will be available after saving.</span>');
                        }

                        $url = request()->getSchemeAndHttpHost().'/p/forms/'.$record->public_id;

                        return new HtmlString(
                            '<div class="flex items-center gap-2">'
                            .'<a href="'.e($url).'" target="_blank" class="text-primary-600 underline break-all text-sm">'.e($url).'</a>'
                            .'</div>'
                        );
                    })
                    ->visibleOn('edit'),
            ]);
    }
}
