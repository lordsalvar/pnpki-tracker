<?php

namespace App\Filament\Clusters\Help\Pages;

use App\Filament\Clusters\Help\HelpCluster;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class StatusesAndRevisions extends HelpPage
{
    protected string $view = 'filament.clusters.help.pages.statuses-and-revisions';

    protected static ?string $cluster = HelpCluster::class;

    protected static ?string $navigationLabel = 'Statuses & revisions';

    protected static ?string $title = 'Statuses and revisions';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;
}
