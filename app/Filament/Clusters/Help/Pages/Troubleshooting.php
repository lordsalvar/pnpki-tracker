<?php

namespace App\Filament\Clusters\Help\Pages;

use App\Filament\Clusters\Help\HelpCluster;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Troubleshooting extends Page
{
    protected string $view = 'filament.clusters.help.pages.troubleshooting';

    protected static ?string $cluster = HelpCluster::class;

    protected static ?string $navigationLabel = 'Troubleshooting';

    protected static ?string $title = 'Troubleshooting';

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;
}
