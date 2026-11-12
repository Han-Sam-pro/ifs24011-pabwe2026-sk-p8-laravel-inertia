<?php

namespace App\Features\Post\Services;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostService
{
    public const COVER_DISK = 'public';

    public function __construct(
        private readonly PostRepositoryInterface $posts,
    ) {}

    public function paginateForAuthor(User $user): LengthAwarePaginator
    {
        return $this->posts->paginateByUser($user->id);
    }

    /**
     * @return array{total: int, published: int, draft: int}
     */
    public function statsFor(User $user): array
    {
        return $this->posts->countsByUser($user->id);
    }

    /**
     * @return Collection<int, Post>
     */
    public function allForAuthor(User $user): Collection
    {
        return $this->posts->allByUser($user->id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Post
    {
        return $this->posts->create($this->preparePayload($user, $data));
    }

    /**
     * Simpan banyak artikel sekaligus dalam satu transaksi (dipakai impor Excel & hasil AI).
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function createMany(User $user, array $rows): int
    {
        return DB::transaction(function () use ($user, $rows) {
            foreach ($rows as $row) {
                $this->create($user, $row);
            }

            return count($rows);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Post $post, array $data): Post
    {
        $payload = $this->preparePayload($post->user, $data, $post);

        return $this->posts->update($post, $payload);
    }

    public function delete(Post $post): void
    {
        $this->deleteCoverFile($post->cover_image_path);
        $this->posts->delete($post);
    }

    public function togglePublish(Post $post): Post
    {
        $isPublished = ! $post->is_published;

        return $this->posts->update($post, [
            'is_published' => $isPublished,
            'published_at' => $isPublished ? now() : null,
        ]);
    }

    public function storeCover(Post $post, UploadedFile $file): Post
    {
        $path = $file->storePublicly('covers', self::COVER_DISK);

        $this->deleteCoverFile($post->cover_image_path);

        return $this->posts->update($post, ['cover_image_path' => $path]);
    }

    public function removeCover(Post $post): Post
    {
        $this->deleteCoverFile($post->cover_image_path);

        return $this->posts->update($post, ['cover_image_path' => null]);
    }

    public function paginateAll(): LengthAwarePaginator
    {
        return $this->posts->paginateAll();
    }

    /**
     * @return Collection<int, Post>
     */
    public function allWithAuthors(): Collection
    {
        return $this->posts->allWithAuthors();
    }

    public function paginatePublished(?string $search, ?string $category): LengthAwarePaginator
    {
        return $this->posts->paginatePublished($search, $category);
    }

    public function latestPublished(int $limit = 6): Collection
    {
        return $this->posts->latestPublished($limit);
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        return $this->posts->findPublishedBySlug($slug);
    }

    /**
     * Buat slug unik dari judul: "judul-artikel", lalu "judul-artikel-2", dst.
     */
    public function generateUniqueSlug(string $title, ?int $exceptId = null): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $counter = 2;

        while ($this->posts->slugExists($slug, $exceptId)) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function preparePayload(User $user, array $data, ?Post $current = null): array
    {
        $isPublished = (bool) ($data['is_published'] ?? $current?->is_published ?? false);

        $category = $data['category'] ?? PostCategory::Umum->value;
        if ($category instanceof PostCategory) {
            $category = $category->value;
        }

        $title = (string) $data['title'];

        $payload = [
            'user_id' => $user->id,
            'title' => $title,
            'slug' => $this->generateUniqueSlug($title, $current?->id),
            'category' => $category,
            'excerpt' => $data['excerpt'] ?? null,
            'content' => (string) ($data['content'] ?? ''),
            'is_published' => $isPublished,
            'published_at' => $isPublished ? ($current?->published_at ?? now()) : null,
        ];

        if ($current !== null) {
            unset($payload['user_id']);
        }

        return $payload;
    }

    private function deleteCoverFile(?string $path): void
    {
        if ($path) {
            Storage::disk(self::COVER_DISK)->delete($path);
        }
    }
}
