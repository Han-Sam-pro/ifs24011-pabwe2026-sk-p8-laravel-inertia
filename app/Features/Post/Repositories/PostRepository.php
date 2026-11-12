<?php

namespace App\Features\Post\Repositories;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PostRepository implements PostRepositoryInterface
{
    public function paginateByUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Post::query()
            ->where('user_id', $userId)
            ->latest('updated_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allByUser(int $userId): Collection
    {
        return Post::query()
            ->with('user:id,name,username')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get();
    }

    public function paginateAll(int $perPage = 15): LengthAwarePaginator
    {
        return Post::query()
            ->with('user:id,name,username')
            ->latest('updated_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allWithAuthors(): Collection
    {
        return Post::query()
            ->with('user:id,name,username')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function paginatePublished(?string $search, ?string $category, int $perPage = 9): LengthAwarePaginator
    {
        return Post::query()
            ->with('user:id,name')
            ->published()
            ->search($search)
            ->category($category)
            ->latest('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function latestPublished(int $limit): Collection
    {
        return Post::query()
            ->with('user:id,name')
            ->published()
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        return Post::query()
            ->with('user:id,name')
            ->published()
            ->where('slug', $slug)
            ->first();
    }

    public function countsByUser(int $userId): array
    {
        $row = Post::query()
            ->where('user_id', $userId)
            ->selectRaw('count(*) as total')
            ->selectRaw('coalesce(sum(case when is_published then 1 else 0 end), 0) as published')
            ->selectRaw('coalesce(sum(case when is_published then 0 else 1 end), 0) as draft')
            ->first();

        return [
            'total' => (int) $row->total,
            'published' => (int) $row->published,
            'draft' => (int) $row->draft,
        ];
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        return Post::query()
            ->where('slug', $slug)
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))
            ->exists();
    }

    public function create(array $data): Post
    {
        return Post::query()->create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        return $post->refresh();
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }
}
