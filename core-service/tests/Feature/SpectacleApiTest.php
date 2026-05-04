<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Hall;
use App\Models\Seance;
use App\Models\Spectacle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SpectacleApiTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
    }

    // --- GET /public/spectacles ---

    public function test_index_returns_paginated_spectacles(): void
    {
        Spectacle::factory()->count(5)->create();

        $response = $this->getJson('/api/public/spectacles');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['data', 'current_page', 'total']]);
    }

    public function test_index_filters_by_category_id(): void
    {
        $cat1 = Category::factory()->create();
        $cat2 = Category::factory()->create();
        Spectacle::factory()->create(['category_id' => $cat1->id]);
        Spectacle::factory()->count(2)->create(['category_id' => $cat2->id]);

        $response = $this->getJson("/api/public/spectacles?category_id={$cat1->id}");

        $response->assertOk();
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_index_filters_by_published_status(): void
    {
        Spectacle::factory()->create(['is_published' => true]);
        Spectacle::factory()->unpublished()->create();

        $response = $this->getJson('/api/public/spectacles?is_published=true');

        $response->assertOk();
        foreach ($response->json('data.data') as $spectacle) {
            $this->assertTrue($spectacle['is_published']);
        }
    }

    public function test_index_searches_by_title(): void
    {
        Spectacle::factory()->create(['title' => 'Le Grand Théâtre']);
        Spectacle::factory()->create(['title' => 'Autre Spectacle']);

        $response = $this->getJson('/api/public/spectacles?search=Grand');

        $response->assertOk();
        $this->assertCount(1, $response->json('data.data'));
        $this->assertStringContainsString('Grand', $response->json('data.data.0.title'));
    }

    // --- GET /public/spectacles/upcoming ---

    public function test_upcoming_returns_published_spectacles_with_future_seances(): void
    {
        $hall      = Hall::factory()->create(['capacity' => 100]);
        $spectacle = Spectacle::factory()->create(['is_published' => true, 'status' => 'upcoming']);
        Seance::factory()->create([
            'spectacle_id'    => $spectacle->id,
            'hall_id'         => $hall->id,
            'available_seats' => 50,
        ]);

        $unpublished = Spectacle::factory()->unpublished()->create(['status' => 'upcoming']);
        Seance::factory()->create(['spectacle_id' => $unpublished->id, 'hall_id' => $hall->id]);

        $response = $this->getJson('/api/public/spectacles/upcoming');

        $response->assertOk();
        $ids = array_column($response->json('data'), 'id');
        $this->assertContains($spectacle->id, $ids);
        $this->assertNotContains($unpublished->id, $ids);
    }

    // --- GET /public/spectacles/{spectacle} ---

    public function test_show_returns_spectacle_with_upcoming_seances(): void
    {
        $hall      = Hall::factory()->create(['capacity' => 200]);
        $spectacle = Spectacle::factory()->create();
        Seance::factory()->create(['spectacle_id' => $spectacle->id, 'hall_id' => $hall->id]);
        Seance::factory()->past()->create(['spectacle_id' => $spectacle->id, 'hall_id' => $hall->id]);

        $response = $this->getJson("/api/public/spectacles/{$spectacle->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $spectacle->id);
    }

    // --- POST /spectacles ---

    public function test_store_creates_spectacle(): void
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();

        $response = $this->postJson('/api/spectacles', [
            'title'      => 'Nouveau Spectacle',
            'base_price' => 25.00,
            'category_id'=> $category->id,
            'duration'   => 120,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('spectacles', ['title' => 'Nouveau Spectacle']);
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/spectacles', ['title' => 'Test', 'base_price' => 10])
            ->assertUnauthorized();
    }

    public function test_store_requires_base_price(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/spectacles', ['title' => 'Test'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['base_price']);
    }

    public function test_store_validates_status_enum(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/spectacles', [
            'title'      => 'Test',
            'base_price' => 10,
            'status'     => 'invalid_status',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    // --- PUT /spectacles/{spectacle} ---

    public function test_update_modifies_spectacle(): void
    {
        $this->actingAsAdmin();
        $spectacle = Spectacle::factory()->create();

        $this->putJson("/api/spectacles/{$spectacle->id}", ['title' => 'Titre Modifié'])
            ->assertOk();

        $this->assertDatabaseHas('spectacles', ['id' => $spectacle->id, 'title' => 'Titre Modifié']);
    }

    // --- DELETE /spectacles/{spectacle} ---

    public function test_destroy_deletes_spectacle_without_upcoming_seances(): void
    {
        $this->actingAsAdmin();
        $spectacle = Spectacle::factory()->create();

        $this->deleteJson("/api/spectacles/{$spectacle->id}")->assertOk();

        $this->assertDatabaseMissing('spectacles', ['id' => $spectacle->id]);
    }

    public function test_destroy_fails_when_spectacle_has_upcoming_seances(): void
    {
        $this->actingAsAdmin();
        $spectacle = Spectacle::factory()->create();
        Seance::factory()->create(['spectacle_id' => $spectacle->id]);

        $this->deleteJson("/api/spectacles/{$spectacle->id}")
            ->assertUnprocessable();
    }
}