<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BatchStatus: string implements HasColor, HasLabel
{
    //
    case PENDING = 'pending';
    case FINALIZED = 'finalized';
    case NEEDS_REVISION = 'needs_revision';

    public function getLabel(): string
    {
        return match ($this) {
            BatchStatus::PENDING => 'Pending',
            BatchStatus::FINALIZED => 'Finalized',
            BatchStatus::NEEDS_REVISION => 'Needs Revision',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            BatchStatus::PENDING => 'warning',
            BatchStatus::FINALIZED => 'success',
            BatchStatus::NEEDS_REVISION => 'danger',
        };
    }
}
