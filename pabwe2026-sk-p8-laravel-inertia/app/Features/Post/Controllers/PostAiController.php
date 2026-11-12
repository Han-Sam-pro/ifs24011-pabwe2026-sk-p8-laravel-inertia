<?php

namespace App\Features\Post\Controllers;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Services\PostService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PostAiController extends Controller
{
    public const MAX_BATCH = 5;

    public function __construct(
        private readonly AiClientInterface $ai,
        private readonly PostService $posts,
    ) {}

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1', 'max:'.self::MAX_BATCH],
        ]);

        try {
            $items = $this->ai->generate((int) $data['jumlah']);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()
            ->with('aiDrafts', array_map(fn ($item) => $item->toArray(), $items))
            ->with('success', count($items).' draf artikel berhasil dibuat AI. Tinjau lalu simpan.');
    }

    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:'.self::MAX_BATCH],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.category' => ['required', 'string', 'in:'.implode(',', PostCategory::values())],
            'items.*.excerpt' => ['nullable', 'string', 'max:500'],
            'items.*.content' => ['required', 'string'],
        ]);

        $rows = array_map(fn (array $item) => [
            'title' => $item['title'],
            'category' => $item['category'],
            'excerpt' => $item['excerpt'] ?? null,
            'content' => $item['content'],
            'is_published' => false,
        ], $data['items']);

        $saved = $this->posts->createMany($request->user(), $rows);

        return back()->with('success', "{$saved} draf artikel hasil AI disimpan.");
    }
}
