<?php

use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\FormSubmission;
use App\Models\User;
use App\Policies\FormSubmissionPolicy;

beforeEach(function (): void {
    $this->policy = new FormSubmissionPolicy;
});

it('allows admins to assign finalized submissions that already belong to a batch', function (): void {
    $admin = User::make(['role' => UserRole::ADMIN->value]);

    $submission = FormSubmission::make([
        'status' => FormSubmissionStatus::FINALIZED,
        'office_id' => 'office-1',
        'batch_id' => 'batch-1',
    ]);

    expect($this->policy->assignBatch($admin, $submission))->toBeTrue();
});

it('allows admins to unassign submissions from finalized batches', function (): void {
    $admin = User::make(['role' => UserRole::ADMIN->value]);

    $batch = Batch::make([
        'status' => BatchStatus::FINALIZED,
        'office_id' => 'office-1',
    ]);

    $submission = FormSubmission::make([
        'status' => FormSubmissionStatus::FOR_SUBMISSION,
        'office_id' => 'office-1',
        'batch_id' => 'batch-1',
    ]);
    $submission->setRelation('batch', $batch);

    expect($this->policy->unassignBatch($admin, $submission))->toBeTrue();
});

it('prevents representatives from assigning submissions that already belong to a batch', function (): void {
    $representative = User::make([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => 'office-1',
    ]);

    $submission = FormSubmission::make([
        'status' => FormSubmissionStatus::FINALIZED,
        'office_id' => 'office-1',
        'batch_id' => 'batch-1',
    ]);

    expect($this->policy->assignBatch($representative, $submission))->toBeFalse();
});

it('prevents representatives from unassigning submissions in finalized batches', function (): void {
    $representative = User::make([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => 'office-1',
    ]);

    $batch = Batch::make([
        'status' => BatchStatus::FINALIZED,
        'office_id' => 'office-1',
    ]);

    $submission = FormSubmission::make([
        'status' => FormSubmissionStatus::FINALIZED,
        'office_id' => 'office-1',
        'batch_id' => 'batch-1',
    ]);
    $submission->setRelation('batch', $batch);

    expect($this->policy->unassignBatch($representative, $submission))->toBeFalse();
});
