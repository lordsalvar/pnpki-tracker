<?php

use App\Enums\UserRole;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Resources\Offices\OfficeResource;
use App\Filament\Resources\Offices\Pages\ListOffices;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('registers users and offices under the settings cluster', function (): void {
    expect(UserResource::getCluster())->toBe(SettingsCluster::class)
        ->and(OfficeResource::getCluster())->toBe(SettingsCluster::class);
});

it('allows admins to access the settings cluster', function (): void {
    $user = User::factory()->create([
        'role' => UserRole::ADMIN->value,
    ]);

    $this->actingAs($user);

    expect(SettingsCluster::canAccess())->toBeTrue()
        ->and(SettingsCluster::shouldRegisterNavigation())->toBeTrue();

    Livewire::test(ListUsers::class)
        ->assertSuccessful();

    Livewire::test(ListOffices::class)
        ->assertSuccessful();
});

it('forbids representatives from accessing the settings cluster', function (): void {
    $user = User::factory()->create([
        'role' => UserRole::REPRESENTATIVE->value,
    ]);

    $this->actingAs($user);

    expect(SettingsCluster::canAccess())->toBeFalse()
        ->and(SettingsCluster::shouldRegisterNavigation())->toBeFalse();

    Livewire::test(ListUsers::class)
        ->assertForbidden();

    Livewire::test(ListOffices::class)
        ->assertForbidden();
});
