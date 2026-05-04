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

    /** @test */
    public function login_fails_with_wrong_password()
    {
        User::factory()->create([
            'email'    => 'bob@example.com',
            'password' => bcrypt('correct'),
            'is_active'=> true,
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'bob@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function login_fails_when_user_does_not_exist()
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'nobody@example.com',
            'password' => 'whatever',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function login_fails_when_account_is_inactive()
    {
        User::factory()->inactive()->create([
            'email'    => 'inactive@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function login_requires_email_field()
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function login_requires_password_field()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function login_requires_valid_email_format()
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function login_deletes_previous_tokens_and_creates_a_new_one()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'is_active'=> true,
        ]);

        // Premier login
        $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        // Deuxième login : le premier token doit être supprimé
        $response = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
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

        $response = $this->withToken($token)->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['success' => true, 'message' => 'Déconnexion réussie']);

        // Le token courant doit être supprimé
        $this->assertCount(0, $user->fresh()->tokens);
    }

    /** @test */
    public function logout_requires_authentication()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    /** @test */
    public function logout_does_not_revoke_other_user_tokens()
    {
        $userA  = User::factory()->create();
        $userB  = User::factory()->create();

        $tokenA = $userA->createToken('auth-token')->plainTextToken;
        $userB->createToken('auth-token');   // token passif

        $this->withToken($tokenA)->postJson('/api/logout');

        // UserB conserve son token
        $this->assertCount(1, $userB->fresh()->tokens);
    }

    // ===================================================================
    // GET /api/user
    // ===================================================================

    /** @test */
    public function me_returns_authenticated_user()
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
    }

    /** @test */
    public function me_requires_authentication()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    /** @test */
    public function me_does_not_expose_password()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $this->assertArrayNotHasKey('password', $response->json('user'));
    }

    // ===================================================================
    // Scénarios d'intégration complets (login → me → logout)
    // ===================================================================

    /** @test */
    public function full_auth_flow_login_me_logout()
    {
        $user = User::factory()->create([
            'password'  => bcrypt('mypassword'),
            'is_active' => true,
        ]);

        // 1. Login
        $loginResponse = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'mypassword',
        ]);
        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('token');

        // 2. Me
        $meResponse = $this->withToken($token)->getJson('/api/user');
        $meResponse->assertStatus(200)
                   ->assertJson(['user' => ['email' => $user->email]]);

        // 3. Logout
        $logoutResponse = $this->withToken($token)->postJson('/api/logout');
        $logoutResponse->assertStatus(200);

        // 4. Vérifier que le token a bien été révoqué en BD
        // (Sanctum met en cache le token résolu dans le même processus PHP,
        // ce qui rend un appel HTTP post-logout non fiable en test)
        $this->assertCount(0, $user->fresh()->tokens);
    }

    /** @test */
    public function user_cannot_access_protected_route_with_invalid_token()
    {
        $this->withToken('invalid-token-xxx')
             ->getJson('/api/user')
             ->assertStatus(401);
    }

    /** @test */
    public function admin_user_has_role_admin_in_response()
    {
        $admin = User::factory()->admin()->create([
            'password'  => bcrypt('adminpass'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => $admin->email,
            'password' => 'adminpass',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['user' => ['role' => 'admin']]);
    }
}
