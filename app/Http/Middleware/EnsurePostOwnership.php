<?php

namespace App\Http\Middleware;

use App\Features\Post\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsurePostOwnership
{
    /**
     * Pastikan artikel pada rute {post} milik pengguna yang sedang login (via PostPolicy::update).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $post = $request->route('post');

        if (! $post instanceof Post) {
            $post = Post::query()->findOrFail($post);
            $request->route()->setParameter('post', $post);
        }

        Gate::authorize('update', $post);

        return $next($request);
    }
}
