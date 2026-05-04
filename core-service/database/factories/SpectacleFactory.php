<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Spectacle>
 */
class SpectacleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'           => fake()->sentence(3),
            'description'     => fake()->paragraph(),
            'duration'        => fake()->numberBetween(60, 180),
            'base_price'      => fake()->randomFloat(2, 10, 100),
            'language'        => 'fr',
            'age_restriction' => 0,
            'director'        => fake()->name(),
            'actors'          => [fake()->name(), fake()->name()],
            'is_published'    => true,
            'status'          => 'upcoming',
            'category_id'     => Category::factory(),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }

    public function withStatus(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}