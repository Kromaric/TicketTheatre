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

class SeanceApiTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
    }

    // --- GET /public/seances ---

    public function test_index_returns_paginated_seances(): void
    {
        Seance::factory()->count(3)->create();

        $response = $this->getJson('/api/public/seances');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['data', 'current_page']]);
    }

    public function test_index_filters_by_spectacle_id(): void
    {
        $spectacle = Spectacle::factory()->create();
        Seance::factory()->create(['spectacle_id' => $spectacle->id]);
        Seance::factory()->create();

        $response = $this->getJson("/api/public/seances?spectacle_id={$spectacle->id}");

        $response->assertOk();
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_index_upcoming_only_filter(): void
    {
        $hall = Hall::factory()->create(['capacity' => 100]);
        $sp   = Spectacle::factory()->create();
        Seance::factory()->create(['spectacle_id' => $sp->id, 'hall_id' => $hall->id]);
        Seance::factory()->past()->create(['spectacle_id' => $sp->id, 'hall_id' => $hall->id]);

        $response = $this->getJson('/api/public/seances?upcoming_only=true');

        $response->assertOk();
        foreach ($response->json('data.data') as $seance) {
            $this->assertGreaterThan(now()->toDateTimeString(), $seance['date_seance']);
        }
    }

    // --- GET /public/seances/{seance} ---

    public function test_show_returns_seance_with_remaining_seats(): void
    {
        $hall   = Hall::factory()->create(['capacity' => 100]);
        $seance = Seance::factory()->create(['hall_id' => $hall->id, 'available_seats' => 50]);
        Reservation::factory()->create([
            'seance_id' => $seance->id,
            'quantity'  => 10,
            'status'    => 'confirmed',
        ]);

        $response = $this->getJson("/api/public/seances/{$seance->id}");

        $response->assertOk()
            ->assertJsonPath('data.remaining_seats', 40);
    }

    // --- GET /public/seances/{seance}/available-seats ---

    public function test_available_seats_returns_correct_counts(): void
    {
        $hall   = Hall::factory()->create(['capacity' => 100]);
        $seance = Seance::factory()->create(['hall_id' => $hall->id, 'available_seats' => 100]);
        Reservation::factory()->create([
            'seance_id' => $seance->id,
            'quantity'  => 20,
            'status'    => 'confirmed',
        ]);

        $response = $this->getJson("/api/public/seances/{$seance->id}/available-seats");

        $response->assertOk()
            ->assertJsonPath('data.total_seats', 100)
            ->assertJsonPath('data.booked_seats', 20)
            ->assertJsonPath('data.remaining_seats', 80)
            ->assertJsonPath('data.is_available', true);
    }

    // --- POST /seances ---

    public function test_store_creates_seance(): void
    {
        $this->actingAsAdmin();
        $spectacle = Spectacle::factory()->create();
        $hall      = Hall::factory()->withCapacity(200)->create();

        $response = $this->postJson('/api/seances', [
            'spectacle_id'    => $spectacle->id,
            'hall_id'         => $hall->id,
            'date_seance'     => now()->addDays(10)->toDateTimeString(),
            'available_seats' => 150,
            'price'           => 25.00,
            'status'          => 'scheduled',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);
    }

    public function test_store_fails_when_available_seats_exceed_hall_capacity(): void
    {
        $this->actingAsAdmin();
        $spectacle = Spectacle::factory()->create();
        $hall      = Hall::factory()->withCapacity(50)->create();

        $this->postJson('/api/seances', [
            'spectacle_id'    => $spectacle->id,
            'hall_id'         => $hall->id,
            'date_seance'     => now()->addDays(10)->toDateTimeString(),
            'available_seats' => 100,
            'price'           => 20.00,
        ])->assertUnprocessable();
    }

    public function test_store_fails_when_hall_already_occupied_at_same_time(): void
    {
        $this->actingAsAdmin();
        $spectacle = Spectacle::factory()->create();
        $hall      = Hall::factory()->withCapacity(200)->create();
        $date      = now()->addDays(5)->toDateTimeString();

        Seance::factory()->create([
            'hall_id'     => $hall->id,
            'date_seance' => $date,
        ]);

        $this->postJson('/api/seances', [
            'spectacle_id'    => $spectacle->id,
            'hall_id'         => $hall->id,
            'date_seance'     => $date,
            'available_seats' => 100,
            'price'           => 20.00,
        ])->assertUnprocessable();
    }

    public function test_store_requires_future_date(): void
    {
        $this->actingAsAdmin();
        $spectacle = Spectacle::factory()->create();
        $hall      = Hall::factory()->create();

        $this->postJson('/api/seances', [
            'spectacle_id'    => $spectacle->id,
            'hall_id'         => $hall->id,
            'date_seance'     => now()->subDay()->toDateTimeString(),
            'available_seats' => 50,
            'price'           => 20.00,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['date_seance']);
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/seances', [])->assertUnauthorized();
    }

    // --- DELETE /seances/{seance} ---

    public function test_destroy_deletes_seance_without_confirmed_reservations(): void
    {
        $this->actingAsAdmin();
        $seance = Seance::factory()->create();

        $this->deleteJson("/api/seances/{$seance->id}")->assertOk();

        $this->assertDatabaseMissing('seances', ['id' => $seance->id]);
    }

    public function test_destroy_fails_when_seance_has_confirmed_reservations(): void
    {
        $this->actingAsAdmin();
        $seance = Seance::factory()->create();
        Reservation::factory()->confirmed()->create(['seance_id' => $seance->id]);

        $this->deleteJson("/api/seances/{$seance->id}")
            ->assertUnprocessable();
    }
}