<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FormSubmissionStatus: string implements HasColor, HasLabel
{
    case PENDING = 'pending';
    case FINALIZED = 'finalized';

    public function getLabel(): string
    {
        return match ($this) {
            FormSubmissionStatus::PENDING => 'Pending',
            FormSubmissionStatus::FINALIZED => 'Finalized',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            FormSubmissionStatus::PENDING => 'warning',
            FormSubmissionStatus::FINALIZED => 'success',
        };
    }
}
