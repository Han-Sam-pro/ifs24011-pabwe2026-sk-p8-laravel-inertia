<?php

namespace App\Features\Post\Services;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostExcelService
{
    /** Kolom wajib pada berkas impor (dicocokkan dengan nama header, tidak sensitif huruf). */
    public const REQUIRED_HEADERS = ['judul', 'kategori'];

    public function __construct(
        private readonly PostService $posts,
    ) {}

    /**
     * @param  Collection<int, Post>  $posts
     */
    public function export(Collection $posts, string $filename): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Artikel');

        $sheet->fromArray(
            ['ID', 'Judul', 'Kategori', 'Ringkasan', 'Konten', 'Status', 'Tanggal Terbit', 'Penulis'],
            null,
            'A1',
        );

        $row = 2;
        foreach ($posts as $post) {
            $sheet->fromArray([
                $post->id,
                $post->title,
                $post->category->value,
                (string) $post->excerpt,
                $post->content,
                $post->is_published ? 'Publik' : 'Draf',
                $post->published_at?->format('Y-m-d H:i:s') ?? '',
                $post->user?->name ?? '',
            ], null, 'A'.$row++);
        }

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Impor artikel sebagai milik $user. Baris yang tidak valid dilaporkan, baris valid disimpan
     * dalam satu transaksi (lihat PostService::createMany).
     *
     * @return array{imported: int, failed: array<int, array{row: int, errors: array<int, string>}>}
     *
     * @throws InvalidArgumentException jika header wajib tidak ditemukan.
     */
    public function import(UploadedFile $file, User $user): array
    {
        $sheet = IOFactory::load($file->getRealPath())->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (count($rows) < 2) {
            throw new InvalidArgumentException('Berkas kosong atau tidak memiliki baris data.');
        }

        $headers = array_map(fn ($h) => Str::lower(trim((string) $h)), array_shift($rows));
        $missing = array_diff(self::REQUIRED_HEADERS, $headers);

        if ($missing !== []) {
            throw new InvalidArgumentException('Kolom wajib tidak ditemukan: '.implode(', ', $missing).'.');
        }

        $valid = [];
        $failed = [];

        foreach ($rows as $index => $cells) {
            $data = $this->mapRow($headers, $cells);

            if ($data === null) {
                continue; // baris kosong
            }

            $rowNumber = $index + 2; // +1 untuk header, +1 karena indeks mulai 0
            $validator = Validator::make($data, [
                'title' => ['required', 'string', 'max:255'],
                'category' => ['required', 'string'],
            ], [
                'title.required' => 'Judul wajib diisi.',
                'category.required' => 'Kategori wajib diisi.',
                'title.max' => 'Judul maksimal 255 karakter.',
            ]);

            $errors = $validator->errors()->all();

            $category = PostCategory::fromInput($data['category']);
            if ($category === null && $data['category'] !== '') {
                $errors[] = 'Kategori "'.$data['category'].'" tidak dikenal. Pilih: '.implode(', ', PostCategory::values()).'.';
            }

            if ($errors !== []) {
                $failed[] = ['row' => $rowNumber, 'errors' => $errors];

                continue;
            }

            $valid[] = [
                'title' => $data['title'],
                'category' => $category->value,
                'excerpt' => $data['excerpt'] !== '' ? $data['excerpt'] : null,
                'content' => $data['content'],
                'is_published' => Str::lower($data['status']) === 'publik',
            ];
        }

        $imported = $valid === [] ? 0 : $this->posts->createMany($user, $valid);

        return ['imported' => $imported, 'failed' => $failed];
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, mixed>  $cells
     * @return array{title: string, category: string, excerpt: string, content: string, status: string}|null
     */
    private function mapRow(array $headers, array $cells): ?array
    {
        $byHeader = [];
        foreach ($headers as $i => $header) {
            $byHeader[$header] = trim((string) ($cells[$i] ?? ''));
        }

        if (implode('', $byHeader) === '') {
            return null;
        }

        return [
            'title' => $byHeader['judul'] ?? '',
            'category' => $byHeader['kategori'] ?? '',
            'excerpt' => $byHeader['ringkasan'] ?? '',
            'content' => $byHeader['konten'] ?? '',
            'status' => $byHeader['status'] ?? 'draf',
        ];
    }
}
