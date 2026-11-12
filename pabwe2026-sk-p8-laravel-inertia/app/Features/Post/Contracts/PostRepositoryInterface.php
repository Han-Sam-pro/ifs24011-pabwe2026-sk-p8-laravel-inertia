<?php

namespace App\Features\Post\Contracts;

use App\Features\Post\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Post>
     */
    public function paginateByUser(int $userId, int $perPage = 10): LengthAwarePaginator;

    /**
     * @return Collection<int, Post>
     */
    public function allByUser(int $userId): Collection;

    /**
     * @return LengthAwarePaginator<int, Post>
     */
    public function paginateAll(int $perPage = 15): LengthAwarePaginator;

    /**
     * @return Collection<int, Post>
     */
    public function allWithAuthors(): Collection;

    /**
     * @return LengthAwarePaginator<int, Post>
     */
    public function paginatePublished(?string $search, ?string $category, int $perPage = 9): LengthAwarePaginator;

    /**
     * @return Collection<int, Post>
     */
    public function latestPublished(int $limit): Collection;

    public function findPublishedBySlug(string $slug): ?Post;

    /**
     * @return array{total: int, published: int, draft: int}
     */
    public function countsByUser(int $userId): array;

    public function slugExists(string $slug, ?int $exceptId = null): bool;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Post;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Post $post, array $data): Post;

    public function delete(Post $post): void;
}
