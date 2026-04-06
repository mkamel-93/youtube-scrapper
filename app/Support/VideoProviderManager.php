<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Manager;
use App\Contracts\VideoProviderInterface;
use App\Integrations\YouTubeVideoProvider;

class VideoProviderManager extends Manager
{
    /**
     * Get the default driver name from config.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('services.video_generator.driver');
    }

    /**
     * Create the YouTube driver instance.
     */
    public function createYoutubeDriver(): VideoProviderInterface
    {
        return $this->container->make(YouTubeVideoProvider::class);
    }
}
