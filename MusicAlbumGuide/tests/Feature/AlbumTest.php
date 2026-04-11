<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\AlbumLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AlbumTest extends TestCase
{
    use RefreshDatabase;

    public function test_album_index_is_publicly_available(): void
    {
        $album = Album::factory()->create([
            'title' => 'Discovery',
            'artist' => 'Daft Punk',
        ]);

        $response = $this->get(route('albums.index'));

        $response->assertOk();
        $response->assertSee('Discovery');
        $response->assertSee('Daft Punk');
        $response->assertSee($album->user->name);
    }

    public function test_authenticated_user_can_create_album_and_log_action(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('albums.store'), [
            'title' => 'Melodrama',
            'artist' => 'Lorde',
            'description' => 'A sharp and emotional pop record.',
            'cover_url' => 'https://example.com/melodrama.jpg',
        ]);

        $response->assertRedirect(route('albums.index'));

        $album = Album::where('title', 'Melodrama')->first();

        $this->assertNotNull($album);
        $this->assertSame($user->id, $album->user_id);

        $this->assertDatabaseHas('album_logs', [
            'album_id' => $album->id,
            'user_id' => $user->id,
            'action' => 'created',
        ]);
    }

    public function test_only_owner_can_edit_album(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $album = Album::factory()->for($owner)->create();

        $response = $this->actingAs($intruder)->get(route('albums.edit', $album));

        $response->assertForbidden();
    }

    public function test_owner_can_update_album_and_log_changes(): void
    {
        $user = User::factory()->create();
        $album = Album::factory()->for($user)->create([
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($user)->put(route('albums.update', $album), [
            'title' => 'Updated Title',
            'artist' => $album->artist,
            'description' => 'Updated description',
            'cover_url' => 'https://example.com/updated.jpg',
        ]);

        $response->assertRedirect(route('albums.index'));

        $this->assertDatabaseHas('albums', [
            'id' => $album->id,
            'title' => 'Updated Title',
        ]);

        $log = AlbumLog::where('album_id', $album->id)->where('action', 'updated')->first();

        $this->assertNotNull($log);
        $this->assertSame('Original Title', $log->old_data['title']);
        $this->assertSame('Updated Title', $log->new_data['title']);
    }

    public function test_owner_can_delete_album_and_log_action(): void
    {
        $user = User::factory()->create();
        $album = Album::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('albums.destroy', $album));

        $response->assertRedirect(route('albums.index'));
        $this->assertDatabaseMissing('albums', ['id' => $album->id]);
        $this->assertDatabaseHas('album_logs', [
            'user_id' => $user->id,
            'action' => 'deleted',
        ]);
    }

    public function test_prefill_returns_metadata_from_last_fm(): void
    {
        config()->set('services.lastfm.api_key', 'test-key');

        Http::fake([
            'https://ws.audioscrobbler.com/2.0/*' => Http::sequence()
                ->push([
                    'results' => [
                        'albummatches' => [
                            'album' => [
                                [
                                    'name' => 'Discovery',
                                    'artist' => 'Daft Punk',
                                    'image' => [
                                        ['#text' => 'https://example.com/search.jpg', 'size' => 'large'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ])
                ->push([
                    'album' => [
                        'name' => 'Discovery',
                        'artist' => 'Daft Punk',
                        'wiki' => [
                            'summary' => '<p>Discovery is the second studio album by Daft Punk.</p> Read more on Last.fm',
                        ],
                        'image' => [
                            ['#text' => 'https://example.com/discovery.jpg', 'size' => 'extralarge'],
                        ],
                    ],
                ]),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('albums.prefill'), [
            'title' => 'Discovery',
        ]);

        $response->assertOk()
            ->assertJson([
                'title' => 'Discovery',
                'artist' => 'Daft Punk',
                'cover_url' => 'https://example.com/discovery.jpg',
            ]);

        $this->assertStringContainsString(
            'second studio album',
            $response->json('description')
        );
    }
}
