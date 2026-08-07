<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function moderator(): User
    {
        $user = User::factory()->create();
        $user->assignRole('moderator');

        return $user;
    }

    public function test_guest_cannot_access_admin_menus(): void
    {
        $this->getJson('/api/v1/admin/menus')->assertUnauthorized();
    }

    public function test_admin_can_create_a_menu(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson('/api/v1/admin/menus', [
            'name' => 'Main Menu',
        ])->assertCreated();

        $this->assertDatabaseHas('menus', ['name' => 'Main Menu', 'slug' => 'main-menu']);
    }

    public function test_duplicate_menu_slugs_are_automatically_deduplicated(): void
    {
        $admin = $this->admin();
        Menu::factory()->create(['slug' => 'main-menu']);

        $this->actingAs($admin)->postJson('/api/v1/admin/menus', [
            'name' => 'Main Menu',
        ])->assertCreated();

        $this->assertDatabaseHas('menus', ['slug' => 'main-menu-1']);
    }

    public function test_admin_can_update_and_delete_a_menu(): void
    {
        $admin = $this->admin();
        $menu = Menu::factory()->create();

        $this->actingAs($admin)->putJson("/api/v1/admin/menus/{$menu->id}", [
            'name' => 'Renamed Menu',
        ])->assertOk();

        $this->assertDatabaseHas('menus', ['id' => $menu->id, 'name' => 'Renamed Menu']);

        $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/menus/{$menu->id}")
            ->assertOk();

        $this->assertSoftDeleted('menus', ['id' => $menu->id]);
    }

    /**
     * Moderator has neither menus.list nor menus.manage — proves menu
     * access isn't accidentally granted by an unrelated role/permission.
     */
    public function test_moderator_cannot_access_menus_at_all(): void
    {
        $moderator = $this->moderator();
        $menu = Menu::factory()->create();

        $this->actingAs($moderator)->getJson('/api/v1/admin/menus')->assertForbidden();
        $this->actingAs($moderator)->postJson('/api/v1/admin/menus', ['name' => 'X'])->assertForbidden();
        $this->actingAs($moderator)->deleteJson("/api/v1/admin/menus/{$menu->id}")->assertForbidden();
    }

    public function test_menu_creation_requires_a_name(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson('/api/v1/admin/menus', [
            'name' => '',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name']);
    }
}
