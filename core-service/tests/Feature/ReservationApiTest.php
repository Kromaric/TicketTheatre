<?php

namespace Tests\Feature;

use App\Models\Hall;
use App\Models\Reservation;
use App\Models\Seance;
use App\Models\Spectacle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReservationApiTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
    }

    private function makeSeance(int $capacity = 100, int $seats = 100): Seance
    {
        $hall = Hall::factory()->withCapacity($capacity)->create();

        return Seance::factory()->create([
            'hall_id'         => $hall->id,
            'available_seats' => $seats,
            'status'          => 'scheduled',
        ]);
    }

    // --- POST /reservations ---

    public function test_store_creates_pending_reservation(): void
    {
        $user   = User::factory()->create();
        $seance = $this->makeSeance();

        $response = $this->postJson('/api/reservations', [
            'user_id'   => $user->id,
            'seance_id' => $seance->id,
            'quantity'  => 2,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.payment_status', 'pending');

        $this->assertDatabaseHas('reservations', [
            'user_id'   => $user->id,
            'seance_id' => $seance->id,
            'quantity'  => 2,
        ]);
    }

    public function test_store_sets_expiry_to_15_minutes(): void
    {
        $user   = User::factory()->create();
        $seance = $this->makeSeance();

        $response = $this->postJson('/api/reservations', [
            'user_id'   => $user->id,
            'seance_id' => $seance->id,
            'quantity'  => 1,
        ]);

        $expiresAt = \Carbon\Carbon::parse($response->json('data.expires_at'));
        $this->assertEqualsWithDelta(now()->addMinutes(15)->timestamp, $expiresAt->timestamp, 5);
    }

    public function test_store_generates_booking_reference(): void
    {
        $user   = User::factory()->create();
        $seance = $this->makeSeance();

        $response = $this->postJson('/api/reservations', [
            'user_id'   => $user->id,
            'seance_id' => $seance->id,
            'quantity'  => 1,
        ]);

        $this->assertMatchesRegularExpression(
            '/^TH-\d{4}-[A-Z0-9]{6}$/',
            $response->json('data.booking_reference')
        );
    }

    public function test_store_fails_when_quantity_exceeds_available_seats(): void
    {
        $user   = User::factory()->create();
        $seance = $this->makeSeance(100, 3);

        $this->postJson('/api/reservations', [
            'user_id'   => $user->id,
            'seance_id' => $seance->id,
            'quantity'  => 5,
        ])->assertUnprocessable();
    }

    public function test_store_fails_when_seance_is_in_the_past(): void
    {
        $user   = User::factory()->create();
        $hall   = Hall::factory()->create(['capacity' => 100]);
        $seance = Seance::factory()->past()->create(['hall_id' => $hall->id]);

        $this->postJson('/api/reservations', [
            'user_id'   => $user->id,
            'seance_id' => $seance->id,
            'quantity'  => 1,
        ])->assertUnprocessable();
    }

    public function test_store_fails_when_seance_is_not_scheduled(): void
    {
        $user   = User::factory()->create();
        $seance = Seance::factory()->cancelled()->create();

        $this->postJson('/api/reservations', [
            'user_id'   => $user->id,
            'seance_id' => $seance->id,
            'quantity'  => 1,
        ])->assertUnprocessable();
    }

    public function test_store_validates_quantity_between_1_and_10(): void
    {
        $user   = User::factory()->create();
        $seance = $this->makeSeance();

        $this->postJson('/api/reservations', [
            'user_id'   => $user->id,
            'seance_id' => $seance->id,
            'quantity'  => 11,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['quantity']);
    }

    // --- GET /reservations (protected) ---

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/reservations')->assertUnauthorized();
    }

    public function test_index_returns_paginated_reservations(): void
    {
        $this->actingAsAdmin();
        Reservation::factory()->count(3)->create();

        $response = $this->getJson('/api/reservations');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['data', 'current_page']]);
    }

    public function test_index_filters_by_status(): void
    {
        $this->actingAsAdmin();
        Reservation::factory()->confirmed()->create();
        Reservation::factory()->cancelled()->create();

        $response = $this->getJson('/api/reservations?status=confirmed');

        $response->assertOk();
        foreach ($response->json('data.data') as $r) {
            $this->assertSame('confirmed', $r['status']);
        }
    }

    // --- GET /reservations/{reservation} ---

    public function test_show_requires_authentication(): void
    {
        $reservation = Reservation::factory()->create();

        $this->getJson("/api/reservations/{$reservation->id}")->assertUnauthorized();
    }

    public function test_show_returns_reservation_with_relations(): void
    {
        $this->actingAsAdmin();
        $reservation = Reservation::factory()->create();

        $response = $this->getJson("/api/reservations/{$reservation->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $reservation->id)
            ->assertJsonStructure(['data' => ['seance', 'user']]);
    }

    // --- GET /public/reservations/reference/{ref} ---

    public function test_get_by_reference_returns_reservation(): void
    {
        $reservation = Reservation::factory()->create(['booking_reference' => 'TH-2025-ABCD12']);

        $response = $this->getJson('/api/public/reservations/reference/TH-2025-ABCD12');

        $response->assertOk()
            ->assertJsonPath('data.booking_reference', 'TH-2025-ABCD12');
    }

    public function test_get_by_reference_returns_404_for_unknown_reference(): void
    {
        $this->getJson('/api/public/reservations/reference/TH-0000-XXXXXX')
            ->assertNotFound();
    }

    // --- POST /reservations/{reservation}/cancel ---

    public function test_cancel_sets_status_to_cancelled(): void
    {
        $this->actingAsAdmin();
        $reservation = Reservation::factory()->create(['status' => 'pending']);

        $response = $this->postJson("/api/reservations/{$reservation->id}/cancel", [
            'cancellation_reason' => 'Changement de plans',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cancel_fails_when_already_cancelled(): void
    {
        $this->actingAsAdmin();
        $reservation = Reservation::factory()->cancelled()->create();

        $this->postJson("/api/reservations/{$reservation->id}/cancel")
            ->assertUnprocessable();
    }

    public function test_cancel_requires_authentication(): void
    {
        $reservation = Reservation::factory()->create();

        $this->postJson("/api/reservations/{$reservation->id}/cancel")
            ->assertUnauthorized();
    }

    // --- POST /reservations/{reservation}/confirm-payment ---

    public function test_confirm_payment_sets_status_to_confirmed(): void
    {
        $this->actingAsAdmin();
        $reservation = Reservation::factory()->create([
            'status'         => 'pending',
            'payment_status' => 'pending',
            'expires_at'     => now()->addMinutes(10),
        ]);

        $response = $this->postJson("/api/reservations/{$reservation->id}/confirm-payment", [
            'payment_id' => 'pay_test_123',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('reservations', [
            'id'             => $reservation->id,
            'status'         => 'confirmed',
            'payment_status' => 'paid',
        ]);
    }

    public function test_confirm_payment_requires_payment_id(): void
    {
        $this->actingAsAdmin();
        $reservation = Reservation::factory()->create(['status' => 'pending']);

        $this->postJson("/api/reservations/{$reservation->id}/confirm-payment", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['payment_id']);
    }

    public function test_confirm_payment_fails_on_expired_reservation(): void
    {
        $this->actingAsAdmin();
        $reservation = Reservation::factory()->expired()->create();

        $this->postJson("/api/reservations/{$reservation->id}/confirm-payment", [
            'payment_id' => 'pay_test_123',
        ])->assertUnprocessable();
    }

    // --- GET /users/{userId}/reservations ---

    public function test_user_reservations_returns_user_reservations(): void
    {
        $this->actingAsAdmin();
        $user = User::factory()->create();
        Reservation::factory()->count(3)->create(['user_id' => $user->id]);
        Reservation::factory()->create();  // other user

        $response = $this->getJson("/api/users/{$user->id}/reservations");

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    public function test_user_reservations_requires_authentication(): void
    {
        $user = User::factory()->create();

        $this->getJson("/api/users/{$user->id}/reservations")->assertUnauthorized();
    }
}