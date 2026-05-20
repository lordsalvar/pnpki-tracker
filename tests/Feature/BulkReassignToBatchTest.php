<?php

use App\Actions\Batch\AssignBatchAction;
use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\FormSubmission;
use App\Models\User;

it('allows admins to reassign submissions to finalized batches', function (): void {
    $admin = User::make(['role' => UserRole::ADMIN->value]);

    $sourceBatch = Batch::make([
        'id' => 'batch-source',
        'office_id' => 'office-1',
        'status' => BatchStatus::FINALIZED,
    ]);

    $targetBatch = Batch::make([
        'id' => 'batch-target',
        'office_id' => 'office-1',
        'status' => BatchStatus::FINALIZED,
    ]);

    $submission = FormSubmission::make([
        'office_id' => 'office-1',
        'batch_id' => $sourceBatch->id,
        'status' => FormSubmissionStatus::FINALIZED,
    ]);

    expect(fn () => app(AssignBatchAction::class)->execute($submission, $targetBatch, $admin))
        ->not->toThrow(InvalidArgumentException::class);
});

it('prevents representatives from assigning submissions to finalized batches', function (): void {
    $representative = User::make(['role' => UserRole::REPRESENTATIVE->value]);

    $batch = Batch::make([
        'office_id' => 'office-1',
        'status' => BatchStatus::FINALIZED,
    ]);

    $submission = FormSubmission::make([
        'office_id' => 'office-1',
        'status' => FormSubmissionStatus::FINALIZED,
    ]);

    app(AssignBatchAction::class)->execute($submission, $batch, $representative);
})->throws(InvalidArgumentException::class);
