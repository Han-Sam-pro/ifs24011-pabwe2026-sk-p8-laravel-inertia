<?php

namespace App\Features\Post\Models;

use App\Features\Post\Enums\PostCategory;
use App\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['user_id', 'title', 'slug', 'category', 'excerpt', 'content', 'cover_image_path', 'is_published', 'published_at'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $appends = ['cover_url'];

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $pattern = '%'.addcslashes(mb_strtolower($term), '%_\\').'%';

        return $query->where(function (Builder $q) use ($pattern) {
            $q->whereRaw('lower(title) like ?', [$pattern])
                ->orWhereRaw('lower(content) like ?', [$pattern]);
        });
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        $enum = PostCategory::fromInput($category);

        return $enum ? $query->where('category', $enum->value) : $query;
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => $this->cover_image_path
            ? Storage::disk('public')->url($this->cover_image_path)
            : null);
    }

    protected function casts(): array
    {
        return [
            'category' => PostCategory::class,
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
