<?php

namespace Tests\Unit\Models;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_name_attribute_concatenates_first_and_last_name(): void
    {
        $user = User::factory()->make([
            'first_name' => 'Jean',
            'last_name'  => 'Dupont',
        ]);

        $this->assertSame('Jean Dupont', $user->full_name);
    }

    public function test_user_has_many_reservations(): void
    {
        $user = User::factory()->create();
        Reservation::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->reservations);
    }

    public function test_password_is_hidden_in_serialization(): void
    {
        $user = User::factory()->make();

        $this->assertArrayNotHasKey('password', $user->toArray());
    }

    public function test_preferences_cast_to_array(): void
    {
        $user = User::factory()->create(['preferences' => ['lang' => 'fr']]);
        $user->refresh();

        $this->assertIsArray($user->preferences);
        $this->assertSame('fr', $user->preferences['lang']);
    }

    public function test_factory_admin_state_sets_role(): void
    {
        $admin = User::factory()->admin()->make();

        $this->assertSame('admin', $admin->role);
    }
}