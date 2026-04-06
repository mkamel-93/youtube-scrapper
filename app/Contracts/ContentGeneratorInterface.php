<?php

declare(strict_types=1);

namespace App\Contracts;

interface ContentGeneratorInterface
{
    /**
     * @param  array<int, string>  $categories
     * @return array<string, array<int, string>>
     */
    public function generateTitlesBatch(array $categories, int $countPerCategory = 10): array;
}
