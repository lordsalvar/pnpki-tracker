<?php

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
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

it('updates form submissions on already approved batches', function (): void {
    $office = Office::query()->create([
        'name' => 'Davao Del Sur',
        'acronym' => 'DDS',
        'number_of_employees' => 10,
    ]);

    $representative = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $office->id,
    ]);

    $approvedBatch = Batch::query()->create([
        'office_id' => $office->id,
        'user_id' => $representative->id,
        'batch_name' => 'DDS-1',
        'status' => BatchStatus::FINALIZED->value,
        'application_status' => ApplicationStatus::APPROVED_SUBMISSION->value,
    ]);

    $forSubmissionBatch = Batch::query()->create([
        'office_id' => $office->id,
        'user_id' => $representative->id,
        'batch_name' => 'DDS-2',
        'status' => BatchStatus::FINALIZED->value,
        'application_status' => ApplicationStatus::FOR_SUBMISSION->value,
    ]);

    $staleOnApproved = FormSubmission::query()->create([
        'firstname' => 'Nova',
        'lastname' => 'Candralo',
        'email' => 'nova@example.com',
        'phone_number' => '09123456789',
        'office_id' => $office->id,
        'batch_id' => $approvedBatch->id,
        'organizational_unit' => 'Unit A',
        'civil_status' => 'single',
        'status' => FormSubmissionStatus::FOR_SUBMISSION->value,
        'sex' => Sex::Female->value,
        'tin_number' => '123456789',
        'birth_date' => '1990-01-01',
    ]);

    $alreadyApproved = FormSubmission::query()->create([
        'firstname' => 'Ana',
        'lastname' => 'Reyes',
        'email' => 'ana@example.com',
        'phone_number' => '09123456780',
        'office_id' => $office->id,
        'batch_id' => $approvedBatch->id,
        'organizational_unit' => 'Unit A',
        'civil_status' => 'single',
        'status' => FormSubmissionStatus::APPROVED_SUBMISSION->value,
        'sex' => Sex::Female->value,
        'tin_number' => '223456789',
        'birth_date' => '1990-02-01',
    ]);

    $onForSubmissionBatch = FormSubmission::query()->create([
        'firstname' => 'Juan',
        'lastname' => 'Dela Cruz',
        'email' => 'juan@example.com',
        'phone_number' => '09123456781',
        'office_id' => $office->id,
        'batch_id' => $forSubmissionBatch->id,
        'organizational_unit' => 'Unit A',
        'civil_status' => 'single',
        'status' => FormSubmissionStatus::FOR_SUBMISSION->value,
        'sex' => Sex::Male->value,
        'tin_number' => '987654321',
        'birth_date' => '1991-02-02',
    ]);

    Artisan::call('batches:sync-approved-submission-forms');

    expect($staleOnApproved->fresh()->status)->toBe(FormSubmissionStatus::APPROVED_SUBMISSION)
        ->and($alreadyApproved->fresh()->status)->toBe(FormSubmissionStatus::APPROVED_SUBMISSION)
        ->and($onForSubmissionBatch->fresh()->status)->toBe(FormSubmissionStatus::FOR_SUBMISSION)
        ->and(Artisan::output())->toContain('Updated 1 form submission(s)');
});
