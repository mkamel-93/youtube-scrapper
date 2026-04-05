<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlaylistController;

Route::get('/', [PlaylistController::class, 'index'])->name('home');
Route::post('/playlists/start', [PlaylistController::class, 'start'])->name('playlists.start');
