<?php

namespace App\Filament\Clusters\Forms;

use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Models\FormSubmission;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class FormsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $navigationLabel = 'Forms';

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        $count = $user?->role === UserRole::REPRESENTATIVE->value
            ? FormSubmission::where('office_id', $user->office_id)->where('status', FormSubmissionStatus::PENDING->value)->count()
            : FormSubmission::where('status', FormSubmissionStatus::FINALIZED->value)->count();

        return $count > 0 ? (string) $count : null;
    }
}
