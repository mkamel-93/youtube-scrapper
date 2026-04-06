<?php

declare(strict_types=1);

namespace App\Integrations;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

abstract class BaseHttpClient
{
    /** @var array<string, mixed> */
    protected array $config;

    public function __construct()
    {
        $this->config = (array) config($this->getConfigPath(), []);
    }

    abstract protected function getConfigPath(): string;

    protected function api(): PendingRequest
    {
        return Http::baseUrl($this->getBaseUrl())
            ->timeout(30)
            ->withQueryParameters($this->getDefaultQueryParams());
    }

    protected function getBaseUrl(): string
    {
        return rtrim((string) data_get($this->config, 'base_url', ''), '/');
    }

    /** @return array<string, string> */
    protected function getDefaultQueryParams(): array
    {
        return [
            'key' => (string) data_get($this->config, 'api_key', ''),
        ];
    }

    protected function isValidConfig(): bool
    {
        if (! data_get($this->config, 'api_key') || ! data_get($this->config, 'base_url')) {
            Log::error('Configuration missing for: '.static::class);

            return false;
        }

        return true;
    }
}
