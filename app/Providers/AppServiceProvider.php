<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\VideoProviderManager;
use Illuminate\Support\ServiceProvider;
use App\Support\ContentGeneratorManager;
use App\Contracts\VideoProviderInterface;
use App\Contracts\ContentGeneratorInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Content Generator Management
        $this->app->singleton(ContentGeneratorManager::class, fn ($app) => new ContentGeneratorManager($app));

        $this->app->bind(ContentGeneratorInterface::class, fn ($app) => $app->make(ContentGeneratorManager::class)->driver());

        // Video Provider Management
        $this->app->singleton(VideoProviderManager::class, fn ($app) => new VideoProviderManager($app));

        $this->app->bind(VideoProviderInterface::class, fn ($app) => $app->make(VideoProviderManager::class)->driver());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
