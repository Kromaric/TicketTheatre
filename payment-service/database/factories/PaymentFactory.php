<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stripe_payment_intent_id' => 'pi_' . fake()->unique()->uuid(),
            'user_id' => fake()->numberBetween(1, 1000),
            'order_id' => fake()->optional()->numberBetween(1, 1000),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'currency' => fake()->randomElement(['eur', 'usd', 'gbp']),
            'status' => fake()->randomElement(['pending', 'succeeded', 'failed']),
            'payment_method' => fake()->optional()->creditCardType(),
            'customer_email' => fake()->email(),
            'description' => fake()->optional()->sentence(),
            'metadata' => [],
            'paid_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the payment is succeeded.
     */
    public function succeeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'succeeded',
            'paid_at' => now(),
        ]);
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the payment is failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the payment is refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
            'paid_at' => fake()->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }
}
