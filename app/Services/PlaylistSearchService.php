<?php

declare(strict_types=1);

namespace App\Services;

class PlaylistSearchService
{
    /**
     * @param  array<int, string>  $categories
     * @return array{success: bool, data: array{playlists: array<int, mixed>, categories: array<int, string>}}
     */
    public function searchPlaylists(array $categories): array
    {
        $playlists = $this->fetchMockData($categories);

        logger('Playlist search completed', [
            'categories' => $categories,
            'categories_count' => count($categories),
            'playlists_found' => count($playlists),
        ]);

        return [
            'success' => true,
            'data' => [
                'playlists' => $playlists,
                'categories' => $categories,
            ],
        ];
    }

    /**
     * @param  array<int, string>  $categories
     * @return array<int, array{
     * id: string,
     * title: string,
     * category: string,
     * lessons_count: int,
     * total_duration: string,
     * views: int,
     * instructor: string,
     * thumbnail: string,
     * url: string,
     * published_at: string
     * }>
     */
    private function fetchMockData(array $categories): array
    {
        $mockPlaylists = [];

        // A small pool of real YouTube Video IDs for variety
        $sampleVideoIds = [
            'EngW7tLk6R8', // Laravel 11 Tutorial
            'dQw4w9WgXcQ', // Never Gonna Give You Up
            'y6120QOlsfU', // PHP in 100 Seconds
            'L_jWHffIx5E', // Web Dev Roadmap
        ];

        foreach ($categories as $category) {
            // Pick a random ID from the list
            $videoId = $sampleVideoIds[array_rand($sampleVideoIds)];

            $hours = rand(2, 15);
            $minutes = rand(0, 59);

            $mockPlaylists[] = [
                'id' => uniqid('playlist_'),
                'title' => "Complete {$category} Course - Full Tutorial",
                'category' => $category,
                'lessons_count' => rand(10, 50),
                'total_duration' => "{$hours}h {$minutes}min",
                'views' => rand(1000, 100000),
                'instructor' => 'Expert Instructor',

                // Fix: Using a real YouTube thumbnail URL
                'thumbnail' => "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg",

                // Optional: Match the watch URL to the thumbnail for consistency
                'url' => "https://youtube.com/watch?v={$videoId}",
                'published_at' => now()->subDays(rand(1, 365))->toDateString(),
            ];
        }

        return $mockPlaylists;
    }
}
