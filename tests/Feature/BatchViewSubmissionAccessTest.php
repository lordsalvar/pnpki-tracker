<?php

use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\Sex;
use App\Enums\UserRole;
use App\Filament\Resources\Batches\Pages\ViewBatch;
use App\Filament\Resources\Batches\RelationManagers\FormSubmissionsRelationManager;
use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Models\Batch;
use App\Models\FormSubmission;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createBatchSubmission(Office $office, Batch $batch, FormSubmissionStatus $status): FormSubmission
{
    return FormSubmission::query()->create([
        'firstname' => fake()->firstName(),
        'lastname' => fake()->lastName(),
        'email' => fake()->unique()->safeEmail(),
        'phone_number' => '09'.fake()->numerify('#########'),
        'office_id' => $office->id,
        'batch_id' => $batch->id,
        'organizational_unit' => 'Unit A',
        'civil_status' => 'single',
        'status' => $status->value,
        'sex' => Sex::Male->value,
        'tin_number' => fake()->numerify('#########'),
        'birth_date' => fake()->unique()->date(),
    ]);
}

it('lets representatives open pending submissions in edit mode from a reverted batch', function (): void {
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
        'status' => BatchStatus::PENDING->value,
        'application_status' => null,
    ]);

    $submission = createBatchSubmission($office, $batch, FormSubmissionStatus::PENDING);

    $this->actingAs($representative);

    Livewire::test(FormSubmissionsRelationManager::class, [
        'ownerRecord' => $batch,
        'pageClass' => ViewBatch::class,
    ])
        ->assertSuccessful()
        ->loadTable()
        ->assertCanSeeTableRecords([$submission])
        ->assertTableActionVisible('edit', $submission->getKey())
        ->assertTableActionHasUrl('edit', FormSubmissionResource::getUrl('edit', [
            'record' => $submission,
            'batch' => $batch->getKey(),
        ]), $submission->getKey())
        ->assertTableActionHidden('view', $submission->getKey());
});

it('keeps finalized submissions view only from the batch page', function (): void {
    $office = Office::query()->create([
        'name' => 'Davao Del Norte',
        'acronym' => 'DDN',
        'number_of_employees' => 12,
    ]);

    $representative = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $office->id,
    ]);

    $batch = Batch::query()->create([
        'office_id' => $office->id,
        'user_id' => $representative->id,
        'batch_name' => 'DDN-1',
        'status' => BatchStatus::FINALIZED->value,
        'application_status' => null,
    ]);

    $submission = createBatchSubmission($office, $batch, FormSubmissionStatus::FINALIZED);

    $this->actingAs($representative);

    Livewire::test(FormSubmissionsRelationManager::class, [
        'ownerRecord' => $batch,
        'pageClass' => ViewBatch::class,
    ])
        ->assertSuccessful()
        ->loadTable()
        ->assertCanSeeTableRecords([$submission])
        ->assertTableActionVisible('view', $submission->getKey())
        ->assertTableActionHasUrl('view', FormSubmissionResource::getUrl('view', [
            'record' => $submission,
            'batch' => $batch->getKey(),
        ]), $submission->getKey())
        ->assertTableActionHidden('edit', $submission->getKey());
});
