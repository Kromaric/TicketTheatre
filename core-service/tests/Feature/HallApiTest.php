<?php

namespace Tests\Feature;

use App\Models\Hall;
use App\Models\Seance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HallApiTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
    }

    // --- GET /public/halls ---

    public function test_index_returns_all_active_halls(): void
    {
        Hall::factory()->count(2)->create(['is_active' => true]);
        Hall::factory()->inactive()->create();

        $response = $this->getJson('/api/public/halls');

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_index_filters_by_type(): void
    {
        Hall::factory()->create(['type' => 'Grande salle']);
        Hall::factory()->create(['type' => 'Petit théâtre']);

        $response = $this->getJson('/api/public/halls?type=Grande+salle');

        $response->assertOk();
        foreach ($response->json('data') as $hall) {
            $this->assertSame('Grande salle', $hall['type']);
        }
    }

    // --- GET /public/halls/{hall} ---

    public function test_show_returns_hall_with_upcoming_seances(): void
    {
        $hall = Hall::factory()->create();
        Seance::factory()->create(['hall_id' => $hall->id]);  // future
        Seance::factory()->past()->create(['hall_id' => $hall->id]);

        $response = $this->getJson("/api/public/halls/{$hall->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $hall->id);
    }

    // --- POST /halls ---

    public function test_store_creates_hall(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/halls', [
            'name'     => 'Salle Lumière',
            'capacity' => 200,
            'type'     => 'Grande salle',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('halls', ['name' => 'Salle Lumière']);
    }

    public function test_store_requires_capacity_min_1(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/halls', ['name' => 'Test', 'capacity' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['capacity']);
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/halls', ['name' => 'Test', 'capacity' => 100])
            ->assertUnauthorized();
    }

    // --- PUT /halls/{hall} ---

    public function test_update_modifies_hall(): void
    {
        $this->actingAsAdmin();
        $hall = Hall::factory()->create();

        $this->putJson("/api/halls/{$hall->id}", ['name' => 'Nouveau Nom'])
            ->assertOk();

        $this->assertDatabaseHas('halls', ['id' => $hall->id, 'name' => 'Nouveau Nom']);
    }

    // --- DELETE /halls/{hall} ---

    public function test_destroy_deletes_hall_without_upcoming_seances(): void
    {
        $this->actingAsAdmin();
        $hall = Hall::factory()->create();

        $this->deleteJson("/api/halls/{$hall->id}")->assertOk();

        $this->assertDatabaseMissing('halls', ['id' => $hall->id]);
    }

    public function test_destroy_fails_when_hall_has_upcoming_seances(): void
    {
        $this->actingAsAdmin();
        $hall = Hall::factory()->create();
        Seance::factory()->create(['hall_id' => $hall->id]);  // future seance

        $this->deleteJson("/api/halls/{$hall->id}")
            ->assertUnprocessable();
    }

    // --- GET /halls/available ---

    public function test_get_available_requires_authentication(): void
    {
        $this->getJson('/api/halls/available?date_start=2030-01-01&date_end=2030-01-31')
            ->assertUnauthorized();
    }

    public function test_get_available_requires_date_range(): void
    {
        $this->actingAsAdmin();

        $this->getJson('/api/halls/available')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date_start', 'date_end']);
    }
}