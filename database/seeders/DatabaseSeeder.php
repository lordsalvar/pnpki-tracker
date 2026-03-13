<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\OfficeSeeder;
use Database\Seeders\RepresentativeSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@local.dev',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN->value,
        ]);

        DB::table('offices')->insert([
            'name' => 'Provincial Information and Communications Technology Office',
            'acronym' => 'PGO-PICTO',
        ]);

        DB::table('offices')->insert([
            'name' => 'Provincial Human Resource Management Office',
            'acronym' => 'PHRMO',
        ]);

        DB::table('offices')->insert([
            'name' => 'Provincial Health Office',
            'acronym' => 'PHO',
        ]);

        DB::table('users')->insert([
            'name'=>'client',
            'email'=>'client@local.dev',
            'password'=>Hash::make('password'),
            'role'=>UserRole::CLIENT->value,
        ]);




        //Representatives - each assigned to different offices
        DB::table('users')->insert([
            'name'=>'PICTO Representative',
            'email'=>'picto@local.dev',
            'password'=>Hash::make('password'),
            'role'=>UserRole::REPRESENTATIVE->value,
            'office_id' => 1,
        ]);

        DB::table('users')->insert([
            'name'=>'PHRMO Representative',
            'email'=>'phrmo@local.dev',
            'password'=>Hash::make('password'),
            'role'=>UserRole::REPRESENTATIVE->value,
            'office_id' => 2,
        ]);

         DB::table('users')->insert([
            'name'=>'PHO Representative',
            'email'=>'pho@local.dev',
            'password'=>Hash::make('password'),
            'role'=>UserRole::REPRESENTATIVE->value,
            'office_id' => 3,
        ]);

    }
}
