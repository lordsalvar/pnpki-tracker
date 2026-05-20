<?php

use App\Enums\BatchStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\User;
use App\Policies\BatchPolicy;
use Illuminate\Database\Eloquent\Relations\HasMany;

beforeEach(function (): void {
    $this->policy = new BatchPolicy;
});

it('allows admins to finalize non-finalized batches', function (): void {
    $admin = User::make(['role' => UserRole::ADMIN->value]);

    $batch = Batch::make(['status' => BatchStatus::PENDING]);

    expect($this->policy->finalize($admin, $batch))->toBeTrue();
});

it('allows representatives to finalize batches in their office', function (): void {
    $representative = User::make([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => 'office-1',
    ]);

    $batch = Batch::make([
        'status' => BatchStatus::PENDING,
        'office_id' => 'office-1',
    ]);

    expect($this->policy->finalize($representative, $batch))->toBeTrue();
});

it('prevents finalizing already finalized batches', function (): void {
    $admin = User::make(['role' => UserRole::ADMIN->value]);

    $batch = Batch::make(['status' => BatchStatus::FINALIZED]);

    expect($this->policy->finalize($admin, $batch))->toBeFalse();
});

it('allows admins to delete batches with no submissions', function (): void {
    $admin = User::make(['role' => UserRole::ADMIN->value]);

    $batch = new class extends Batch
    {
        public function formSubmissions(): HasMany
        {
            $relation = Mockery::mock(HasMany::class);
            $relation->shouldReceive('exists')->andReturn(false);

            return $relation;
        }
    };

    expect($this->policy->delete($admin, $batch))->toBeTrue();
});

it('prevents deleting batches that still have submissions', function (): void {
    $admin = User::make(['role' => UserRole::ADMIN->value]);

    $batch = new class extends Batch
    {
        public function formSubmissions(): HasMany
        {
            $relation = Mockery::mock(HasMany::class);
            $relation->shouldReceive('exists')->andReturn(true);

            return $relation;
        }
    };

    expect($this->policy->delete($admin, $batch))->toBeFalse();
});

it('prevents non-admins from deleting batches', function (): void {
    $representative = User::make(['role' => UserRole::REPRESENTATIVE->value]);

    $batch = Batch::make(['status' => BatchStatus::PENDING]);

    expect($this->policy->delete($representative, $batch))->toBeFalse();
});
