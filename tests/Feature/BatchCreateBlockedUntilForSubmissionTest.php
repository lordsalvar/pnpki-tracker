<?php

use App\Enums\ApplicationStatus;
use App\Enums\BatchStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Batches\Pages\ListBatches;
use App\Models\Batch;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('allows creating a batch when the office has no batches', function (): void {
    $office = Office::query()->create([
        'name' => 'Davao Del Sur',
        'acronym' => 'DDS',
        'number_of_employees' => 10,
    ]);

    $representative = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $office->id,
    ]);

    $this->actingAs($representative);

    $action = Livewire::test(ListBatches::class)
        ->assertSuccessful()
        ->assertActionExists('create')
        ->instance()
        ->getAction('create');

    expect($action->isDisabled())->toBeFalse()
        ->and($action->getTooltip())->toBeNull()
        ->and($action->canCreateAnother())->toBeFalse();
});

it('disables creating a batch when an earlier batch has not reached for submission', function (): void {
    $office = Office::query()->create([
        'name' => 'Davao Del Sur',
        'acronym' => 'DDS',
        'number_of_employees' => 10,
    ]);

    $representative = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $office->id,
    ]);

    Batch::query()->create([
        'office_id' => $office->id,
        'user_id' => $representative->id,
        'batch_name' => 'DDS-1',
        'status' => BatchStatus::PENDING->value,
        'application_status' => null,
    ]);

    $this->actingAs($representative);

    $action = Livewire::test(ListBatches::class)
        ->assertSuccessful()
        ->assertActionExists('create')
        ->instance()
        ->getAction('create');

    expect($action->isDisabled())->toBeTrue()
        ->and($action->getTooltip())->toBe(
            'A new batch cannot be created until the previous batch reaches For Submission.'
        );
});

it('allows creating a batch when all office batches have reached for submission', function (): void {
    $office = Office::query()->create([
        'name' => 'Davao Del Sur',
        'acronym' => 'DDS',
        'number_of_employees' => 10,
    ]);

    $representative = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $office->id,
    ]);

    Batch::query()->create([
        'office_id' => $office->id,
        'user_id' => $representative->id,
        'batch_name' => 'DDS-1',
        'status' => BatchStatus::FINALIZED->value,
        'application_status' => ApplicationStatus::FOR_SUBMISSION->value,
    ]);

    $this->actingAs($representative);

    $action = Livewire::test(ListBatches::class)
        ->assertSuccessful()
        ->assertActionExists('create')
        ->instance()
        ->getAction('create');

    expect($action->isDisabled())->toBeFalse()
        ->and($action->getTooltip())->toBeNull();
});

it('allows creating a batch when office batches are approved submissions', function (): void {
    $office = Office::query()->create([
        'name' => 'Davao Del Sur',
        'acronym' => 'DDS',
        'number_of_employees' => 10,
    ]);

    $representative = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $office->id,
    ]);

    Batch::query()->create([
        'office_id' => $office->id,
        'user_id' => $representative->id,
        'batch_name' => 'DDS-1',
        'status' => BatchStatus::FINALIZED->value,
        'application_status' => ApplicationStatus::APPROVED_SUBMISSION->value,
    ]);

    $this->actingAs($representative);

    $action = Livewire::test(ListBatches::class)
        ->assertSuccessful()
        ->assertActionExists('create')
        ->instance()
        ->getAction('create');

    expect($action->isDisabled())->toBeFalse();
});

it('ignores incomplete batches from other offices when deciding create availability', function (): void {
    $ownOffice = Office::query()->create([
        'name' => 'Davao Del Sur',
        'acronym' => 'DDS',
        'number_of_employees' => 10,
    ]);

    $otherOffice = Office::query()->create([
        'name' => 'Davao City',
        'acronym' => 'DVO',
        'number_of_employees' => 20,
    ]);

    $representative = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $ownOffice->id,
    ]);

    Batch::query()->create([
        'office_id' => $ownOffice->id,
        'user_id' => $representative->id,
        'batch_name' => 'DDS-1',
        'status' => BatchStatus::FINALIZED->value,
        'application_status' => ApplicationStatus::FOR_SUBMISSION->value,
    ]);

    Batch::query()->create([
        'office_id' => $otherOffice->id,
        'user_id' => $representative->id,
        'batch_name' => 'DVO-1',
        'status' => BatchStatus::PENDING->value,
        'application_status' => null,
    ]);

    $this->actingAs($representative);

    $action = Livewire::test(ListBatches::class)
        ->assertSuccessful()
        ->assertActionExists('create')
        ->instance()
        ->getAction('create');

    expect($action->isDisabled())->toBeFalse();
});
