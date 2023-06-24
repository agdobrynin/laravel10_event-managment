<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testTakeTokenValidationError(): void
    {
        $this->postJson('/api/take-token', [])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors' => ['email', 'password']]);

        $this->postJson('/api/take-token', ['password' => 'password'])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors' => ['email']]);
    }

    public function testTakeTokenSuccess(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/take-token', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['token']);
    }

    public function testInvalidateToken401(): void
    {
        $this->deleteJson('/api/invalidate-token')
            ->assertUnauthorized();
    }

    public function testInvalidateTokenSuccess(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $this->deleteJson('/api/invalidate-token')
            ->assertNoContent();
    }
}
