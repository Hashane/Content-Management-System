<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_obtain_a_token_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'hashane@123']);

        $response = $this->postJson('/api/v1/auth/tokens', [
            'email' => $user->email,
            'password' => 'hashane@123',
        ]);

        $response->assertCreated()->assertJsonStructure(['data' => ['token']]);
    }

    public function test_token_request_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'hashane@123']);

        $this->postJson('/api/v1/auth/tokens', [
            'email' => $user->email,
            'password' => 'hashane@1234',
        ])->assertUnprocessable();
    }

    public function test_token_can_authenticate_a_protected_endpoint(): void
    {
        $user = User::factory()->create(['password' => 'hashane@123']);

        $token = $this->postJson('/api/v1/auth/tokens', [
            'email' => $user->email,
            'password' => 'hashane@123',
        ])->json('data.token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_revoked_token_cannot_authenticate(): void
    {
        $user = User::factory()->create(['password' => 'hashane@123']);

        $token = $this->postJson('/api/v1/auth/tokens', [
            'email' => $user->email,
            'password' => 'hashane@123',
        ])->json('data.token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson('/api/v1/auth/tokens/current')
            ->assertOk();

        // Skip Sanctum's cache & force re-check
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }

    public function test_revoking_without_a_bearer_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->deleteJson('/api/v1/auth/tokens/current')
            ->assertUnauthorized();
    }

    public function test_token_expires_after_the_configured_lifetime(): void
    {
        $user = User::factory()->create(['password' => 'hashane@123']);

        $token = $this->postJson('/api/v1/auth/tokens', [
            'email' => $user->email,
            'password' => 'hashane@123',
        ])->json('data.token');

        $user->tokens()->first()->forceFill([
            'created_at' => now()->subMinutes(config('sanctum.expiration') + 1),
        ])->save();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }
}
