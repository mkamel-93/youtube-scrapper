<?php

declare(strict_types=1);

namespace App\Integrations;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Contracts\VideoProviderInterface;
use Illuminate\Http\Client\ConnectionException;

class YouTubeVideoProvider extends BaseHttpClient implements VideoProviderInterface
{
    protected function getConfigPath(): string
    {
        return 'services.youtube';
    }

    /**
     * Search for YouTube playlists and retrieve detailed information.
     *
     * This method queries the YouTube Search API, parses the nested 'items' array
     * to extract playlist IDs, and hydrates them with full details.
     *
     * @param  string  $query  The search term to query YouTube.
     * @param  int  $maxResults  Maximum number of playlists to fetch.
     * @return array<int, mixed>
     *
     * @link https://developers.google.com/youtube/v3/docs/search/list YouTube Search API
     *
     * @example Response Structure of $items:
     * [
     * 0 => [
     *     'kind' => 'youtube#searchResult',
     *     'etag' => 'aAGlFnHSAHn5dTd1qD_iHtpDhC8',
     *     'id' => [
     *         'kind'       => 'youtube#playlist',
     *         'playlistId' => 'PLn_bJ_IMD0_P171YEE5fesx3Urwu-Adp_',
     *     ],
     *     'snippet' => [
     *         'publishedAt' => '2024-03-16T11:31:43Z',
     *         'channelId'   => 'UC2W8nfkf_havZW5JMo3iy_w',
     *         'title'       => 'Laravel 11: Beginner\'s Guide...',
     *         'description' => 'Welcome to our Laravel 11 tutorial series...',
     *         'thumbnails'  => [
     *             'default' => ['url' => '...', 'width' => 120, 'height' => 90],
     *             'medium'  => ['url' => '...', 'width' => 320, 'height' => 180],
     *             'high'    => ['url' => '...', 'width' => 480, 'height' => 360],
     *         ],
     *         'channelTitle'         => 'DeepTechTrends',
     *         'liveBroadcastContent' => 'none',
     *         'publishTime'          => '2024-03-16T11:31:43Z',
     *         ],
     *     ],
     * ]
     */
    public function searchPlaylists(string $query, int $maxResults = 2): array
    {
        if (! $this->isValidConfig()) {
            return [];
        }

        $cacheKey = "playlists:{$query}|max_results:{$maxResults}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($query, $maxResults) {
            Log::debug("YouTube new search for query: {$query} with max results: {$maxResults}");
            try {
                $response = $this->api()->get('search', [
                    'part' => 'snippet',
                    'q' => $query,
                    'type' => 'playlist',
                    'maxResults' => $maxResults,
                ]);

                if ($response->failed()) {
                    Log::error("YouTube API Error: {$response->status()}", [
                        'body' => $response->body(),
                    ]);

                    return [];
                }

                /** @var array<int, array{id: array{playlistId?: string}}> $items */
                $items = $response->json('items', []);
                $ids = collect($items)
                    ->map(fn ($item) => data_get($item, 'id.playlistId'))
                    ->filter()
                    ->implode(',');

                return $ids ? $this->getMultiplePlaylistDetails($ids) : [];
            } catch (Exception $e) {
                Log::error("YouTube API Error: {$e->getMessage()}");

                return [];
            }
        });
    }

    /**
     * @return array<int, array{
     * uuid: mixed,
     * title: mixed,
     * description: mixed,
     * thumbnail: mixed,
     * channel_id: mixed,
     * channel_name: mixed,
     * lessons_count: mixed,
     * published_at: string
     * }>
     *
     * @throws ConnectionException
     */
    private function getMultiplePlaylistDetails(string $ids): array
    {
        $response = $this->api()->get('playlists', [
            'part' => 'snippet,contentDetails',
            'id' => $ids,
        ]);

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json('items', []);

        return collect($items)->map(function (array $playlist) {
            $snippet = $playlist['snippet'] ?? [];
            $contentDetails = $playlist['contentDetails'] ?? [];

            return [
                'uuid' => data_get($playlist, 'id'),
                'title' => data_get($snippet, 'title', 'Untitled'),
                'description' => data_get($snippet, 'description', ''),
                'thumbnail' => data_get($snippet, 'thumbnails.high.url', ''),
                'channel_id' => data_get($snippet, 'channelId', 'Unknown'),
                'channel_name' => data_get($snippet, 'channelTitle', 'Unknown'),
                'lessons_count' => data_get($contentDetails, 'itemCount', 0),
                'published_at' => ($timestamp = strtotime((string) data_get($snippet, 'publishedAt'))) !== false
                    ? date('Y-m-d', $timestamp)
                    : now()->toDateString(),
            ];
        })->toArray();
    }
}
