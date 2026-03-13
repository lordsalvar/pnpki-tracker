<?php

namespace App\Enums;

enum UserRole: string
{
    case CLIENT = 'client';
    case ADMIN = 'admin';
    case REPRESENTATIVE = 'representative';

    public static function labels(): array
    {
        return [
            self::CLIENT->value => 'client',
            self::ADMIN->value => 'admin',
            self::REPRESENTATIVE->value => 'representative',
        ];
    }
}
