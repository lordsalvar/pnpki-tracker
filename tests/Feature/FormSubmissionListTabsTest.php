<?php

use App\Enums\FormSubmissionStatus;
use App\Enums\Sex;
use App\Enums\UserRole;
use App\Filament\Resources\FormSubmissions\Pages\ListFormSubmissions;
use App\Models\FormSubmission;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createSubmissionForOffice(Office $office, FormSubmissionStatus $status): FormSubmission
{
    return FormSubmission::query()->create([
        'firstname' => fake()->firstName(),
        'lastname' => fake()->lastName(),
        'email' => fake()->unique()->safeEmail(),
        'phone_number' => '09'.fake()->numerify('#########'),
        'office_id' => $office->id,
        'organizational_unit' => 'Unit A',
        'civil_status' => 'single',
        'status' => $status->value,
        'sex' => Sex::Male->value,
        'tin_number' => fake()->numerify('#########'),
        'birth_date' => fake()->unique()->date(),
    ]);
}

it('shows status tabs including pending for representatives', function (): void {
    $office = Office::query()->create([
        'name' => 'Davao Del Sur',
        'acronym' => 'DDS',
        'number_of_employees' => 10,
    ]);

    $representative = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $office->id,
    ]);

    createSubmissionForOffice($office, FormSubmissionStatus::PENDING);
    createSubmissionForOffice($office, FormSubmissionStatus::FINALIZED);
    createSubmissionForOffice($office, FormSubmissionStatus::NEEDS_REVISION);
    createSubmissionForOffice($office, FormSubmissionStatus::FOR_SUBMISSION);
    createSubmissionForOffice($office, FormSubmissionStatus::APPROVED_SUBMISSION);

    $this->actingAs($representative);

    $tabs = Livewire::test(ListFormSubmissions::class)
        ->assertSuccessful()
        ->instance()
        ->getTabs();

    expect($tabs)->toHaveKeys([
        'submissions',
        'pending',
        'finalized',
        'needs_revision',
        'for_submission',
        'approved_submission',
    ])
        ->and($tabs['submissions']->getBadge())->toBe(5)
        ->and($tabs['pending']->getBadge())->toBe(1)
        ->and($tabs['finalized']->getBadge())->toBe(1)
        ->and($tabs['needs_revision']->getBadge())->toBe(1)
        ->and($tabs['for_submission']->getBadge())->toBe(1)
        ->and($tabs['approved_submission']->getBadge())->toBe(1);
});

it('shows status tabs without pending for admins', function (): void {
    $office = Office::query()->create([
        'name' => 'Davao Del Norte',
        'acronym' => 'DDN',
        'number_of_employees' => 12,
    ]);

    $admin = User::factory()->create([
        'role' => UserRole::ADMIN->value,
    ]);

    createSubmissionForOffice($office, FormSubmissionStatus::PENDING);
    createSubmissionForOffice($office, FormSubmissionStatus::FINALIZED);
    createSubmissionForOffice($office, FormSubmissionStatus::NEEDS_REVISION);
    createSubmissionForOffice($office, FormSubmissionStatus::FOR_SUBMISSION);
    createSubmissionForOffice($office, FormSubmissionStatus::APPROVED_SUBMISSION);

    $this->actingAs($admin);

    $tabs = Livewire::test(ListFormSubmissions::class)
        ->assertSuccessful()
        ->instance()
        ->getTabs();

    expect($tabs)->toHaveKeys([
        'submissions',
        'finalized',
        'needs_revision',
        'for_submission',
        'approved_submission',
    ])
        ->and($tabs)->not->toHaveKey('pending')
        ->and($tabs['submissions']->getBadge())->toBe(4)
        ->and($tabs['finalized']->getBadge())->toBe(1)
        ->and($tabs['needs_revision']->getBadge())->toBe(1)
        ->and($tabs['for_submission']->getBadge())->toBe(1)
        ->and($tabs['approved_submission']->getBadge())->toBe(1);
});

it('filters the table when a representative selects the pending tab', function (): void {
    $office = Office::query()->create([
        'name' => 'Davao Oriental',
        'acronym' => 'DO',
        'number_of_employees' => 8,
    ]);

    $representative = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $office->id,
    ]);

    $pending = createSubmissionForOffice($office, FormSubmissionStatus::PENDING);
    $finalized = createSubmissionForOffice($office, FormSubmissionStatus::FINALIZED);

    $this->actingAs($representative);

    Livewire::test(ListFormSubmissions::class)
        ->assertSuccessful()
        ->set('activeTab', 'pending')
        ->assertCanSeeTableRecords([$pending])
        ->assertCanNotSeeTableRecords([$finalized]);
});

it('scopes representative tab badges to their office only', function (): void {
    $officeA = Office::query()->create([
        'name' => 'Office A',
        'acronym' => 'OA',
        'number_of_employees' => 5,
    ]);

    $officeB = Office::query()->create([
        'name' => 'Office B',
        'acronym' => 'OB',
        'number_of_employees' => 5,
    ]);

    $representative = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $officeA->id,
    ]);

    createSubmissionForOffice($officeA, FormSubmissionStatus::PENDING);
    createSubmissionForOffice($officeB, FormSubmissionStatus::PENDING);
    createSubmissionForOffice($officeB, FormSubmissionStatus::FINALIZED);

    $this->actingAs($representative);

    $tabs = Livewire::test(ListFormSubmissions::class)
        ->assertSuccessful()
        ->instance()
        ->getTabs();

    expect($tabs['submissions']->getBadge())->toBe(1)
        ->and($tabs['pending']->getBadge())->toBe(1)
        ->and($tabs['finalized']->getBadge())->toBe(0);
});
