<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Services\PostService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPostController extends Controller
{
    public function __construct(
        private readonly PostService $posts,
    ) {}

    public function home(): Response
    {
        return Inertia::render('Public/Home', [
            'latest' => $this->posts->latestPublished(6),
        ]);
    }

    public function index(Request $request): Response
    {
        $search = $request->string('q')->trim()->toString();
        $category = $request->string('kategori')->trim()->toString();

        return Inertia::render('Public/Articles', [
            'posts' => $this->posts->paginatePublished($search, $category),
            'filters' => ['q' => $search, 'kategori' => $category],
            'categories' => PostCategory::options(),
        ]);
    }

    public function show(string $slug): Response
    {
        $post = $this->posts->findPublishedBySlug($slug);

        abort_if($post === null, 404);

        return Inertia::render('Public/ArticleShow', [
            'post' => $post,
        ]);
    }
}
