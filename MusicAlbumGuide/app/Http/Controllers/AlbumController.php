<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Services\LastFmAlbumLookup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use RuntimeException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\AlbumService;
use App\Http\Requests\StoreAlbumRequest;
use App\Http\Requests\UpdateAlbumRequest;

class AlbumController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public function __construct(private AlbumService $service) {}

    public static function middleware(): array
    {
        return [
            (new Middleware('auth'))->except(['index']),
        ];
    }

    public function index(): View
    {
        $albums = Album::query()
            ->with('user')
            ->latest()
            ->paginate(9);

        return view('albums.index', compact('albums'));
    }

    public function create(): View
    {
        return view('albums.create');
    }

    public function store(StoreAlbumRequest $request): RedirectResponse
    {
        $this->service->create($request->validated(), $request->user());

        return redirect()
            ->route('albums.index')
            ->with('status', 'Альбом создан');
    }

    public function edit(Album $album): View
    {
        $this->authorize('update', $album);

        $album->load([
            'logs' => fn ($query) => $query->latest()->with('user')->limit(6)
        ]);

        return view('albums.edit', compact('album'));
    }

    public function update(UpdateAlbumRequest $request, Album $album): RedirectResponse
    {
        $this->authorize('update', $album);

        $this->service->update($album, $request->validated());

        return redirect()
            ->route('albums.index')
            ->with('status', 'Альбом обновлён');
    }

    public function destroy(Album $album): RedirectResponse
    {
        $this->authorize('delete', $album);

        $this->service->delete($album);

        return redirect()
            ->route('albums.index')
            ->with('status', 'Альбом удалён');
    }

    public function prefill(Request $request, LastFmAlbumLookup $lookup): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        try {
            $payload = $lookup->fetchByTitle($validated['title']);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 503);
        }

        if ($payload === null) {
            return response()->json([
                'message' => 'Album metadata not found. Try a more precise title.',
            ], 404);
        }

        return response()->json($payload);
    }
}
