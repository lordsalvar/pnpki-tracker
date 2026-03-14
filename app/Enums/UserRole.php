<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string  implements HasLabel
{
    case CLIENT = 'CLIENT';
    case ADMIN = 'ADMIN';
    case REPRESENTATIVE = 'REPRESENTATIVE';

    public static function labels(): array
    {
        return [
            UserRole::CLIENT => 'Client',
            UserRole::ADMIN => 'Admin',
            UserRole::REPRESENTATIVE => 'Representative',
        ];
    }

    public function getLabel(): string
    {
        return match($this) {
            UserRole::CLIENT => 'Client',
            UserRole::ADMIN => 'Admin',
            UserRole::REPRESENTATIVE => 'Representative',
        };
    }
}
