<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserModelTest extends TestCase
{
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
    public function password_is_hidden_in_serialization(): void
    {
        $user = User::factory()->make();

        $this->assertArrayNotHasKey('password', $user->toArray());
    }

    /** @test */
    public function password_is_hashed_when_set_via_fill(): void
    {
        $user = new User();
        $user->fill(['password' => 'secret']);

        $this->assertTrue(Hash::check('secret', $user->password));
    }
}
