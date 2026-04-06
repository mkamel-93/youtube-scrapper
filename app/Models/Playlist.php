<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
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
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'lessons_count' => 'integer',
        'views' => 'integer',
        'published_at' => 'datetime',
    ];
}
