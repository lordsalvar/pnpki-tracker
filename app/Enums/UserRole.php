<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string  implements HasLabel
{
    case ADMIN = 'ADMIN';
    case REPRESENTATIVE = 'REPRESENTATIVE';

    public static function labels(): array
    {
        return [
            UserRole::ADMIN => 'Admin',
            UserRole::REPRESENTATIVE => 'Representative',
        ];
    }

    public function getLabel(): string
    {
        return match($this) {
            UserRole::ADMIN => 'Admin',
            UserRole::REPRESENTATIVE => 'Representative',
        };
    }
}
