<?php

namespace App\Services;

use App\Models\Album;
use App\Models\User;

class AlbumService
{
    public function create(array $data, User $user): Album
    {
        $data['user_id'] = $user->id;

        return Album::create($data);
    }

    public function update(Album $album, array $data): Album
    {
        $album->update($data);

        return $album;
    }

    public function delete(Album $album): void
    {
        $album->delete();
    }
}
