<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hall>
 */
class HallFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => fake()->company() . ' Hall',
            'location'    => fake()->address(),
            'capacity'    => fake()->numberBetween(50, 500),
            'description' => fake()->paragraph(),
            'type'        => fake()->randomElement(['Grande salle', 'Petit théâtre', 'Salle polyvalente']),
            'is_active'   => true,
            'image_url'   => null,
            'amenities'   => ['Climatisation', 'Bar'],
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function withCapacity(int $capacity): static
    {
        return $this->state(fn () => ['capacity' => $capacity]);
    }
}