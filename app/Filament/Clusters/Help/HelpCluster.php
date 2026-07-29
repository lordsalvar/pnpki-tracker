<?php

namespace App\Filament\Clusters\Help;

use App\Enums\UserRole;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class HelpCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = 'Help';

    protected static ?int $navigationSort = 100;

    protected static ?string $clusterBreadcrumb = 'Help';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === UserRole::ADMIN->value;
    }

    public static function canAccessClusteredComponents(): bool
    {
        return static::canAccess();
    }
}
