<?php

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Enums\PostCategory;
use OpenAI;
use RuntimeException;
use Throwable;

class OpenAiPostGeneratorService implements AiClientInterface
{
    public function generate(int $count): array
    {
        $apiKey = (string) config('services.openai.key');

        if ($apiKey === '') {
            throw new RuntimeException('OPENAI_API_KEY belum diatur di file .env.');
        }

        try {
            $response = OpenAI::client($apiKey)->chat()->create([
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $this->userPrompt($count)],
                ],
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException('Permintaan ke layanan AI gagal: '.$e->getMessage(), 0, $e);
        }

        return $this->parse((string) ($response->choices[0]->message->content ?? ''), $count);
    }

    /**
     * @return array<int, GeneratedPostItem>
     */
    public function parse(string $json, int $count): array
    {
        $decoded = json_decode($json, true);

        if (! is_array($decoded) || ! isset($decoded['articles']) || ! is_array($decoded['articles'])) {
            throw new RuntimeException('Format respons AI tidak valid.');
        }

        $items = [];
        foreach (array_slice($decoded['articles'], 0, $count) as $article) {
            if (is_array($article) && trim((string) ($article['content'] ?? '')) !== '') {
                $items[] = GeneratedPostItem::fromArray($article);
            }
        }

        if ($items === []) {
            throw new RuntimeException('AI tidak menghasilkan artikel yang valid.');
        }

        return $items;
    }

    private function systemPrompt(): string
    {
        $categories = implode(', ', PostCategory::values());

        return 'Kamu adalah penulis blog berbahasa Indonesia. Balas HANYA dengan JSON object berbentuk '
            .'{"articles":[{"title":"...","category":"...","excerpt":"...","content":"..."}]}. '
            .'category harus salah satu dari: '.$categories.'. '
            .'excerpt berupa 1-2 kalimat ringkasan. content berupa artikel dalam format Markdown.';
    }

    private function userPrompt(int $count): string
    {
        return "Buat {$count} draf artikel blog yang bervariasi topiknya, masing-masing ditulis minimal 3 paragraf.";
    }
}
