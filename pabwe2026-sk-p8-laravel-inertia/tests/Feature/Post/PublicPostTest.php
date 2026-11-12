<?php

namespace Tests\Feature\Post;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_only_published_articles(): void
    {
        Post::factory()->published()->create(['title' => 'Terbit']);
        Post::factory()->create(['title' => 'Draf']);

        $this->get('/')->assertOk()->assertInertia(fn ($page) => $page
            ->component('Public/Home')
            ->has('latest', 1)
            ->where('latest.0.title', 'Terbit'));
    }

    public function test_articles_can_be_searched_and_filtered_by_category(): void
    {
        Post::factory()->published()->create(['title' => 'Tips Laravel', 'category' => 'Teknologi']);
        Post::factory()->published()->create(['title' => 'Tips Makan', 'category' => 'Gaya Hidup']);
        Post::factory()->create(['title' => 'Laravel Draf', 'category' => 'Teknologi']);

        $this->get('/articles?q=laravel&kategori=Teknologi')->assertInertia(fn ($page) => $page
            ->component('Public/Articles')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Tips Laravel')
            ->where('filters.q', 'laravel'));
    }

    public function test_search_matches_content_and_ignores_unknown_category(): void
    {
        Post::factory()->published()->create(['title' => 'Judul', 'content' => 'Membahas Inertia']);

        $this->get('/articles?q=inertia&kategori=Tidak-Ada')->assertInertia(fn ($page) => $page
            ->has('posts.data', 1));
    }

    public function test_published_article_detail_is_accessible_and_drafts_are_404(): void
    {
        $published = Post::factory()->published()->create(['slug' => 'artikel-terbit']);
        $draft = Post::factory()->create(['slug' => 'artikel-draf']);

        $this->get('/articles/artikel-terbit')->assertOk()->assertInertia(fn ($page) => $page
            ->component('Public/ArticleShow')
            ->where('post.slug', $published->slug));

        $this->get('/articles/'.$draft->slug)->assertNotFound();
        $this->get('/articles/tidak-ada')->assertNotFound();
    }

    public function test_guest_can_view_public_pages_without_login(): void
    {
        $this->assertGuest();
        $this->get('/articles')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_logged_user_sees_article_list_too(): void
    {
        $this->actingAs(User::factory()->create())->get('/articles')->assertOk();
    }
}
