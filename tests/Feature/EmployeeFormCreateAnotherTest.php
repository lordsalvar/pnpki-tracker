<?php

use App\Enums\UserRole;
use App\Filament\Resources\EmployeeForms\Pages\ListEmployeeForms;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('disables create and create another when creating a shareable form', function (): void {
    $user = User::factory()->create([
        'role' => UserRole::ADMIN->value,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(ListEmployeeForms::class)
        ->assertSuccessful()
        ->assertActionExists('create');

    $action = $component->instance()->getAction('create');

    expect($action->canCreateAnother())->toBeFalse();
});
