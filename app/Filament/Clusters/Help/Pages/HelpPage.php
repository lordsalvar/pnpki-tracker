<?php

namespace App\Filament\Clusters\Help\Pages;

use App\Enums\UserRole;
use Filament\Pages\Page;

abstract class HelpPage extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->role === UserRole::ADMIN->value;
    }
}
