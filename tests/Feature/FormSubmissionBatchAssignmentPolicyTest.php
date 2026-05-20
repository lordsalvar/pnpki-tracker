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

it('prevents representatives from deleting submissions', function (): void {
    $representative = User::make([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => 'office-1',
    ]);

    $submission = FormSubmission::make([
        'office_id' => 'office-1',
    ]);

    expect($this->policy->delete($representative, $submission))->toBeFalse();
    expect($this->policy->restore($representative, $submission))->toBeFalse();
    expect($this->policy->forceDelete($representative, $submission))->toBeFalse();
});

it('allows admins to delete submissions', function (): void {
    $admin = User::make(['role' => UserRole::ADMIN->value]);

    $submission = FormSubmission::make([
        'office_id' => 'office-1',
    ]);

    expect($this->policy->delete($admin, $submission))->toBeTrue();
    expect($this->policy->restore($admin, $submission))->toBeTrue();
    expect($this->policy->forceDelete($admin, $submission))->toBeTrue();
});
