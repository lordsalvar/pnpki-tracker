<?php

namespace App\Filament\Clusters\Settings;

use App\Enums\UserRole;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class SettingsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 90;

    protected static ?string $clusterBreadcrumb = 'Settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === UserRole::ADMIN->value;
    }

    public static function canAccessClusteredComponents(): bool
    {
        return static::canAccess();
    }
}
