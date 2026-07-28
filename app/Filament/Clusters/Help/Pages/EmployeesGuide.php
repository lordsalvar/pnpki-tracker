<?php

namespace App\Filament\Clusters\Help\Pages;

use App\Filament\Clusters\Help\HelpCluster;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class EmployeesGuide extends Page
{
    protected string $view = 'filament.clusters.help.pages.employees-guide';

    protected static ?string $cluster = HelpCluster::class;

    protected static ?string $navigationLabel = 'Employees';

    protected static ?string $title = 'Guide for employees';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
}
