<?php

namespace Tests\Unit\Ai;

use App\Features\Ai\Services\OpenAiPostGeneratorService;
use RuntimeException;
use Tests\TestCase;

class OpenAiPostGeneratorServiceTest extends TestCase
{
    public function test_parse_maps_articles_and_respects_count(): void
    {
        $json = json_encode(['articles' => [
            ['title' => 'Satu', 'category' => 'Bisnis', 'excerpt' => 'x', 'content' => 'Isi satu'],
            ['title' => 'Dua', 'category' => 'Salah', 'excerpt' => 'y', 'content' => 'Isi dua'],
            ['title' => 'Tiga', 'category' => 'Opini', 'excerpt' => 'z', 'content' => 'Isi tiga'],
        ]]);

        $items = (new OpenAiPostGeneratorService)->parse($json, 2);

        $this->assertCount(2, $items);
        $this->assertSame('Bisnis', $items[0]->category);
        $this->assertSame('Umum', $items[1]->category);
    }

    public function test_parse_skips_empty_content_and_fails_when_nothing_valid(): void
    {
        $this->expectException(RuntimeException::class);

        (new OpenAiPostGeneratorService)->parse(
            json_encode(['articles' => [['title' => 'Kosong', 'content' => '  ']]]),
            1,
        );
    }

    public function test_parse_rejects_invalid_json_shape(): void
    {
        $this->expectException(RuntimeException::class);

        (new OpenAiPostGeneratorService)->parse('bukan json', 1);
    }

    public function test_generate_requires_api_key(): void
    {
        config(['services.openai.key' => '']);

        $this->expectException(RuntimeException::class);

        (new OpenAiPostGeneratorService)->generate(1);
    }
}
