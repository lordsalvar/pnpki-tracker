<?php

namespace App\Filament\Clusters\Help;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class HelpCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = 'Help';

    protected static ?int $navigationSort = 100;

    protected static ?string $clusterBreadcrumb = 'Help';
}
