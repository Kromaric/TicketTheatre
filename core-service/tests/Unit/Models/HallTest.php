<?php

namespace Tests\Unit\Models;

use App\Models\Hall;
use App\Models\Seance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HallTest extends TestCase
{
    use RefreshDatabase;

    public function test_hall_has_many_seances(): void
    {
        $hall = Hall::factory()->create();
        Seance::factory()->count(3)->create(['hall_id' => $hall->id]);

        $this->assertCount(3, $hall->seances);
    }

    public function test_amenities_cast_to_array(): void
    {
        $hall = Hall::factory()->create(['amenities' => ['Bar', 'Climatisation']]);
        $hall->refresh();

        $this->assertIsArray($hall->amenities);
        $this->assertContains('Bar', $hall->amenities);
    }

    public function test_is_active_defaults_to_true(): void
    {
        $hall = Hall::factory()->create();

        $this->assertTrue($hall->is_active);
    }

    public function test_factory_inactive_state(): void
    {
        $hall = Hall::factory()->inactive()->create();

        $this->assertFalse($hall->fresh()->is_active);
    }
}