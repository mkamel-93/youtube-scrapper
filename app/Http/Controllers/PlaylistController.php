<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Services\PlaylistDiscoveryService;
use App\Http\Requests\PlaylistSearchRequest;

class PlaylistController extends Controller
{
    public function __construct(
        private readonly PlaylistDiscoveryService $playlistSearchService
    ) {}

    public function index(): View
    {
        return view('welcome');
    }

    public function start(PlaylistSearchRequest $request): JsonResponse
    {
        $categories = $request->validated('categories');

        $result = $this->playlistSearchService->searchPlaylists($categories);

        return response()->json($result);
    }
}
