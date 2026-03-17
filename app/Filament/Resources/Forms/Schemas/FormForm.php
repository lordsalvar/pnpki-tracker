<?php

namespace App\Filament\Resources\Forms\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class FormForm
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

                Placeholder::make('public_url')
                    ->label('Public Link')
                    ->content(function ($record): HtmlString {
                        if (! $record?->public_id) {
                            return new HtmlString('<span class="text-gray-400 text-sm">Will be available after saving.</span>');
                        }

                        $url = url('/p/forms/'.$record->public_id);

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
