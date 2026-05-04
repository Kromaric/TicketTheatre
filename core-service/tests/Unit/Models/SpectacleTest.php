<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Seance;
use App\Models\Spectacle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpectacleTest extends TestCase
{
    use RefreshDatabase;

    public function test_spectacle_belongs_to_category(): void
    {
        $category  = Category::factory()->create();
        $spectacle = Spectacle::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($spectacle->category->is($category));
    }

    public function test_spectacle_has_many_seances(): void
    {
        $spectacle = Spectacle::factory()->create();
        Seance::factory()->count(3)->create(['spectacle_id' => $spectacle->id]);

        $this->assertCount(3, $spectacle->seances);
    }

    public function test_actors_cast_to_array(): void
    {
        $spectacle = Spectacle::factory()->create(['actors' => ['Alice', 'Bob']]);

        $this->assertIsArray($spectacle->fresh()->actors);
        $this->assertContains('Alice', $spectacle->actors);
    }

    public function test_is_published_defaults_to_true(): void
    {
        $spectacle = Spectacle::factory()->create();

        $this->assertTrue($spectacle->is_published);
    }
}