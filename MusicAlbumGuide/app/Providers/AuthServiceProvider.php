<?php

namespace App\Providers;

use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Album::class => AlbumPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
