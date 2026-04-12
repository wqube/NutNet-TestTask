<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class LastFmAlbumLookup
{
    private const CACHE_TTL = 3600; // 1 hour

    public function fetchByTitle(string $title): ?array
    {
        $apiKey = config('services.lastfm.api_key');

        if (blank($apiKey)) {
            throw new RuntimeException('Missing LASTFM_API_KEY configuration.');
        }

        $cacheKey = 'lastfm:album:' . md5(Str::lower($title));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($title) {

            $searchResponse = $this->request([
                'method' => 'album.search',
                'album' => $title,
                'limit' => 1,
            ]);

            $matches = data_get($searchResponse, 'results.albummatches.album', []);

            if (isset($matches['name'])) {
                $matches = [$matches];
            }

            $match = $matches[0] ?? null;

            if (! is_array($match)) {
                return null;
            }

            $artist = $match['artist'] ?? null;
            $albumTitle = $match['name'] ?? $title;

            $details = $artist
                ? $this->request([
                    'method' => 'album.getinfo',
                    'artist' => $artist,
                    'album' => $albumTitle,
                    'autocorrect' => 1,
                ])
                : null;

            $album = data_get($details, 'album', []);

            return [
                'title' => $album['name'] ?? $albumTitle,
                'artist' => $album['artist'] ?? $artist,
                'description' => $this->sanitizeDescription(
                    data_get($album, 'wiki.summary')
                ),
                'cover_url' => $this->extractImage(
                    $album['image'] ?? $match['image'] ?? []
                ),
            ];
        });
    }

    private function request(array $query): ?array
    {
        try {
            $response = Http::baseUrl(config('services.lastfm.base_url'))
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->retry(3, 300) // 🔥 было 1 → стало 3
                ->get('', array_merge($query, [
                    'api_key' => config('services.lastfm.api_key'),
                    'format' => 'json',
                ]));
        } catch (ConnectionException $e) {
            throw new RuntimeException('Last.fm is unreachable right now.', previous: $e);
        }

        if (! $response->successful()) {
            $payload = $response->json();

            $message = data_get($payload, 'message')
                ?? data_get($payload, 'error.message')
                ?? data_get($payload, 'errors.0.message');

            if (filled($message)) {
                throw new RuntimeException('Last.fm error: ' . $message);
            }

            throw new RuntimeException(
                'Last.fm request failed with status ' . $response->status() . '.'
            );
        }

        return $response->json();
    }

    private function sanitizeDescription(?string $description): ?string
    {
        if (blank($description)) {
            return null;
        }

        $description = trim(strip_tags($description));

        // убираем хвост last.fm
        $description = preg_replace(
            '/\s*Read more on Last\.fm.*$/i',
            '',
            $description
        );

        return Str::limit($description, 500);
    }

    private function extractImage(array $images): ?string
    {
        $preferredOrder = ['extralarge', 'large', 'medium', 'small'];

        foreach ($preferredOrder as $size) {
            foreach ($images as $image) {
                if (
                    ($image['size'] ?? null) === $size &&
                    filled($image['#text'] ?? null)
                ) {
                    return $image['#text'];
                }
            }
        }

        foreach ($images as $image) {
            if (filled($image['#text'] ?? null)) {
                return $image['#text'];
            }
        }

        return null;
    }
}
