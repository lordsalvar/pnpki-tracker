<?php

namespace App\Filament\Clusters\Help\Pages;

use App\Enums\UserRole;
use App\Filament\Clusters\Help\HelpCluster;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class Overview extends HelpPage
{
    protected string $view = 'filament.clusters.help.pages.overview';

    protected static ?string $cluster = HelpCluster::class;

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $title = 'Help overview';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, [
            UserRole::ADMIN->value,
            UserRole::REPRESENTATIVE->value,
        ], true);
    }
}
