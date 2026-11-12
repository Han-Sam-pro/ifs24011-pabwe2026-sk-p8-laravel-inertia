<?php

use App\Features\Admin\Controllers\AdminPostController;
use App\Features\Admin\Controllers\AdminUserController;
use App\Features\Auth\Controllers\AuthController;
use App\Features\Post\Controllers\PostAiController;
use App\Features\Post\Controllers\PostController;
use App\Features\Post\Controllers\PostExcelController;
use App\Features\Post\Controllers\PublicPostController;
use App\Features\Profile\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik (tanpa login)
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicPostController::class, 'home'])->name('home');
Route::get('/articles', [PublicPostController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [PublicPostController::class, 'show'])->name('articles.show');

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Profil (semua peran yang sudah login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| Anggota: Dashboard Penulis
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'ensure.role:'.User::ROLE_MEMBER])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
        Route::redirect('/', '/dashboard/posts')->name('index');

        Route::prefix('posts')->name('posts.')->group(function () {
            Route::get('/', [PostController::class, 'index'])->name('index');
            Route::post('/', [PostController::class, 'store'])->name('store');

            // Rute statis didaftarkan sebelum {post} agar tidak tertangkap parameter.
            Route::get('/export', [PostExcelController::class, 'exportOwn'])->name('export');
            Route::post('/import', [PostExcelController::class, 'import'])->name('import');
            Route::post('/ai-generate', [PostAiController::class, 'generate'])->name('ai-generate');
            Route::post('/ai-save', [PostAiController::class, 'save'])->name('ai-save');

            Route::middleware('ensure.post.ownership')->group(function () {
                Route::get('/{post}', [PostController::class, 'show'])->name('show');
                Route::put('/{post}', [PostController::class, 'update'])->name('update');
                Route::delete('/{post}', [PostController::class, 'destroy'])->name('destroy');
                Route::patch('/{post}/publish', [PostController::class, 'togglePublish'])->name('publish');
                Route::post('/{post}/cover', [PostController::class, 'storeCover'])->name('cover.store');
                Route::delete('/{post}/cover', [PostController::class, 'destroyCover'])->name('cover.destroy');
            });
        });
    });

/*
|--------------------------------------------------------------------------
| Admin: Panel Admin (monitoring, read-only untuk artikel)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'ensure.role:'.User::ROLE_ADMIN])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::redirect('/', '/admin/users')->name('index');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::get('/users/{user}/posts', [AdminPostController::class, 'userPosts'])->name('users.posts');
        Route::get('/users/{user}/posts/export', [AdminPostController::class, 'exportUserPosts'])->name('users.posts.export');

        Route::get('/posts', [AdminPostController::class, 'index'])->name('posts.index');
        Route::get('/posts/export', [AdminPostController::class, 'exportAll'])->name('posts.export');
    });
