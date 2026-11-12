<?php

namespace Tests\Unit\Post;

use App\Features\Post\Models\Post;
use Tests\TestCase;

class PostModelTest extends TestCase
{
    public function test_blank_search_term_does_not_filter(): void
    {
        $this->assertSame(
            Post::query()->toSql(),
            Post::query()->search('   ')->toSql(),
        );
    }

    public function test_search_matches_title_or_content_and_escapes_wildcards(): void
    {
        $query = Post::query()->search('100%_Off');

        $this->assertStringContainsString('lower(title) like ?', $query->toSql());
        $this->assertContains('%100\%\_off%', $query->getBindings());
    }

    public function test_category_scope_ignores_unknown_values(): void
    {
        $this->assertSame(
            Post::query()->toSql(),
            Post::query()->category('tidak-dikenal')->toSql(),
        );
        $this->assertContains('Bisnis', Post::query()->category('bisnis')->getBindings());
    }

    public function test_cover_url_is_null_without_cover_and_public_url_with_cover(): void
    {
        $this->assertNull((new Post)->cover_url);
        $this->assertStringEndsWith('/storage/covers/a.jpg', (new Post(['cover_image_path' => 'covers/a.jpg']))->cover_url);
    }
}
