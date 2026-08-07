<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SpaAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_protected_endpoint(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'hashane@123']);

        $this->fromFrontend()->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'hashane@123',
        ])->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'hashane@123']);

        $this->fromFrontend()->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'hashane@1234',
        ])->assertUnprocessable();

        $this->assertGuest();
    }

    public function test_authenticated_user_can_fetch_profile_with_roles_and_privileges(): void
    {
        $user = User::factory()->create();

        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Permission::create(['name' => 'pages.list', 'guard_name' => 'web']);
        $role->givePermissionTo('pages.list');
        $user->assignRole('admin');

        $response = $this->actingAs($user)->getJson('/api/v1/me');

        $response->assertOk()->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => ['admin'],
                'privileges' => ['pages.list'],
            ],
        ]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->fromFrontend()->actingAs($user)
            ->postJson('/api/v1/logout')
            ->assertOk();

        $this->assertGuest('web');
    }

    /**
     * Simulate request from SPA
     * 
     */
    private function fromFrontend(): static
    {
        return $this->withHeader('Referer', 'http://localhost');
    }
}
