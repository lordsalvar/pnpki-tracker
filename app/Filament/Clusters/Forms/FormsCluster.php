<?php

namespace App\Filament\Clusters\Forms;

use App\Models\FormSubmission;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;
use App\Enums\FormSubmissionStatus;

class FormsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $navigationLabel = 'Forms';

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        $count = $user?->role === UserRole::REPRESENTATIVE->value
            ? FormSubmission::where('office_id', $user->office_id)->where('status', FormSubmissionStatus::PENDING->value)->count()
            : FormSubmission::count();

        return $count > 0 ? (string) $count : null;
    }
}
