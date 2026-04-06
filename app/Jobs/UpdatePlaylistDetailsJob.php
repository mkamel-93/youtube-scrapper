<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Playlist;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdatePlaylistDetailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    /**
     * @param  array<int, array<string, mixed>>  $playlists
     */
    public function __construct(
        private readonly array $playlists
    ) {}

    public function handle(): void
    {
        Log::info('UpdatePlaylistDetailsJob: Upserting playlists', [
            'count' => count($this->playlists),
        ]);

        Playlist::upsert(
            $this->playlists,
            ['uuid'],
            [
                'title',
                'description',
                'thumbnail',
                'channel_id',
                'channel_name',
                'category',
                'lessons_count',
                'total_duration',
                'views',
                'url',
                'published_at',
            ]
        );

        Log::info('UpdatePlaylistDetailsJob: Completed');
    }
}
