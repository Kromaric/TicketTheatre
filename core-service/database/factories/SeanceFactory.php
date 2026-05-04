<?php

namespace Database\Factories;

use App\Models\Hall;
use App\Models\Spectacle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Seance>
 */
class SeanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'spectacle_id'    => Spectacle::factory(),
            'hall_id'         => Hall::factory(),
            'date_seance'     => fake()->dateTimeBetween('+1 day', '+3 months'),
            'available_seats' => 100,
            'price'           => fake()->randomFloat(2, 10, 80),
            'status'          => 'scheduled',
        ];
    }

    public function past(): static
    {
        return $this->state(fn () => [
            'date_seance' => fake()->dateTimeBetween('-3 months', '-1 day'),
            'status'      => 'completed',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => 'cancelled']);
    }
}
