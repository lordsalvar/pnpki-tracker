<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FormSubmissionStatus: string implements HasColor, HasLabel
{
    case PENDING = 'pending';
    case FINALIZED = 'finalized';
    case NEEDS_REVISION = 'needs_revision';

    case FLAGGED = 'flagged';

    case FOR_SUBMISSION = 'for_submission';

    public function getLabel(): string
    {
        return match ($this) {
            FormSubmissionStatus::PENDING => 'Pending',
            FormSubmissionStatus::FINALIZED => 'Finalized',
            FormSubmissionStatus::NEEDS_REVISION => 'Needs Revision',
            FormSubmissionStatus::FLAGGED => 'Flagged',
            FormSubmissionStatus::FOR_SUBMISSION => 'For Submission',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            FormSubmissionStatus::PENDING => 'warning',
            FormSubmissionStatus::FINALIZED => 'info',
            FormSubmissionStatus::NEEDS_REVISION => 'danger',
            FormSubmissionStatus::FLAGGED => 'warning',
            FormSubmissionStatus::FOR_SUBMISSION => 'success',
        };
    }
}
