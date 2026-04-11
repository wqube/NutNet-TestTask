<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\AlbumLog;
use App\Services\LastFmAlbumLookup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use RuntimeException;

class AlbumController extends Controller implements HasMiddleware
{
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

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAlbum($request);
        $data['user_id'] = $request->user()->id;

        $album = Album::create($data);

        $this->logChange($album, 'created', null, $album->fresh()->only([
            'title',
            'artist',
            'description',
            'cover_url',
            'user_id',
        ]));

        return redirect()
            ->route('albums.index')
            ->with('status', 'Album created.');
    }

    public function edit(Album $album): View
    {
        $this->ensureOwner($album);
        $album->load(['logs' => fn ($query) => $query->latest()->with('user')->limit(6)]);

        return view('albums.edit', compact('album'));
    }

    public function update(Request $request, Album $album): RedirectResponse
    {
        $this->ensureOwner($album);

        $data = $this->validateAlbum($request);
        $oldData = $album->only(['title', 'artist', 'description', 'cover_url', 'user_id']);

        $album->update($data);

        $this->logChange($album, 'updated', $oldData, $album->fresh()->only([
            'title',
            'artist',
            'description',
            'cover_url',
            'user_id',
        ]));

        return redirect()
            ->route('albums.index')
            ->with('status', 'Album updated.');
    }

    public function destroy(Album $album): RedirectResponse
    {
        $this->ensureOwner($album);

        $oldData = $album->only(['title', 'artist', 'description', 'cover_url', 'user_id']);

        $this->logChange($album, 'deleted', $oldData, null);

        $album->delete();

        return redirect()
            ->route('albums.index')
            ->with('status', 'Album deleted.');
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

    private function validateAlbum(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
        ]);
    }

    private function ensureOwner(Album $album): void
    {
        abort_unless($album->user_id === request()->user()?->id, 403);
    }

    private function logChange(Album $album, string $action, ?array $oldData, ?array $newData): void
    {
        AlbumLog::create([
            'album_id' => $album->id,
            'user_id' => request()->user()?->id,
            'action' => $action,
            'old_data' => $oldData,
            'new_data' => $newData,
        ]);
    }
}
