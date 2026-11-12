<?php

namespace Tests\Unit\Post;

use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Enums\PostCategory;
use Tests\TestCase;

class PostCategoryTest extends TestCase
{
    public function test_enum_has_six_categories_with_labels(): void
    {
        $this->assertCount(6, PostCategory::cases());
        $this->assertSame('Teknologi & Koding', PostCategory::Teknologi->label());
        $this->assertSame(['Teknologi', 'Pendidikan', 'Gaya Hidup', 'Bisnis', 'Opini', 'Umum'], PostCategory::values());
        $this->assertCount(6, PostCategory::options());
    }

    public function test_from_input_accepts_value_or_label_case_insensitively(): void
    {
        $this->assertSame(PostCategory::Opini, PostCategory::fromInput('opini'));
        $this->assertSame(PostCategory::Bisnis, PostCategory::fromInput('Bisnis & Karir'));
        $this->assertNull(PostCategory::fromInput('Tidak Ada'));
        $this->assertNull(PostCategory::fromInput(null));
    }

    public function test_generated_item_falls_back_to_umum_for_unknown_category(): void
    {
        $item = GeneratedPostItem::fromArray(['title' => ' Judul ', 'category' => 'Hewan', 'content' => ' Isi ']);

        $this->assertSame('Judul', $item->title);
        $this->assertSame('Umum', $item->category);
        $this->assertSame('Isi', $item->content);
        $this->assertSame('Tanpa Judul', GeneratedPostItem::fromArray([])->title);
    }
}
