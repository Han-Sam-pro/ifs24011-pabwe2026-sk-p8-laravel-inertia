<?php

namespace Tests\Feature\Post;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class PostExcelTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create(['username' => 'penulis']);
    }

    public function test_author_can_export_only_own_posts(): void
    {
        Post::factory()->for($this->author)->create(['title' => 'Milik Saya']);
        Post::factory()->create(['title' => 'Milik Orang']);

        $response = $this->actingAs($this->author)->get('/dashboard/posts/export');

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_export_streams_xlsx_content(): void
    {
        Post::factory()->for($this->author)->create(['title' => 'Untuk Diekspor']);

        $response = $this->actingAs($this->author)->get('/dashboard/posts/export');

        ob_start();
        $response->baseResponse->sendContent();
        $content = ob_get_clean();

        $this->assertStringStartsWith('PK', $content); // berkas xlsx adalah arsip zip
    }

    public function test_import_saves_valid_rows_and_reports_invalid_ones(): void
    {
        $file = $this->makeXlsx([
            ['Judul', 'Kategori', 'Ringkasan', 'Konten', 'Status'],
            ['Artikel A', 'Teknologi', 'Ringkas', 'Isi A', 'Publik'],
            ['', 'Bisnis', '', 'Tanpa judul', 'Draf'],
            ['Artikel C', 'Kategori Salah', '', 'Isi C', 'Draf'],
            ['', '', '', '', ''],
            ['Artikel D', 'opini', '', '', ''],
        ]);

        $response = $this->actingAs($this->author)
            ->post('/dashboard/posts/import', ['file' => $file]);

        $response->assertSessionHas('success');
        $report = session('importReport');

        $this->assertSame(2, $report['imported']);
        $this->assertCount(2, $report['failed']);
        $this->assertSame(3, $report['failed'][0]['row']);
        $this->assertSame(4, $report['failed'][1]['row']);
        $this->assertDatabaseHas('posts', ['title' => 'Artikel A', 'is_published' => true]);
        $this->assertDatabaseHas('posts', ['title' => 'Artikel D', 'category' => 'Opini', 'is_published' => false]);
        $this->assertSame(2, Post::where('user_id', $this->author->id)->count());
    }

    public function test_import_without_required_headers_is_rejected(): void
    {
        $file = $this->makeXlsx([
            ['Nama', 'Lain'],
            ['x', 'y'],
        ]);

        $this->actingAs($this->author)
            ->post('/dashboard/posts/import', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertSame(0, Post::count());
    }

    public function test_import_of_empty_file_is_rejected(): void
    {
        $file = $this->makeXlsx([['Judul', 'Kategori']]);

        $this->actingAs($this->author)
            ->post('/dashboard/posts/import', ['file' => $file])
            ->assertSessionHas('error');
    }

    public function test_import_requires_file(): void
    {
        $this->actingAs($this->author)
            ->post('/dashboard/posts/import', [])
            ->assertSessionHasErrors('file');
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    private function makeXlsx(array $rows): UploadedFile
    {
        $dir = storage_path('framework/testing');
        @mkdir($dir, 0775, true);

        $path = $dir.'/impor-'.uniqid().'.xlsx';
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1');
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'impor.xlsx', null, null, true);
    }
}
