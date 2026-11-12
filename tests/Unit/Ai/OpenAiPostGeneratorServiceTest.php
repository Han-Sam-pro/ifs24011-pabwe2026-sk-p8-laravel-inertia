<?php

namespace Tests\Unit\Ai;

use App\Features\Ai\Services\OpenAiPostGeneratorService;
use Mockery;
use OpenAI\Contracts\ClientContract;
use OpenAI\Contracts\Resources\ChatContract;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Meta\MetaInformation;
use RuntimeException;
use Tests\TestCase;

class OpenAiPostGeneratorServiceTest extends TestCase
{
    public function test_generate_sends_prompt_and_parses_openai_response(): void
    {
        config(['services.openai.key' => 'sk-test']);

        $json = json_encode(['articles' => [
            ['title' => 'Satu', 'category' => 'Teknologi', 'excerpt' => 'x', 'content' => 'Isi satu'],
        ]]);

        $chat = Mockery::mock(ChatContract::class);
        $chat->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (array $params) => str_contains($params['messages'][1]['content'], 'Buat 2 draf')))
            ->andReturn($this->chatResponse($json));

        $client = Mockery::mock(ClientContract::class);
        $client->shouldReceive('chat')->once()->andReturn($chat);

        $items = (new OpenAiPostGeneratorService($client))->generate(2);

        $this->assertCount(1, $items);
        $this->assertSame('Teknologi', $items[0]->category);
    }

    public function test_generate_wraps_provider_errors(): void
    {
        config(['services.openai.key' => 'sk-test']);

        $chat = Mockery::mock(ChatContract::class);
        $chat->shouldReceive('create')->once()->andThrow(new RuntimeException('koneksi putus'));

        $client = Mockery::mock(ClientContract::class);
        $client->shouldReceive('chat')->once()->andReturn($chat);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Permintaan ke layanan AI gagal: koneksi putus');

        (new OpenAiPostGeneratorService($client))->generate(1);
    }

    private function chatResponse(string $content): CreateResponse
    {
        return CreateResponse::from([
            'id' => 'chatcmpl-test',
            'object' => 'chat.completion',
            'created' => 1,
            'model' => 'gpt-4o-mini',
            'choices' => [[
                'index' => 0,
                'message' => ['role' => 'assistant', 'content' => $content],
                'logprobs' => null,
                'finish_reason' => 'stop',
            ]],
            'usage' => ['prompt_tokens' => 1, 'completion_tokens' => 1, 'total_tokens' => 2],
        ], MetaInformation::from([]));
    }

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
