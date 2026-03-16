<?php

namespace App\Filament\Resources\Forms\Pages;

use App\Filament\Resources\Forms\FormResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewForm extends ViewRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        $baseUrl = url('/p/forms/');

        return [
            Action::make('copy_link')
                ->label('Copy Public Link')
                ->icon('heroicon-o-clipboard-document')
                ->color('gray')
                ->action(function () {
                    Notification::make()
                        ->title('Link copied to clipboard!')
                        ->success()
                        ->send();
                })
                ->extraAttributes([
                    'x-on:click' => 'navigator.clipboard.writeText('.json_encode($baseUrl).' + $wire.record.public_id)',
                ]),

            EditAction::make(),
        ];
    }
}
