<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->create([
            'name' => 'admin',
            'email' => 'admin@local.dev',
            'password' => 'password',
            'role' => UserRole::ADMIN->value,
        ]);

        $picto = Office::query()->create([
            'name' => 'Provincial Information and Communications Technology Office',
            'acronym' => 'PGO-PICTO',
        ]);

        $phrmo = Office::query()->create([
            'name' => 'Provincial Human Resource Management Office',
            'acronym' => 'PHRMO',
        ]);

        $pho = Office::query()->create([
            'name' => 'Provincial Health Office',
            'acronym' => 'PHO',
        ]);

        User::query()->create([
            'name' => 'PICTO Representative',
            'email' => 'picto@local.dev',
            'password' => 'password',
            'role' => UserRole::REPRESENTATIVE->value,
            'office_id' => $picto->id,
        ]);

        User::query()->create([
            'name' => 'PHRMO Representative',
            'email' => 'phrmo@local.dev',
            'password' => 'password',
            'role' => UserRole::REPRESENTATIVE->value,
            'office_id' => $phrmo->id,
        ]);

        User::query()->create([
            'name' => 'PHO Representative',
            'email' => 'pho@local.dev',
            'password' => 'password',
            'role' => UserRole::REPRESENTATIVE->value,
            'office_id' => $pho->id,
        ]);
    }
}
