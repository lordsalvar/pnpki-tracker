<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CivilStatus: string implements HasLabel
{
    case Single = 'single';
    case Married = 'married';
    case Widowed = 'widowed';
    case Separated = 'separated';

    public function getLabel(): string
    {
        return match ($this) {
            CivilStatus::Single => 'Single',
            CivilStatus::Married => 'Married',
            CivilStatus::Widowed => 'Widowed',
            CivilStatus::Separated => 'Separated',
        };
    }
}
