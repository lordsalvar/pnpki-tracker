<?php

namespace App\Filament\Resources\Forms\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                    ->required()
                    ->maxLength(255),

                Select::make('office_id')
                    ->label('Office')
                    ->relationship('office', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->acronym . ' — ' . $record->name)
                    ->searchable()
                    ->preload()
                    ->required(),

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