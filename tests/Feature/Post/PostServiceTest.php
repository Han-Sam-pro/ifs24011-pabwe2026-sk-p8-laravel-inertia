<?php

namespace Tests\Feature\Post;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Services\PostService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_accepts_category_enum(): void
    {
        $user = User::factory()->create();

        $post = app(PostService::class)->create($user, [
            'title' => 'Dengan Enum',
            'category' => PostCategory::Bisnis,
            'content' => 'Isi',
        ]);

        $this->assertSame(PostCategory::Bisnis, $post->category);
    }
}
