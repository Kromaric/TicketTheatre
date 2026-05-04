<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Spectacle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_many_spectacles(): void
    {
        $category = Category::factory()->create();
        Spectacle::factory()->count(2)->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->spectacles);
    }

    public function test_color_stores_hex_value(): void
    {
        $category = Category::factory()->create(['color' => '#FF5733']);

        $this->assertSame('#FF5733', $category->fresh()->color);
    }
}