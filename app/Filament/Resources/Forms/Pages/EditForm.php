<?php

namespace App\Filament\Resources\Forms\Pages;

use App\Filament\Resources\Forms\FormResource;
use App\Models\Form;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;

    protected function afterSave(): void
    {
        if ($this->record->is_active) {
            Form::query()
                ->where('office_id', $this->record->office_id)
                ->where('id', '!=', $this->record->id)
                ->update(['is_active' => false]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
