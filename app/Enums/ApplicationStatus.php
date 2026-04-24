<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ApplicationStatus: string implements HasColor, HasLabel
{
    //
    case PENDING_FOR_REVIEW = 'pending_for_review';
    case MODIFICATION_REQUESTED = 'modification_requested';
    case NEEDS_REVISION = 'needs_revision';
    case FOR_SUBMISSION = 'for_submission';
    case APPROVED_SUBMISSION = 'approved_submission';

    public function getLabel(): string
    {
        return match ($this) {
            ApplicationStatus::PENDING_FOR_REVIEW => 'Pending for Review',
            ApplicationStatus::MODIFICATION_REQUESTED => 'Modification Requested',
            ApplicationStatus::NEEDS_REVISION => 'Needs Revision',
            ApplicationStatus::FOR_SUBMISSION => 'For Submission',
            ApplicationStatus::APPROVED_SUBMISSION => 'Approved Submission',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            ApplicationStatus::PENDING_FOR_REVIEW => 'warning',
            ApplicationStatus::MODIFICATION_REQUESTED => 'info',
            ApplicationStatus::NEEDS_REVISION => 'danger',
            ApplicationStatus::FOR_SUBMISSION => 'success',
            ApplicationStatus::APPROVED_SUBMISSION => 'primary',
        };
    }
}
