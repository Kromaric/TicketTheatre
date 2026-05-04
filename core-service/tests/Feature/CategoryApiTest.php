<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Spectacle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
    }

    // --- GET /public/categories ---

    public function test_index_returns_all_categories_with_spectacles_count(): void
    {
        $category = Category::factory()->create();
        Spectacle::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/public/categories');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.spectacles_count', 3);
    }

    // --- GET /public/categories/{category} ---

    public function test_show_returns_category_with_spectacles(): void
    {
        $category  = Category::factory()->create();
        Spectacle::factory()->count(2)->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/public/categories/{$category->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonCount(2, 'data.spectacles');
    }

    public function test_show_returns_404_for_missing_category(): void
    {
        $this->getJson('/api/public/categories/999')->assertNotFound();
    }

    // --- POST /categories ---

    public function test_store_creates_category_when_authenticated(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/categories', [
            'name'        => 'Comédie',
            'description' => 'Spectacles comiques',
            'color'       => '#FFAA00',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('categories', ['name' => 'Comédie']);
    }

    public function test_store_auto_generates_slug(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/categories', ['name' => 'Mon Spectacle']);

        $this->assertDatabaseHas('categories', ['slug' => 'mon-spectacle']);
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/categories', ['name' => 'Test'])
            ->assertUnauthorized();
    }

    public function test_store_requires_unique_name(): void
    {
        $this->actingAsAdmin();
        Category::factory()->create(['name' => 'Existant']);

        $this->postJson('/api/categories', ['name' => 'Existant'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_rejects_invalid_color_format(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/categories', ['name' => 'Test', 'color' => 'red'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['color']);
    }

    // --- PUT /categories/{category} ---

    public function test_update_modifies_category(): void
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();

        $this->putJson("/api/categories/{$category->id}", ['name' => 'Nouveau Nom'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Nouveau Nom']);
    }

    public function test_update_requires_authentication(): void
    {
        $category = Category::factory()->create();

        $this->putJson("/api/categories/{$category->id}", ['name' => 'Nom'])
            ->assertUnauthorized();
    }

    // --- DELETE /categories/{category} ---

    public function test_destroy_deletes_category_without_spectacles(): void
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();

        $this->deleteJson("/api/categories/{$category->id}")->assertOk();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_destroy_fails_when_category_has_spectacles(): void
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();
        Spectacle::factory()->create(['category_id' => $category->id]);

        $this->deleteJson("/api/categories/{$category->id}")
            ->assertUnprocessable();
    }

    public function test_destroy_requires_authentication(): void
    {
        $category = Category::factory()->create();

        $this->deleteJson("/api/categories/{$category->id}")
            ->assertUnauthorized();
    }
}