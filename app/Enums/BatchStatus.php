<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;


enum BatchStatus: string implements HasLabel
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
}
