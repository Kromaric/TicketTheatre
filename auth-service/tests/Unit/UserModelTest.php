<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tests unitaires pour le modèle User.
 *
 * Règle : on utilise make() (sans BD) pour tester la logique pure du modèle.
 * Les tests qui nécessitent une persistance utilisent RefreshDatabase avec la
 * migration de test qui reproduit le schéma du core-service.
 */
class UserModelTest extends TestCase
{
    // ---------------------------------------------------------------
    // Accessor full_name  (pas de BD nécessaire)
    // ---------------------------------------------------------------

    /** @test */
    public function it_returns_full_name_from_accessor(): void
    {
        $user = User::factory()->make([
            'first_name' => 'Jean',
            'last_name'  => 'Dupont',
        ]);

        $this->assertSame('Jean Dupont', $user->full_name);
    }

    /** @test */
    public function full_name_contains_first_and_last_name(): void
    {
        $user = User::factory()->make([
            'first_name' => 'Marie',
            'last_name'  => 'Curie',
        ]);

        $this->assertStringContainsString('Marie', $user->full_name);
        $this->assertStringContainsString('Curie', $user->full_name);
    }

    // ---------------------------------------------------------------
    // Champs cachés (pas de BD nécessaire)
    // ---------------------------------------------------------------

    /** @test */
    public function password_is_hidden_in_serialization(): void
    {
        $user = User::factory()->make();

        $this->assertArrayNotHasKey('password', $user->toArray());
    }

    /** @test */
    public function remember_token_is_hidden_in_serialization(): void
    {
        $user = User::factory()->make();

        $this->assertArrayNotHasKey('remember_token', $user->toArray());
    }

    // ---------------------------------------------------------------
    // Casts (pas de BD nécessaire)
    // ---------------------------------------------------------------

    /** @test */
    public function is_active_is_cast_to_boolean(): void
    {
        $user = User::factory()->make(['is_active' => 1]);

        $this->assertIsBool($user->is_active);
        $this->assertTrue($user->is_active);
    }

    /** @test */
    public function preferences_is_cast_to_array(): void
    {
        $user = User::factory()->make([
            'preferences' => ['lang' => 'fr', 'notifications' => true],
        ]);

        $this->assertIsArray($user->preferences);
        $this->assertSame('fr', $user->preferences['lang']);
    }

    /** @test */
    public function date_of_birth_is_cast_to_carbon(): void
    {
        $user = User::factory()->make(['date_of_birth' => '1990-05-15']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->date_of_birth);
    }

    // ---------------------------------------------------------------
    // Password hashing via cast 'hashed' (pas de BD nécessaire)
    // ---------------------------------------------------------------

    /** @test */
    public function password_is_hashed_when_set_via_fill(): void
    {
        $user = new User();
        $user->fill(['password' => 'secret']);

        $this->assertTrue(Hash::check('secret', $user->password));
    }

    // ---------------------------------------------------------------
    // États de la factory (pas de BD nécessaire)
    // ---------------------------------------------------------------

    /** @test */
    public function inactive_factory_state_sets_is_active_false(): void
    {
        $user = User::factory()->inactive()->make();

        $this->assertFalse($user->is_active);
    }

    /** @test */
    public function admin_factory_state_sets_role_admin(): void
    {
        $user = User::factory()->admin()->make();

        $this->assertSame('admin', $user->role);
    }

    // ---------------------------------------------------------------
    // Tests avec persistance BD (migration de test requise)
    // ---------------------------------------------------------------

    /** @test */
    public function user_can_be_created_with_factory(): void
    {
        // On utilise RefreshDatabase uniquement dans ce groupe
        $this->refreshDatabase();

        $user = User::factory()->create();

        $this->assertDatabaseHas('users', ['email' => $user->email]);
    }

    /** @test */
    public function soft_deleted_user_is_not_returned_by_default(): void
    {
        $this->refreshDatabase();

        $user = User::factory()->create();
        $user->delete();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertNull(User::find($user->id));
    }

    /** @test */
    public function soft_deleted_user_is_returned_with_trashed(): void
    {
        $this->refreshDatabase();

        $user = User::factory()->create();
        $user->delete();

        $this->assertNotNull(User::withTrashed()->find($user->id));
    }
}
