<?php

use App\Enums\UserRole;
use App\Filament\Clusters\Help\HelpCluster;
use App\Filament\Clusters\Help\Pages\AdministratorsGuide;
use App\Filament\Clusters\Help\Pages\EmployeesGuide;
use App\Filament\Clusters\Help\Pages\Overview;
use App\Filament\Clusters\Help\Pages\RepresentativesGuide;
use App\Filament\Clusters\Help\Pages\StatusesAndRevisions;
use App\Filament\Clusters\Help\Pages\Troubleshooting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('redirects guests away from help pages', function (): void {
    $this->get(Overview::getUrl())
        ->assertRedirect();
});

it('allows admins to view help pages', function (string $pageClass): void {
    $user = User::factory()->create([
        'role' => UserRole::ADMIN->value,
    ]);

    $this->actingAs($user);

    Livewire::test($pageClass)
        ->assertSuccessful();
})->with([
    'overview' => Overview::class,
    'representatives' => RepresentativesGuide::class,
    'administrators' => AdministratorsGuide::class,
    'employees' => EmployeesGuide::class,
    'statuses' => StatusesAndRevisions::class,
    'troubleshooting' => Troubleshooting::class,
]);

it('allows representatives to access the help cluster with overview and representatives pages only', function (): void {
    $user = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
    ]);

    $this->actingAs($user);

    expect(HelpCluster::canAccess())->toBeTrue()
        ->and(HelpCluster::shouldRegisterNavigation())->toBeTrue()
        ->and(Overview::canAccess())->toBeTrue()
        ->and(RepresentativesGuide::canAccess())->toBeTrue()
        ->and(AdministratorsGuide::canAccess())->toBeFalse()
        ->and(EmployeesGuide::canAccess())->toBeFalse()
        ->and(StatusesAndRevisions::canAccess())->toBeFalse()
        ->and(Troubleshooting::canAccess())->toBeFalse();
});

it('allows representatives to view overview and representatives guide', function (string $pageClass): void {
    $user = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
    ]);

    $this->actingAs($user);

    Livewire::test($pageClass)
        ->assertSuccessful();
})->with([
    'overview' => Overview::class,
    'representatives' => RepresentativesGuide::class,
]);

it('forbids representatives from viewing admin-only help pages', function (string $pageClass): void {
    $user = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
    ]);

    $this->actingAs($user);

    Livewire::test($pageClass)
        ->assertForbidden();
})->with([
    'administrators' => AdministratorsGuide::class,
    'employees' => EmployeesGuide::class,
    'statuses' => StatusesAndRevisions::class,
    'troubleshooting' => Troubleshooting::class,
]);
