<?php

declare(strict_types=1);

namespace App\Contracts;

interface VideoProviderInterface
{
    /** @return array<int, array<string, mixed>> */
    public function searchPlaylists(string $query, int $maxResults = 2): array;
}
