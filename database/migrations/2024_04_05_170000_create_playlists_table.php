<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playlists', function (Blueprint $table) {
            $table->id();

            // YouTube Data (required)
            $table->string('uuid')->unique()->comment('YouTube playlist ID');
            $table->string('title')->comment('Youtube playlist title');
            $table->string('channel_id');
            $table->string('channel_name');
            $table->string('category');
            $table->string('url');

            // YouTube Data (nullable - may not be available)
            $table->longText('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->integer('lessons_count')->nullable();
            $table->string('total_duration')->nullable();
            $table->bigInteger('views')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index('category');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playlists');
    }
};
