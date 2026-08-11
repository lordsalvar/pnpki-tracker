<?php

use App\Actions\Batch\ApproveBatchSubmissionAction;
use App\Enums\ApplicationStatus;
use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\Sex;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\FormSubmission;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('marks all batch form submissions as approved submission when the batch is approved', function (): void {
    $office = Office::query()->create([
        'name' => 'Davao Del Sur',
        'acronym' => 'DDS',
        'number_of_employees' => 10,
    ]);

    $representative = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $office->id,
    ]);

    $batch = Batch::query()->create([
        'office_id' => $office->id,
        'user_id' => $representative->id,
        'batch_name' => 'DDS-1',
        'status' => BatchStatus::FINALIZED->value,
        'application_status' => ApplicationStatus::FOR_SUBMISSION->value,
    ]);

    $first = FormSubmission::query()->create([
        'firstname' => 'Nova',
        'lastname' => 'Candralo',
        'email' => 'nova@example.com',
        'phone_number' => '09123456789',
        'office_id' => $office->id,
        'batch_id' => $batch->id,
        'organizational_unit' => 'Unit A',
        'civil_status' => 'single',
        'status' => FormSubmissionStatus::FOR_SUBMISSION->value,
        'sex' => Sex::Female->value,
        'tin_number' => '123456789',
        'birth_date' => '1990-01-01',
    ]);

    $second = FormSubmission::query()->create([
        'firstname' => 'Juan',
        'lastname' => 'Dela Cruz',
        'email' => 'juan@example.com',
        'phone_number' => '09123456780',
        'office_id' => $office->id,
        'batch_id' => $batch->id,
        'organizational_unit' => 'Unit A',
        'civil_status' => 'single',
        'status' => FormSubmissionStatus::FOR_SUBMISSION->value,
        'sex' => Sex::Male->value,
        'tin_number' => '987654321',
        'birth_date' => '1991-02-02',
    ]);

    app(ApproveBatchSubmissionAction::class)->execute($batch->fresh());

    expect($batch->fresh()->application_status)->toBe(ApplicationStatus::APPROVED_SUBMISSION)
        ->and($first->fresh()->status)->toBe(FormSubmissionStatus::APPROVED_SUBMISSION)
        ->and($second->fresh()->status)->toBe(FormSubmissionStatus::APPROVED_SUBMISSION);
});

it('rejects approving a batch that is not for submission', function (): void {
    $office = Office::query()->create([
        'name' => 'Davao Del Sur',
        'acronym' => 'DDS',
        'number_of_employees' => 10,
    ]);

    $representative = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $office->id,
    ]);

    $batch = Batch::query()->create([
        'office_id' => $office->id,
        'user_id' => $representative->id,
        'batch_name' => 'DDS-1',
        'status' => BatchStatus::FINALIZED->value,
        'application_status' => ApplicationStatus::PENDING_FOR_REVIEW->value,
    ]);

    expect(fn () => app(ApproveBatchSubmissionAction::class)->execute($batch))
        ->toThrow(InvalidArgumentException::class);
});
