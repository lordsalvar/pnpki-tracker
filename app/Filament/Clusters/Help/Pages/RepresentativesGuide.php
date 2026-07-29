<?php

namespace App\Filament\Clusters\Help\Pages;

use App\Enums\UserRole;
use App\Filament\Clusters\Help\HelpCluster;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class RepresentativesGuide extends HelpPage
{
    protected string $view = 'filament.clusters.help.pages.representatives-guide';

    protected static ?string $cluster = HelpCluster::class;

    protected static ?string $navigationLabel = 'Representatives';

    protected static ?string $title = 'Guide for representatives';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, [
            UserRole::ADMIN->value,
            UserRole::REPRESENTATIVE->value,
        ], true);
    }
}
