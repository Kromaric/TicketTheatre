<?php

namespace Tests\Unit\Models;

use App\Models\Hall;
use App\Models\Reservation;
use App\Models\Seance;
use App\Models\Spectacle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_seance_belongs_to_spectacle(): void
    {
        $spectacle = Spectacle::factory()->create();
        $seance    = Seance::factory()->create(['spectacle_id' => $spectacle->id]);

        $this->assertTrue($seance->spectacle->is($spectacle));
    }

    public function test_seance_belongs_to_hall(): void
    {
        $hall   = Hall::factory()->create();
        $seance = Seance::factory()->create(['hall_id' => $hall->id]);

        $this->assertTrue($seance->hall->is($hall));
    }

    public function test_seance_has_many_reservations(): void
    {
        $seance = Seance::factory()->create();
        Reservation::factory()->count(2)->create(['seance_id' => $seance->id]);

        $this->assertCount(2, $seance->reservations);
    }

    public function test_factory_past_state(): void
    {
        $seance = Seance::factory()->past()->create();

        $this->assertLessThan(now(), $seance->date_seance);
        $this->assertSame('completed', $seance->status);
    }
}