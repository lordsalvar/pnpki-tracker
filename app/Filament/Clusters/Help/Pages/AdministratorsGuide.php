<?php

namespace App\Filament\Clusters\Help\Pages;

use App\Filament\Clusters\Help\HelpCluster;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class AdministratorsGuide extends Page
{
    protected string $view = 'filament.clusters.help.pages.administrators-guide';

    protected static ?string $cluster = HelpCluster::class;

    protected static ?string $navigationLabel = 'Administrators';

    protected static ?string $title = 'Guide for administrators';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;
}
