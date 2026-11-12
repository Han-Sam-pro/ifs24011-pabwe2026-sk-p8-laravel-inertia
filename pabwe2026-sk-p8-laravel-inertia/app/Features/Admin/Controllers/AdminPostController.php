<?php

namespace App\Features\Admin\Controllers;

use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Monitoring artikel (read-only). Admin tidak memiliki rute ubah/hapus artikel di sini.
 */
class AdminPostController extends Controller
{
    public function __construct(
        private readonly PostService $posts,
        private readonly PostExcelService $excel,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Posts/Index', [
            'posts' => $this->posts->paginateAll(),
            'owner' => null,
        ]);
    }

    public function userPosts(User $user): Response
    {
        return Inertia::render('Admin/Posts/Index', [
            'posts' => $this->posts->paginateForAuthor($user),
            'owner' => $user->only('id', 'name', 'username'),
        ]);
    }

    public function exportAll(): StreamedResponse
    {
        return $this->excel->export(
            $this->posts->allWithAuthors(),
            'artikel-semua-'.now()->format('Ymd-His').'.xlsx',
        );
    }

    public function exportUserPosts(User $user): StreamedResponse
    {
        return $this->excel->export(
            $this->posts->allForAuthor($user),
            'artikel-'.$user->username.'.xlsx',
        );
    }
}
