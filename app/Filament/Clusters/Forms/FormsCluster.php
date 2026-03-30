<?php

namespace App\Filament\Clusters\Forms;

use App\Models\FormSubmission;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class FormsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $navigationLabel = 'Forms';

    public static function getNavigationBadge(): ?string
    {

        if (FormSubmission::count() > 0) {
            return FormSubmission::count();
        }

        return null;
    }
}
