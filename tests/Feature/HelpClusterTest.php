<?php

use App\Enums\UserRole;
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

it('allows authenticated staff to view help pages', function (string $pageClass): void {
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

it('shows overview content for representatives', function (): void {
    $user = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
    ]);

    $this->actingAs($user);

    Livewire::test(Overview::class)
        ->assertSuccessful()
        ->assertSee('PNPKI Submission Tracker')
        ->assertSee('How the process works');
});
