<?php

namespace App\Enums;

enum UserRole: string
{
    case CLIENT = 'client';
    case ADMIN = 'admin';

    public static function labels(): array
    {
        return [
            self::CLIENT->value => 'Client',
            self::ADMIN->value => 'Admin',
        ];
    }
}