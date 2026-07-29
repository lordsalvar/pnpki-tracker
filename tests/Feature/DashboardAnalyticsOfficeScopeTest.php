<?php

use App\Enums\BatchStatus;
use App\Enums\CivilStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\Sex;
use App\Enums\UserRole;
use App\Filament\Widgets\AccountWidget;
use App\Filament\Widgets\FormSubmissionsChart;
use App\Filament\Widgets\StatsOverview;
use App\Models\Batch;
use App\Models\EmployeeForm;
use App\Models\FormSubmission;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows a full-width account widget without the Filament info card', function (): void {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN->value,
        'name' => 'Dashboard Tester',
    ]);

    $this->actingAs($admin);

    Livewire::test(AccountWidget::class)
        ->assertSuccessful()
        ->assertSee('Dashboard Tester');

    expect((new AccountWidget)->getColumnSpan())->toBe('full');
});

it('scopes dashboard overview stats to the representative office', function (): void {
    [$ownOffice, $otherOffice] = createOfficesWithAnalyticsFixture();

    $representative = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $ownOffice->id,
    ]);

    $this->actingAs($representative);

    Livewire::test(StatsOverview::class)
        ->assertSuccessful()
        ->assertSee('2')
        ->assertSee('Your office')
        ->assertSee('Headcount for your office')
        ->assertDontSee('Employees (all offices)');
});

it('shows global dashboard overview stats for admins', function (): void {
    createOfficesWithAnalyticsFixture();

    $admin = User::factory()->create([
        'role' => UserRole::ADMIN->value,
    ]);

    $this->actingAs($admin);

    Livewire::test(StatsOverview::class)
        ->assertSuccessful()
        ->assertSee('5')
        ->assertSee('Employees (all offices)')
        ->assertSee('Registered offices')
        ->assertDontSee('Your office')
        ->assertDontSee('Headcount for your office');
});

it('scopes the form submissions chart to the representative office', function (): void {
    [$ownOffice] = createOfficesWithAnalyticsFixture();

    $representative = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $ownOffice->id,
    ]);

    $this->actingAs($representative);

    $chart = Livewire::test(FormSubmissionsChart::class)
        ->assertSuccessful()
        ->assertSee('New registrations per day for your office.');

    $data = invade($chart->instance())->getData();
    $total = array_sum($data['datasets'][0]['data']);

    expect($total)->toBe(2);
});

it('shows all offices on the form submissions chart for admins', function (): void {
    createOfficesWithAnalyticsFixture();

    $admin = User::factory()->create([
        'role' => UserRole::ADMIN->value,
    ]);

    $this->actingAs($admin);

    $chart = Livewire::test(FormSubmissionsChart::class)
        ->assertSuccessful()
        ->assertSee('New registrations per day.');

    $data = invade($chart->instance())->getData();
    $total = array_sum($data['datasets'][0]['data']);

    expect($total)->toBe(5);
});

/**
 * @return array{0: Office, 1: Office}
 */
function createOfficesWithAnalyticsFixture(): array
{
    $ownOffice = Office::query()->create([
        'name' => 'Davao Del Sur',
        'acronym' => 'DDS',
        'number_of_employees' => 15,
    ]);

    $otherOffice = Office::query()->create([
        'name' => 'Davao City',
        'acronym' => 'DVO',
        'number_of_employees' => 40,
    ]);

    $creator = User::factory()->create([
        'role' => UserRole::ADMIN->value,
    ]);

    foreach ([$ownOffice, $otherOffice] as $office) {
        EmployeeForm::query()->create([
            'office_id' => $office->id,
            'user_id' => $creator->id,
            'name' => $office->acronym.' Form',
            'is_active' => true,
        ]);
    }

    Batch::query()->create([
        'office_id' => $ownOffice->id,
        'user_id' => $creator->id,
        'batch_name' => 'Own Batch',
        'status' => BatchStatus::PENDING->value,
    ]);

    Batch::query()->create([
        'office_id' => $otherOffice->id,
        'user_id' => $creator->id,
        'batch_name' => 'Other Batch A',
        'status' => BatchStatus::PENDING->value,
    ]);

    Batch::query()->create([
        'office_id' => $otherOffice->id,
        'user_id' => $creator->id,
        'batch_name' => 'Other Batch B',
        'status' => BatchStatus::PENDING->value,
    ]);

    createFormSubmission($ownOffice, 'Alice');
    createFormSubmission($ownOffice, 'Bob');
    createFormSubmission($otherOffice, 'Carol');
    createFormSubmission($otherOffice, 'Dave');
    createFormSubmission($otherOffice, 'Eve');

    User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $ownOffice->id,
    ]);

    User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
        'office_id' => $otherOffice->id,
    ]);

    return [$ownOffice, $otherOffice];
}

function createFormSubmission(Office $office, string $firstname): FormSubmission
{
    return FormSubmission::query()->create([
        'firstname' => $firstname,
        'lastname' => 'Tester',
        'email' => strtolower($firstname).'@example.com',
        'phone_number' => '09171234567',
        'office_id' => $office->id,
        'organizational_unit' => 'IT',
        'civil_status' => CivilStatus::Single->value,
        'status' => FormSubmissionStatus::PENDING->value,
        'sex' => Sex::Male->value,
        'tin_number' => '123-456-789',
    ]);
}
