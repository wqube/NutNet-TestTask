<?php

namespace App\Observers;

use App\Models\Album;
use App\Models\AlbumLog;

class AlbumObserver
{
    public function created(Album $album): void
    {
        AlbumLog::create([
            'album_id' => $album->id,
            'user_id' => $album->user_id,
            'action' => 'created',
            'old_data' => null,
            'new_data' => $album->toArray(),
        ]);
    }

    public function updated(Album $album): void
    {
        AlbumLog::create([
            'album_id' => $album->id,
            'user_id' => auth()->id(),
            'action' => 'updated',
            'old_data' => $album->getOriginal(),
            'new_data' => $album->getChanges(),
        ]);
    }

    public function deleting(Album $album): void
    {
        AlbumLog::create([
            'album_id' => $album->id,
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'old_data' => $album->toArray(),
            'new_data' => null,
        ]);
    }
}
