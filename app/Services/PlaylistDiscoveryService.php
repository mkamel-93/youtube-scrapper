<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\UpdatePlaylistDetailsJob;
use App\Contracts\VideoProviderInterface;
use App\Contracts\ContentGeneratorInterface;

class PlaylistDiscoveryService
{
    private const YOUTUBE_PLAYLIST_URL = 'https://www.youtube.com/playlist?list=';

    public function __construct(
        private readonly ContentGeneratorInterface $generator,
        private readonly VideoProviderInterface $videoProvider
    ) {}

    /**
     * @param  array<int, string>  $categories
     * @return array{success: bool, data: array{playlists: array<int, mixed>, categories: array<int, string>, titles: array<string, array<int, string>>}}
     */
    public function searchPlaylists(array $categories): array
    {
        $titlesMap = $this->generator->generateTitlesBatch($categories, 10);

        $discoveredPlaylists = $this->discoverPlaylistsFromTitles($titlesMap);

        if (! empty($discoveredPlaylists)) {
            UpdatePlaylistDetailsJob::dispatch($discoveredPlaylists);
        }

        return [
            'success' => true,
            'data' => [
                'playlists' => $discoveredPlaylists,
                'categories' => $categories,
                'titles' => $titlesMap,
            ],
        ];
    }

    /**
     * Discover playlists from AI-generated titles.
     *
     * @param  array<string, array<int, string>>  $titlesMap
     * @return array<int, array<string, mixed>>
     */
    private function discoverPlaylistsFromTitles(array $titlesMap): array
    {
        $playlists = [];
        $seenUuids = [];

        foreach ($titlesMap as $category => $titles) {
            foreach ($titles as $title) {
                $searchResults = $this->videoProvider->searchPlaylists($title, 2);

                foreach ($searchResults as $playlist) {
                    if (in_array($playlist['uuid'], $seenUuids, true)) {
                        continue;
                    }

                    $seenUuids[] = $playlist['uuid'];
                    $playlists[] = $this->transformPlaylist($playlist, $category);
                }
            }
        }

        return $playlists;
    }

    /**
     * Enrich playlist data with category and computed fields.
     *
     * @param  array<string, mixed>  $playlist
     * @return array<string, mixed>
     */
    private function transformPlaylist(array $playlist, string $category): array
    {
        $lessonCount = (int) data_get($playlist, 'lessons_count', 0);
        $uuid = (string) data_get($playlist, 'uuid');

        return [
            'uuid' => $uuid,
            'category' => $category,
            'title' => data_get($playlist, 'title'),
            'description' => data_get($playlist, 'description'),
            'lessons_count' => $lessonCount,
            'total_duration' => $this->estimateDuration($lessonCount),
            'views' => data_get($playlist, 'views'),
            'channel_id' => data_get($playlist, 'channel_id'),
            'channel_name' => data_get($playlist, 'channel_name'),
            'thumbnail' => data_get($playlist, 'thumbnail'),
            'url' => self::YOUTUBE_PLAYLIST_URL.$uuid,
            'published_at' => data_get($playlist, 'published_at'),
        ];
    }

    /**
     * Estimate total duration based on lesson count.
     * Assumes average 10 minutes per video.
     */
    private function estimateDuration(int $lessonCount): string
    {
        $totalMinutes = $lessonCount * 10;
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return "{$hours}h {$minutes}min";
    }
}
