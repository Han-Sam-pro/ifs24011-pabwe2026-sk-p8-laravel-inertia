<?php

namespace Tests\Feature\Admin;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_member_cannot_access_admin_panel(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_admin_can_list_users_with_post_counts(): void
    {
        $member = User::factory()->create();
        Post::factory()->for($member)->count(2)->create();

        $this->actingAs($this->admin)
            ->get('/admin/users')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Users/Index')
                ->has('users.data', 2));
    }

    public function test_admin_can_monitor_all_posts_and_one_users_posts_read_only(): void
    {
        $member = User::factory()->create();
        $post = Post::factory()->for($member)->create();

        $this->actingAs($this->admin)
            ->get('/admin/posts')
            ->assertInertia(fn ($page) => $page->component('Admin/Posts/Index')->has('posts.data', 1));

        $this->actingAs($this->admin)
            ->get("/admin/users/{$member->id}/posts")
            ->assertInertia(fn ($page) => $page->where('owner.id', $member->id)->has('posts.data', 1));

        // Tidak ada rute ubah/hapus artikel untuk admin.
        $this->put("/dashboard/posts/{$post->id}", ['title' => 'x', 'category' => 'Umum', 'content' => 'x'])
            ->assertForbidden();
        $this->delete("/dashboard/posts/{$post->id}")->assertForbidden();
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => $post->title]);
    }

    public function test_admin_can_export_all_and_per_user_posts(): void
    {
        $member = User::factory()->create(['username' => 'anggota']);
        Post::factory()->for($member)->create();

        $this->actingAs($this->admin)->get('/admin/posts/export')
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->actingAs($this->admin)->get("/admin/users/{$member->id}/posts/export")
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_admin_can_change_role_of_other_user(): void
    {
        $member = User::factory()->create();

        $this->actingAs($this->admin)
            ->patch("/admin/users/{$member->id}/role", ['role' => 'admin'])
            ->assertSessionHas('success');

        $this->assertSame(User::ROLE_ADMIN, $member->refresh()->role);
    }

    public function test_admin_cannot_change_own_role_or_delete_self(): void
    {
        $this->actingAs($this->admin)
            ->patch("/admin/users/{$this->admin->id}/role", ['role' => 'member'])
            ->assertSessionHas('error');

        $this->actingAs($this->admin)
            ->delete("/admin/users/{$this->admin->id}")
            ->assertSessionHas('error');

        $this->assertTrue($this->admin->refresh()->isAdmin());
    }

    public function test_invalid_role_is_rejected(): void
    {
        $member = User::factory()->create();

        $this->actingAs($this->admin)
            ->patch("/admin/users/{$member->id}/role", ['role' => 'superuser'])
            ->assertSessionHas('error');

        $this->assertSame(User::ROLE_MEMBER, $member->refresh()->role);
    }

    public function test_admin_can_delete_other_user_and_their_posts_cascade(): void
    {
        $member = User::factory()->create();
        $post = Post::factory()->for($member)->create();

        $this->actingAs($this->admin)->delete("/admin/users/{$member->id}")->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $member->id]);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
