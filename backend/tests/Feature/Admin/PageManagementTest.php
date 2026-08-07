<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setup(): void
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

    public function test_guest_cannot_access_admin_pages(): void
    {
        $this->getJson('/api/v1/admin/pages')->assertUnauthorized();
    }

    public function test_admin_can_create_a_page(): void
    {
        $admin = $this->admin();
        $response = $this->actingAs($admin)->postJson('/api/v1/admin/pages', [
            'title' => 'About Me',
            'body_html' => '<p>Jude Hashane</p>',
            'status' => 'draft',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('pages', [
            'title' => 'About Me',
            'slug' => 'about-me',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
    }

    public function test_page_creation_requires_valid_data(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson('/api/v1/admin/pages', [
            'title' => '',
            'body_html' => '',
            'status' => 'not-a-real-status',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'body_html', 'status']);
    }

    public function test_duplicate_slugs_are_automatically_deduplicated(): void
    {
        $admin = $this->admin();

        Page::factory()->create(['slug' => 'about-me']);

        $this->actingAs($admin)->postJson('/api/v1/admin/pages', [
            'title' => 'About Me',
            'body_html' => '<p>Jude Hashane</p>',
            'status' => 'draft',
        ])->assertCreated();

        $this->assertDatabaseHas('pages', ['slug' => 'about-me-1']);
    }

    public function test_explicit_duplicate_slug_is_rejected_by_validation(): void
    {
        $admin = $this->admin();

        Page::factory()->create(['slug' => 'taken-slug']);

        $this->actingAs($admin)->postJson('/api/v1/admin/pages', [
            'title' => 'Something Else',
            'slug' => 'taken-slug',
            'body_html' => '<p>Hello</p>',
            'status' => 'draft',
        ])->assertUnprocessable()->assertJsonValidationErrors(['slug']);
    }
}
