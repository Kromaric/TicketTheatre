<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests d'intégration pour AuthController.
 *
 * Couvre :
 *   POST /api/login
 *   POST /api/logout
 *   GET  /api/user
 */
class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // ===================================================================
    // POST /api/login
    // ===================================================================

    /** @test */
    public function login_returns_token_and_user_data_on_valid_credentials()
    {
        $user = User::factory()->create([
            'email'    => 'alice@example.com',
            'password' => bcrypt('secret123'),
            'is_active'=> true,
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'alice@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'token',
                     'user' => [
                         'id', 'first_name', 'last_name', 'full_name',
                         'email', 'role', 'phone_number', 'sex',
                         'date_of_birth', 'avatar', 'is_active',
                     ],
                 ])
                 ->assertJson(['success' => true])
                 ->assertJson(['user' => ['email' => 'alice@example.com']]);

        $this->assertNotEmpty($response->json('token'));
    }

    /**
     * @test
     *
     * L'API renvoie la même erreur 422 pour un mot de passe incorrect et pour
     * un compte inexistant — comportement intentionnel pour ne pas révéler
     * l'existence d'un compte.
     */
    public function login_rejects_invalid_credentials()
    {
        User::factory()->create([
            'email'    => 'bob@example.com',
            'password' => bcrypt('correct'),
            'is_active'=> true,
        ]);

        // Mot de passe erroné
        $this->postJson('/api/login', ['email' => 'bob@example.com', 'password' => 'wrong'])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['email']);

        // Compte inexistant
        $this->postJson('/api/login', ['email' => 'nobody@example.com', 'password' => 'whatever'])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function login_fails_when_account_is_inactive()
    {
        User::factory()->inactive()->create([
            'email'    => 'inactive@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->postJson('/api/login', [
            'email'    => 'inactive@example.com',
            'password' => 'password',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    // une seule session active à la fois, évite les tokens orphelins actifs
    public function login_deletes_previous_tokens_and_creates_a_new_one()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'is_active'=> true,
        ]);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password']);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])
             ->assertStatus(200);

        $this->assertCount(1, $user->fresh()->tokens);
    }

    // ===================================================================
    // POST /api/logout
    // ===================================================================

    /** @test */
    public function logout_revokes_current_token()
    {
        $user  = User::factory()->create(['is_active' => true]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')
             ->assertStatus(200)
             ->assertJson(['success' => true, 'message' => 'Déconnexion réussie']);

        $this->assertCount(0, $user->fresh()->tokens);
    }

    /** @test */
    public function logout_does_not_revoke_other_user_tokens()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $tokenA = $userA->createToken('auth-token')->plainTextToken;
        $userB->createToken('auth-token');

        $this->withToken($tokenA)->postJson('/api/logout');

        $this->assertCount(1, $userB->fresh()->tokens);
    }

    // ===================================================================
    // GET /api/user
    // ===================================================================

    /** @test */
    public function me_returns_authenticated_user_without_exposing_password()
    {
        $user = User::factory()->create([
            'first_name' => 'Clara',
            'last_name'  => 'Martin',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'user' => [
                         'id', 'first_name', 'last_name', 'full_name',
                         'email', 'role', 'phone_number', 'sex',
                         'date_of_birth', 'avatar', 'is_active',
                     ],
                 ])
                 ->assertJson([
                     'success' => true,
                     'user' => [
                         'first_name' => 'Clara',
                         'last_name'  => 'Martin',
                         'full_name'  => 'Clara Martin',
                     ],
                 ]);

        $this->assertArrayNotHasKey('password', $response->json('user'));
    }

    // ===================================================================
    // Routes protégées — middleware auth
    // ===================================================================

    /**
     * @test
     *
     * Vérifie que /api/logout et /api/user rejettent les requêtes sans token
     * valide, qu'il soit absent ou invalide.
     */
    public function protected_routes_reject_unauthenticated_requests()
    {
        // Sans token
        $this->postJson('/api/logout')->assertStatus(401);
        $this->getJson('/api/user')->assertStatus(401);

        // Avec token invalide
        $this->withToken('invalid-token-xxx')->getJson('/api/user')->assertStatus(401);
    }

}
