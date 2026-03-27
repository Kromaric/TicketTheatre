<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'first_name'   => fake()->firstName(),
            'last_name'    => fake()->lastName(),
            'email'        => fake()->unique()->safeEmail(),
            'password'     => static::$password ??= Hash::make('password'),
            'role'         => 'user',
            'phone_number' => fake()->phoneNumber(),
            'sex'          => fake()->randomElement(['M', 'F']),
            'date_of_birth'=> fake()->date('Y-m-d', '-18 years'),
            'avatar'       => null,
            'is_active'    => true,
            'preferences'  => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }
}
