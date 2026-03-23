<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;


enum BatchStatus: string implements HasLabel, HasColor
{
    //
    case PENDING = 'pending';
    case FINALIZED = 'finalized';

    public function getLabel(): string
    {
        return match($this) {
            BatchStatus::PENDING => 'Pending',
            BatchStatus::FINALIZED => 'Finalized',
        };
    }

     public function getColor(): string|array|null
    {
        return match ($this) {
            BatchStatus::PENDING => 'warning',
            BatchStatus::FINALIZED => 'success',
        };
    }
}
