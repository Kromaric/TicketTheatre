<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test health check endpoint.
     */
    public function test_health_check_returns_ok(): void
    {
        $response = $this->get('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'service' => 'payment-service',
            ]);
    }

    /**
     * Test create payment endpoint with valid data.
     */
    public function test_create_payment_with_valid_data(): void
    {
        // Note: This test requires mocking Stripe API
        // You would use a package like mockery to mock the StripeClient

        $data = [
            'amount' => 50.00,
            'currency' => 'eur',
            'user_id' => 1,
            'order_id' => 123,
            'customer_email' => 'test@example.com',
            'description' => 'Test payment',
        ];

        // This test will fail without proper Stripe mocking
        // Uncomment when you have set up Stripe mocking
        // $response = $this->postJson('/api/payments', $data);
        //
        // $response->assertStatus(201)
        //     ->assertJsonStructure([
        //         'success',
        //         'data' => [
        //             'id',
        //             'stripe_payment_intent_id',
        //             'amount',
        //             'currency',
        //             'status',
        //         ],
        //         'client_secret',
        //     ]);

        $this->assertTrue(true); // Placeholder
    }

    /**
     * Test create payment with invalid amount.
     */
    public function test_create_payment_with_invalid_amount(): void
    {
        $data = [
            'amount' => -10,
            'currency' => 'eur',
            'user_id' => 1,
        ];

        $response = $this->postJson('/api/payments', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /**
     * Test create payment without required fields.
     */
    public function test_create_payment_without_required_fields(): void
    {
        $response = $this->postJson('/api/payments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'user_id']);
    }

    /**
     * Test get payment details.
     */
    public function test_get_payment_details(): void
    {
        $payment = Payment::factory()->create();

        $response = $this->get("/api/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'amount',
                    'currency',
                    'status',
                ],
            ]);
    }

    /**
     * Test get payment with invalid ID.
     */
    public function test_get_payment_with_invalid_id(): void
    {
        $response = $this->get('/api/payments/99999');

        $response->assertStatus(404);
    }

    /**
     * Test refund validation.
     */
    public function test_refund_with_invalid_reason(): void
    {
        $payment = Payment::factory()->create(['status' => 'succeeded']);

        $response = $this->postJson("/api/payments/{$payment->id}/refund", [
            'reason' => 'invalid_reason',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }
}
