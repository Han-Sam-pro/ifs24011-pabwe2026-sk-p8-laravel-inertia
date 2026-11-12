<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostExcelController extends Controller
{
    public function __construct(
        private readonly PostService $posts,
        private readonly PostExcelService $excel,
    ) {}

    public function exportOwn(Request $request): StreamedResponse
    {
        $user = $request->user();

        return $this->excel->export(
            $this->posts->allForAuthor($user),
            'artikel-saya-'.now()->format('Ymd-His').'.xlsx',
        );
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv', 'max:5120'],
        ]);

        try {
            $report = $this->excel->import($request->file('file'), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()
            ->with('success', "{$report['imported']} artikel berhasil diimpor sebagai draf/publik sesuai kolom Status.")
            ->with('importReport', $report);
    }
}
