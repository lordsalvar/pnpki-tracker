<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

<<<<<<< HEAD
      DB::table('users')->insert([
        'name' => 'Admin',
        'email' => 'admin@local.dev',
        'password' => Hash::make('password'),
      ]);
=======
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@local.dev',
            'password' => Hash::make('password'),
        ]);
>>>>>>> 16d7a6256cf510e02a5a4745e0e63526e33ee61a
    }
}
