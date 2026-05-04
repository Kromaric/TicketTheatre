<?php

namespace Tests\Unit\Models;

use App\Models\Reservation;
use App\Models\Seance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_belongs_to_user(): void
    {
        $user        = User::factory()->create();
        $reservation = Reservation::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($reservation->user->is($user));
    }

    public function test_reservation_belongs_to_seance(): void
    {
        $seance      = Seance::factory()->create();
        $reservation = Reservation::factory()->create(['seance_id' => $seance->id]);

        $this->assertTrue($reservation->seance->is($seance));
    }

    public function test_seats_cast_to_array_when_set(): void
    {
        $reservation = Reservation::factory()->create(['seats' => ['A1', 'A2']]);

        $this->assertIsArray($reservation->fresh()->seats);
        $this->assertContains('A1', $reservation->seats);
    }

    public function test_booking_reference_format(): void
    {
        $reservation = Reservation::factory()->create();

        $this->assertMatchesRegularExpression('/^TH-\d{4}-[A-Z0-9]{6}$/', $reservation->booking_reference);
    }

    public function test_factory_confirmed_state(): void
    {
        $reservation = Reservation::factory()->confirmed()->create();

        $this->assertSame('confirmed', $reservation->status);
        $this->assertSame('paid', $reservation->payment_status);
        $this->assertNotNull($reservation->confirmed_at);
    }

    public function test_factory_cancelled_state(): void
    {
        $reservation = Reservation::factory()->cancelled()->create();

        $this->assertSame('cancelled', $reservation->status);
        $this->assertNotNull($reservation->cancelled_at);
    }
}