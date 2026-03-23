<?php

namespace App\Filament\Resources\EmployeeForms\Pages;

use App\Filament\Resources\EmployeeForms\EmployeeFormResource;
use App\Filament\Resources\Offices\RelationManagers\FormSubmissionsRelationManager;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewEmployeeForm extends ViewRecord
{
    protected static string $resource = EmployeeFormResource::class;

    public function getTitle(): string | Htmlable
    {
        return  $this->record->office->name;
    }

    public function getRelationManagers(): array
    {
        return [
            FormSubmissionsRelationManager::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        $publicUrl = request()->getSchemeAndHttpHost().'/p/forms/'.$this->record->public_id;

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
