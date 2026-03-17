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
        $publicUrl = url('/p/forms/'.$this->record->public_id);

        return [
            Action::make('copy_link')
                ->label('Copy Public Link')
                ->icon('heroicon-o-clipboard-document')
                ->color('gray')
                ->extraAttributes([
                    'data-url' => $publicUrl,
                    'x-on:click.stop' => 'window.copyToClipboard($el.dataset.url)',
                ])
                ->action(function () {
                    Notification::make()
                        ->title('Link copied to clipboard!')
                        ->success()
                        ->send();
                }),

            EditAction::make(),
        ];
    }
}
