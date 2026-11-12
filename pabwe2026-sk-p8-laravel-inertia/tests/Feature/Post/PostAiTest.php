<?php

namespace Tests\Feature\Post;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PostAiTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_returns_drafts_to_the_page(): void
    {
        $this->bindFakeAi(fn (int $count) => [
            new GeneratedPostItem('Judul AI', 'Teknologi', 'Ringkas', 'Isi AI'),
        ]);

        $this->actingAs(User::factory()->create())
            ->post('/dashboard/posts/ai-generate', ['jumlah' => 1])
            ->assertSessionHas('aiDrafts', [[
                'title' => 'Judul AI',
                'category' => 'Teknologi',
                'excerpt' => 'Ringkas',
                'content' => 'Isi AI',
            ]]);
    }

    public function test_generate_validates_min_and_max_count(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/dashboard/posts/ai-generate', ['jumlah' => 0])->assertSessionHasErrors('jumlah');
        $this->post('/dashboard/posts/ai-generate', ['jumlah' => 6])->assertSessionHasErrors('jumlah');
    }

    public function test_generate_reports_ai_failure_as_error_message(): void
    {
        $this->bindFakeAi(fn () => throw new RuntimeException('Kuota habis'));

        $this->actingAs(User::factory()->create())
            ->post('/dashboard/posts/ai-generate', ['jumlah' => 2])
            ->assertSessionHas('error', 'Kuota habis');
    }

    public function test_save_stores_batch_as_drafts_for_current_user(): void
    {
        $author = User::factory()->create();

        $this->actingAs($author)->post('/dashboard/posts/ai-save', [
            'items' => [
                ['title' => 'A', 'category' => 'Bisnis', 'excerpt' => null, 'content' => 'Isi A'],
                ['title' => 'B', 'category' => 'Umum', 'excerpt' => 'Ringkas B', 'content' => 'Isi B'],
            ],
        ])->assertSessionHas('success');

        $this->assertSame(2, Post::where('user_id', $author->id)->where('is_published', false)->count());
    }

    public function test_save_validates_items(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/dashboard/posts/ai-save', ['items' => [['title' => '', 'category' => 'X', 'content' => '']]])
            ->assertSessionHasErrors(['items.0.title', 'items.0.category', 'items.0.content']);
    }

    private function bindFakeAi(callable $generator): void
    {
        $this->app->instance(AiClientInterface::class, new class($generator) implements AiClientInterface
        {
            public function __construct(private $generator) {}

            public function generate(int $count): array
            {
                return ($this->generator)($count);
            }
        });
    }
}
