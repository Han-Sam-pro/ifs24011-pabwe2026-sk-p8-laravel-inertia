<?php

namespace App\Features\Ai\DTO;

use App\Features\Post\Enums\PostCategory;

final readonly class GeneratedPostItem
{
    public function __construct(
        public string $title,
        public string $category,
        public string $excerpt,
        public string $content,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $category = PostCategory::fromInput($data['category'] ?? null) ?? PostCategory::Umum;

        return new self(
            title: mb_substr(trim((string) ($data['title'] ?? 'Tanpa Judul')), 0, 255),
            category: $category->value,
            excerpt: mb_substr(trim((string) ($data['excerpt'] ?? '')), 0, 500),
            content: trim((string) ($data['content'] ?? '')),
        );
    }

    /**
     * @return array{title: string, category: string, excerpt: string, content: string}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'category' => $this->category,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
        ];
    }
}
