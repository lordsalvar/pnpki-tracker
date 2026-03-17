<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasLabel
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            Gender::Male => 'Male',
            Gender::Female => 'Female',
            Gender::Other => 'Other',
        };
    }
}
