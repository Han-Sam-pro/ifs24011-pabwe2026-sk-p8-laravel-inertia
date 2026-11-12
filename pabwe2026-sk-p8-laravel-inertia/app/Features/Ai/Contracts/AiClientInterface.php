<?php

namespace App\Features\Ai\Contracts;

use App\Features\Ai\DTO\GeneratedPostItem;

interface AiClientInterface
{
    /**
     * Minta AI membuat $count draf artikel.
     *
     * @return array<int, GeneratedPostItem>
     *
     * @throws \RuntimeException jika respons AI gagal diproses.
     */
    public function generate(int $count): array;
}
