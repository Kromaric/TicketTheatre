<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthValidationTest extends TestCase
{
    use RefreshDatabase;

    // --- validate-credentials ---

    public function test_validate_credentials_returns_user_on_valid_login(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret123')]);

        $response = $this->postJson('/api/validate-credentials', [
            'email'    => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonPath('email', $user->email)
            ->assertJsonPath('id', $user->id);
    }

    public function test_validate_credentials_returns_401_on_wrong_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret123')]);

        $response = $this->postJson('/api/validate-credentials', [
            'email'    => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertUnauthorized();
    }

    public function test_validate_credentials_returns_401_on_unknown_email(): void
    {
        $response = $this->postJson('/api/validate-credentials', [
            'email'    => 'nobody@example.com',
            'password' => 'password',
        ]);

        $response->assertUnauthorized();
    }

    public function test_validate_credentials_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/validate-credentials', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    // --- register ---

    public function test_register_creates_user_and_returns_201(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name'            => 'Marie',
            'last_name'             => 'Curie',
            'email'                 => 'marie@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('users', ['email' => 'marie@example.com']);
    }

    public function test_register_assigns_user_role_by_default(): void
    {
        $this->postJson('/api/register', [
            'first_name'            => 'Test',
            'last_name'             => 'User',
            'email'                 => 'test@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'role' => 'user']);
    }

    public function test_register_fails_on_duplicate_email(): void
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->postJson('/api/register', [
            'first_name'            => 'Other',
            'last_name'             => 'User',
            'email'                 => 'duplicate@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_fails_when_passwords_dont_match(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name'            => 'Test',
            'last_name'             => 'User',
            'email'                 => 'test@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'different',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_fails_when_password_too_short(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name'            => 'Test',
            'last_name'             => 'User',
            'email'                 => 'test@example.com',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}