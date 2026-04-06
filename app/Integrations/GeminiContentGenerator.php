<?php

declare(strict_types=1);

namespace App\Integrations;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Contracts\ContentGeneratorInterface;

class GeminiContentGenerator extends BaseHttpClient implements ContentGeneratorInterface
{
    protected function getConfigPath(): string
    {
        return 'services.gemini';
    }

    /**
     * @param  array<int, string>  $categories
     * @return array<string, array<int, string>>
     */
    public function generateTitlesBatch(array $categories, int $countPerCategory = 2): array
    {
        if (! $this->isValidConfig()) {
            return $this->generateBatchFallback($categories, $countPerCategory);
        }

        try {
            $model = (string) ($this->config['model'] ?? 'gemini-1.5-flash');
            $prompt = $this->buildBatchPrompt($categories, $countPerCategory);

            $response = $this->api()->post("{$model}:generateContent", [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => ['response_mime_type' => 'application/json'],
            ]);

            if ($response->failed()) {
                Log::error("Gemini API Error: {$response->status()}", [
                    'body' => $response->body(),
                ]);

                return $this->generateBatchFallback($categories, $countPerCategory);
            }

            // Process successful response
            $rawJson = (string) $response->json('candidates.0.content.parts.0.text', '{}');
            /** @var array<string, array<int, string>> $decoded */
            $decoded = json_decode($rawJson, true);

            if ($this->isValidBatchResponse($decoded, $categories)) {
                return $decoded;
            }

            Log::warning('Gemini response structure invalid. Switching to fallback.');
        } catch (Exception $e) {
            Log::error("Gemini Batch Exception: {$e->getMessage()}");
        }

        return $this->generateBatchFallback($categories, $countPerCategory);
    }

    /**
     * Validates that all requested categories exist in the AI's JSON response.
     *
     * @param  array<int, string>  $categories
     */
    private function isValidBatchResponse(mixed $data, array $categories): bool
    {
        if (! is_array($data)) {
            return false;
        }

        foreach ($categories as $category) {
            if (! isset($data[$category]) || ! is_array($data[$category]) || empty($data[$category])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, string>  $categories
     */
    private function buildBatchPrompt(array $categories, int $count): string
    {
        $list = implode(', ', $categories);

        return "Generate {$count} educational course titles for each of these categories: [{$list}].
            Return the result strictly as a JSON object where keys are the category names
            and values are arrays of strings (the titles).";
    }

    /**
     * @param  array<int, string>  $categories
     * @return array<string, array<int, string>>
     */
    private function generateBatchFallback(array $categories, int $count): array
    {
        Log::debug('Gemini Batch Fallback');
        $fallback = [];

        foreach ($categories as $category) {
            $fallback[$category] = array_slice([
                "Introduction to {$category}",
                "Advanced {$category} Concepts",
                "The Complete {$category} Guide",
            ], 0, $count);
        }

        return $fallback;
    }
}
