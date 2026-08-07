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

    public function test_stored_html_is_sanitized(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson('/api/v1/admin/pages', [
            'title' => 'XSS Test',
            'body_html' => '<p>Safe</p><script>alert("You are hacked!!")</script>',
            'status' => 'draft',
        ])->assertCreated();

        $page = Page::firstWhere('title', 'XSS Test');
        $this->assertStringNotContainsString('<script>', $page->body_html);
        $this->assertStringContainsString('<p>Safe</p>', $page->body_html);
    }

    public function test_admin_can_update_a_page(): void
    {
        $admin = $this->admin();
        $page = Page::factory()->create(['title' => 'Old Title']);

        $this->actingAs($admin)->putJson("/api/v1/admin/pages/{$page->id}", [
            'title' => 'New Title',
            'body_html' => '<p>Updated</p>',
            'status' => 'draft',
        ])->assertOk();

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'New Title',
            'updated_by' => $admin->id,
        ]);
    }

    public function test_moderator_can_create_and_update_but_not_delete_or_restore(): void
    {
        $moderator = $this->moderator();
        $page = Page::factory()->create();

        $this->actingAs($moderator)->postJson('/api/v1/admin/pages', [
            'title' => 'Moderator Page',
            'body_html' => '<p>Hi</p>',
            'status' => 'draft',
        ])->assertCreated();

        $this->actingAs($moderator)->putJson("/api/v1/admin/pages/{$page->id}", [
            'title' => 'Edited by moderator',
            'body_html' => '<p>Hi</p>',
            'status' => 'draft',
        ])->assertOk();

        $this->actingAs($moderator)
            ->deleteJson("/api/v1/admin/pages/{$page->id}")
            ->assertForbidden();

        $page->delete();

        $this->actingAs($moderator)
            ->postJson("/api/v1/admin/pages/{$page->id}/restore")
            ->assertForbidden();
    }

    public function test_admin_can_delete_view_trash_and_restore_a_page(): void
    {
        $admin = $this->admin();
        $page = Page::factory()->create();

        $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/pages/{$page->id}")
            ->assertOk();

        $this->assertSoftDeleted('pages', ['id' => $page->id]);
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'deleted_by' => $admin->id]);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/pages/trash')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $page->id);

        $this->actingAs($admin)
            ->postJson("/api/v1/admin/pages/{$page->id}/restore")
            ->assertOk();

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'deleted_at' => null, 'deleted_by' => null]);
    }

    public function test_deleted_pages_do_not_appear_in_the_default_listing(): void
    {
        $admin = $this->admin();
        $page = Page::factory()->create();
        $page->delete();

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/pages');

        $response->assertOk();
        $this->assertNotContains($page->id, collect($response->json('data.data'))->pluck('id'));
    }

    public function test_pages_can_be_searched_by_title(): void
    {
        $admin = $this->admin();
        Page::factory()->create(['title' => 'Company Overview']);
        Page::factory()->create(['title' => 'Contact Page']);

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/pages?search=Company');

        $titles = collect($response->json('data.data'))->pluck('title');
        $this->assertTrue($titles->contains('Company Overview'));
        $this->assertFalse($titles->contains('Contact Page'));
    }

    public function test_pages_can_be_filtered_by_status(): void
    {
        $admin = $this->admin();
        Page::factory()->draft()->create(['title' => 'Draft Page']);
        Page::factory()->published()->create(['title' => 'Published Page']);

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/pages?status=published');

        $titles = collect($response->json('data.data'))->pluck('title');
        $this->assertTrue($titles->contains('Published Page'));
        $this->assertFalse($titles->contains('Draft Page'));
    }
}
