<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Features\Post\Requests\PostRequest;
use App\Features\Post\Services\PostService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function __construct(
        private readonly PostService $posts,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Post/Index', [
            'posts' => $this->posts->paginateForAuthor($user),
            'stats' => $this->posts->statsFor($user),
            'categories' => PostCategory::options(),
        ]);
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $this->posts->create($request->user(), $request->validated());

        return back()->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function show(Post $post): Response
    {
        $post->load('user:id,name');

        return Inertia::render('Post/Show', [
            'post' => $post,
        ]);
    }

    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $this->posts->update($post, $request->validated());

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->posts->delete($post);

        return redirect()->route('posts.index')->with('success', 'Artikel berhasil dihapus.');
    }

    public function togglePublish(Post $post): RedirectResponse
    {
        $post = $this->posts->togglePublish($post);

        return back()->with('success', $post->is_published ? 'Artikel dipublikasikan.' : 'Artikel dijadikan draf.');
    }

    public function storeCover(Request $request, Post $post): RedirectResponse
    {
        $request->validate([
            'cover' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $this->posts->storeCover($post, $request->file('cover'));

        return back()->with('success', 'Sampul berhasil diunggah.');
    }

    public function destroyCover(Post $post): RedirectResponse
    {
        $this->posts->removeCover($post);

        return back()->with('success', 'Sampul berhasil dihapus.');
    }
}
