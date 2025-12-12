<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            HallSeeder::class,
            SpectacleSeeder::class,
            SeanceSeeder::class,
            ReservationSeeder::class,
        ]);
    }
}
