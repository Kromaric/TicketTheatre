<?php

namespace Tests\Feature;

use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function postWebhook(string $event, array $payment): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/payment-webhook', [
            'event'   => $event,
            'payment' => $payment,
        ]);
    }

    public function test_payment_succeeded_confirms_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'payment_id'     => 'pay_123',
            'status'         => 'pending',
            'payment_status' => 'pending',
            'expires_at'     => now()->addMinutes(15),
        ]);

        $this->postWebhook('payment.succeeded', [
            'id' => 'pay_123',
        ])->assertOk();

        $reservation->refresh();
        $this->assertSame('confirmed', $reservation->status);
        $this->assertSame('paid', $reservation->payment_status);
        $this->assertNotNull($reservation->confirmed_at);
    }

    public function test_payment_failed_cancels_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'payment_id'     => 'pay_456',
            'status'         => 'pending',
            'payment_status' => 'pending',
        ]);

        $this->postWebhook('payment.failed', [
            'id' => 'pay_456',
        ])->assertOk();

        $reservation->refresh();
        $this->assertSame('cancelled', $reservation->status);
        $this->assertSame('failed', $reservation->payment_status);
    }

    public function test_payment_refunded_cancels_reservation(): void
    {
        $reservation = Reservation::factory()->confirmed()->create([
            'payment_id' => 'pay_789',
        ]);

        $this->postWebhook('payment.refunded', [
            'id' => 'pay_789',
        ])->assertOk();

        $reservation->refresh();
        $this->assertSame('cancelled', $reservation->status);
        $this->assertSame('refunded', $reservation->payment_status);
    }

    public function test_payment_canceled_cancels_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'payment_id' => 'pay_000',
            'status'     => 'pending',
        ]);

        $this->postWebhook('payment.canceled', [
            'id' => 'pay_000',
        ])->assertOk();

        $this->assertSame('cancelled', $reservation->fresh()->status);
    }

    public function test_webhook_returns_404_for_unknown_payment_id(): void
    {
        $this->postWebhook('payment.succeeded', [
            'id' => 'pay_does_not_exist',
        ])->assertNotFound();
    }

    public function test_webhook_requires_event_field(): void
    {
        $this->postJson('/api/payment-webhook', ['payment' => ['id' => 'pay_1']])
            ->assertStatus(400);
    }
}