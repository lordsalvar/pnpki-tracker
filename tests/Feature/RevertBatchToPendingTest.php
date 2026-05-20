<?php

use App\Actions\Batch\RevertBatchToPendingAction;
use App\Enums\ApplicationStatus;
use App\Enums\BatchStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\User;
use App\Policies\BatchPolicy;

beforeEach(function (): void {
    $this->policy = new BatchPolicy;
    $this->action = new RevertBatchToPendingAction;
});

it('allows admins to revert finalized batches to pending', function (): void {
    $admin = User::make(['role' => UserRole::ADMIN->value]);

    $batch = Batch::make([
        'status' => BatchStatus::FINALIZED,
        'application_status' => ApplicationStatus::PENDING_FOR_REVIEW,
    ]);

    expect($this->policy->revertToPending($admin, $batch))->toBeTrue();
});

it('prevents non-admins from reverting batches to pending', function (): void {
    $representative = User::make(['role' => UserRole::REPRESENTATIVE->value]);

    $batch = Batch::make(['status' => BatchStatus::FINALIZED]);

    expect($this->policy->revertToPending($representative, $batch))->toBeFalse();
});

it('throws when reverting a batch that is not finalized', function (): void {
    $batch = Batch::make(['status' => BatchStatus::PENDING]);

    $this->action->execute($batch);
})->throws(InvalidArgumentException::class);
