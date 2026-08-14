<?php

use App\Actions\FormSubmission\RevertForSubmissionToPendingAction;
use App\Enums\ApplicationStatus;
use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\Sex;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\FormSubmission;
use App\Models\Office;
use App\Models\User;
use App\Policies\FormSubmissionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->policy = new FormSubmissionPolicy;
    $this->action = new RevertForSubmissionToPendingAction;
});

function createOfficeForRevertTest(string $name, string $acronym): Office
{
    return Office::query()->create([
        'name' => $name,
        'acronym' => $acronym,
        'number_of_employees' => 10,
    ]);
}

function createSubmissionForRevertTest(
    Office $office,
    FormSubmissionStatus $status,
    ?Batch $batch = null,
): FormSubmission {
    return FormSubmission::query()->create([
        'firstname' => fake()->firstName(),
        'lastname' => fake()->lastName(),
        'email' => fake()->unique()->safeEmail(),
        'phone_number' => '09'.fake()->numerify('#########'),
        'office_id' => $office->id,
        'batch_id' => $batch?->id,
        'organizational_unit' => 'Unit A',
        'civil_status' => 'single',
        'status' => $status->value,
        'sex' => Sex::Male->value,
        'tin_number' => fake()->numerify('#########'),
        'birth_date' => fake()->unique()->date(),
    ]);
}

it('allows admins to revert for-submission records to pending', function (): void {
    $admin = User::make(['role' => UserRole::ADMIN->value]);
    $submission = FormSubmission::make([
        'status' => FormSubmissionStatus::FOR_SUBMISSION,
    ]);

    expect($this->policy->revertForSubmissionToPending($admin, $submission))->toBeTrue();
});

it('prevents non-admins from reverting for-submission records to pending', function (): void {
    $representative = User::make(['role' => UserRole::REPRESENTATIVE->value]);
    $submission = FormSubmission::make([
        'status' => FormSubmissionStatus::FOR_SUBMISSION,
    ]);

    expect($this->policy->revertForSubmissionToPending($representative, $submission))->toBeFalse();
});

it('prevents reverting when the parent batch is already approved', function (): void {
    $admin = User::make(['role' => UserRole::ADMIN->value]);
    $batch = Batch::make([
        'status' => BatchStatus::FINALIZED,
        'application_status' => ApplicationStatus::APPROVED_SUBMISSION,
    ]);
    $submission = FormSubmission::make([
        'status' => FormSubmissionStatus::FOR_SUBMISSION,
    ]);
    $submission->setRelation('batch', $batch);

    expect($this->policy->revertForSubmissionToPending($admin, $submission))->toBeFalse();
});

it('reverts a for-submission record to pending and unassigns it from the batch', function (): void {
    $office = createOfficeForRevertTest('Davao City', 'DVO');
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN->value,
        'office_id' => $office->id,
    ]);

    $batch = Batch::query()->create([
        'office_id' => $office->id,
        'user_id' => $admin->id,
        'batch_name' => 'DVO-1',
        'status' => BatchStatus::FINALIZED->value,
        'application_status' => ApplicationStatus::PENDING_FOR_REVIEW->value,
    ]);

    $submission = createSubmissionForRevertTest(
        $office,
        FormSubmissionStatus::FOR_SUBMISSION,
        $batch,
    );

    $this->actingAs($admin);

    $this->action->execute($submission);

    $submission->refresh();

    expect($submission->status)->toBe(FormSubmissionStatus::PENDING)
        ->and($submission->batch_id)->toBeNull();
});

it('resets batch application status when reverting the last for-submission mark', function (): void {
    $office = createOfficeForRevertTest('Tagum City', 'TGM');
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN->value,
        'office_id' => $office->id,
    ]);

    $batch = Batch::query()->create([
        'office_id' => $office->id,
        'user_id' => $admin->id,
        'batch_name' => 'TGM-1',
        'status' => BatchStatus::FINALIZED->value,
        'application_status' => ApplicationStatus::FOR_SUBMISSION->value,
    ]);

    $submission = createSubmissionForRevertTest(
        $office,
        FormSubmissionStatus::FOR_SUBMISSION,
        $batch,
    );

    $this->actingAs($admin);

    $this->action->execute($submission);

    expect($batch->fresh()->application_status)->toBe(ApplicationStatus::PENDING_FOR_REVIEW)
        ->and($submission->fresh()->status)->toBe(FormSubmissionStatus::PENDING)
        ->and($submission->fresh()->batch_id)->toBeNull();
});

it('throws when reverting a submission that is not for submission', function (): void {
    $office = createOfficeForRevertTest('Digos City', 'DGS');
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN->value,
        'office_id' => $office->id,
    ]);

    $submission = createSubmissionForRevertTest($office, FormSubmissionStatus::FINALIZED);

    $this->actingAs($admin);

    Gate::before(fn (): bool => true);

    $this->action->execute($submission);
})->throws(InvalidArgumentException::class);
