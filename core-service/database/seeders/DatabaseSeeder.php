<?php

namespace Database\Seeders;

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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin Profile',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin', // Rôle pour le test
            'phone_number' => '0612345678',
            'sex' => 'M',
            'date_of_birth' => '1990-01-01',
        ]);
    }
}
