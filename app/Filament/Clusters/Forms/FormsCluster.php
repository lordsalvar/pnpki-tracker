<?php

namespace App\Filament\Clusters\Forms;

use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Models\FormSubmission;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class FormsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $navigationLabel = 'Forms';

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        $count = Cache::remember(
            static::navigationBadgeCacheKey(),
            now()->addSeconds(20),
            function () use ($user): int {
                return $user?->role === UserRole::REPRESENTATIVE->value
                    ? FormSubmission::query()
                        ->where('office_id', $user->office_id)
                        ->where('status', FormSubmissionStatus::PENDING->value)
                        ->count()
                    : FormSubmission::query()
                        ->where('status', FormSubmissionStatus::FINALIZED->value)
                        ->count();
            }
        );

        return $count > 0 ? (string) $count : null;
    }

    private static function navigationBadgeCacheKey(): string
    {
        $user = Auth::user();

        if ($user?->role === UserRole::REPRESENTATIVE->value) {
            return 'nav_badge:forms_cluster:rep:office:'.($user->office_id ?? 'none').':pending';
        }

        return 'nav_badge:forms_cluster:admin:finalized';
    }
}
