<?php

namespace Database\Factories;

use App\Models\Seance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'seance_id'         => Seance::factory(),
            'booking_reference' => 'TH-' . now()->year . '-' . strtoupper(Str::random(6)),
            'quantity'          => fake()->numberBetween(1, 4),
            'total_price'       => fake()->randomFloat(2, 20, 200),
            'status'            => 'pending',
            'payment_status'    => 'pending',
            'expires_at'        => now()->addMinutes(15),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status'         => 'confirmed',
            'payment_status' => 'paid',
            'confirmed_at'   => now(),
            'expires_at'     => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status'     => 'expired',
            'expires_at' => now()->subMinutes(30),
        ]);
    }
}