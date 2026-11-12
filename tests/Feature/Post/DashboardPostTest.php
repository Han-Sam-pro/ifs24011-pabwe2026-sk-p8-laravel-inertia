<?php

namespace Tests\Feature\Post;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardPostTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create();
    }

    public function test_member_can_list_own_posts_with_stats(): void
    {
        Post::factory()->for($this->author)->published()->create();
        Post::factory()->for($this->author)->create();
        Post::factory()->create(); // milik orang lain

        $this->actingAs($this->author)
            ->get('/dashboard/posts')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Post/Index')
                ->where('stats.total', 2)
                ->where('stats.published', 1)
                ->where('stats.draft', 1)
                ->has('posts.data', 2));
    }

    public function test_store_creates_post_with_unique_slug_and_publish_timestamp(): void
    {
        $this->actingAs($this->author);

        $payload = [
            'title' => 'Belajar Laravel',
            'category' => 'Teknologi',
            'excerpt' => 'Ringkasan',
            'content' => '# Halo',
            'is_published' => true,
        ];

        $this->post('/dashboard/posts', $payload)->assertSessionHas('success');
        $this->post('/dashboard/posts', $payload)->assertSessionHas('success');

        $this->assertDatabaseHas('posts', ['slug' => 'belajar-laravel', 'user_id' => $this->author->id]);
        $this->assertDatabaseHas('posts', ['slug' => 'belajar-laravel-2']);
        $this->assertNotNull(Post::where('slug', 'belajar-laravel')->first()->published_at);
    }

    public function test_store_rejects_invalid_category_and_missing_title(): void
    {
        $this->actingAs($this->author)
            ->post('/dashboard/posts', ['title' => '', 'category' => 'Tidak Ada', 'content' => 'x'])
            ->assertSessionHasErrors(['title', 'category']);
    }

    public function test_author_can_update_own_post_and_publish_timestamp_follows_status(): void
    {
        $post = Post::factory()->for($this->author)->create(['title' => 'Lama']);

        $this->actingAs($this->author)
            ->put("/dashboard/posts/{$post->id}", [
                'title' => 'Baru',
                'category' => 'Bisnis',
                'content' => 'Isi',
                'is_published' => true,
            ])->assertSessionHas('success');

        $post->refresh();
        $this->assertSame('Baru', $post->title);
        $this->assertSame('baru', $post->slug);
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
    }

    public function test_author_cannot_modify_or_view_others_post(): void
    {
        $other = Post::factory()->create();

        $this->actingAs($this->author);

        $this->get("/dashboard/posts/{$other->id}")->assertForbidden();
        $this->put("/dashboard/posts/{$other->id}", ['title' => 'x', 'category' => 'Umum', 'content' => 'x'])->assertForbidden();
        $this->delete("/dashboard/posts/{$other->id}")->assertForbidden();
        $this->patch("/dashboard/posts/{$other->id}/publish")->assertForbidden();

        $this->assertDatabaseHas('posts', ['id' => $other->id, 'title' => $other->title]);
    }

    public function test_show_renders_detail_page_for_owner(): void
    {
        $post = Post::factory()->for($this->author)->create();

        $this->actingAs($this->author)
            ->get("/dashboard/posts/{$post->id}")
            ->assertInertia(fn ($page) => $page->component('Post/Show')->where('post.id', $post->id));
    }

    public function test_toggle_publish_switches_status_and_clears_published_at(): void
    {
        $post = Post::factory()->for($this->author)->published()->create();

        $this->actingAs($this->author)->patch("/dashboard/posts/{$post->id}/publish")->assertSessionHas('success');
        $post->refresh();
        $this->assertFalse($post->is_published);
        $this->assertNull($post->published_at);

        $this->patch("/dashboard/posts/{$post->id}/publish");
        $post->refresh();
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
    }

    public function test_author_can_delete_own_post_and_its_cover(): void
    {
        Storage::fake('public');
        $post = Post::factory()->for($this->author)->create();

        $this->actingAs($this->author)
            ->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('c.jpg')])
            ->assertSessionHas('success');

        $post->refresh();
        Storage::disk('public')->assertExists($post->cover_image_path);

        $this->delete("/dashboard/posts/{$post->id}")->assertRedirect(route('posts.index'));

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        Storage::disk('public')->assertMissing($post->cover_image_path);
    }

    public function test_cover_can_be_replaced_and_removed(): void
    {
        Storage::fake('public');
        $post = Post::factory()->for($this->author)->create();
        $this->actingAs($this->author);

        $this->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('a.png')]);
        $first = $post->refresh()->cover_image_path;

        $this->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('b.jpg')]);
        $second = $post->refresh()->cover_image_path;

        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        $this->delete("/dashboard/posts/{$post->id}/cover")->assertSessionHas('success');
        $this->assertNull($post->refresh()->cover_image_path);
    }

    public function test_cover_rejects_non_image_files(): void
    {
        $post = Post::factory()->for($this->author)->create();

        $this->actingAs($this->author)
            ->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->create('x.pdf', 10)])
            ->assertSessionHasErrors('cover');
    }

    public function test_admin_cannot_access_member_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/dashboard/posts')
            ->assertForbidden();
    }
}
