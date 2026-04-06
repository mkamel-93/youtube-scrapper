<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Manager;
use App\Contracts\ContentGeneratorInterface;
use App\Integrations\GeminiContentGenerator;

class ContentGeneratorManager extends Manager
{
    /**
     * Get the default driver name from config.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('services.content_generator.driver');
    }

    /**
     * Create the Gemini driver instance.
     */
    public function createGeminiDriver(): ContentGeneratorInterface
    {
        return $this->container->make(GeminiContentGenerator::class);
    }
}
