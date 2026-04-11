<x-app-layout>
    <section class="grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
        <div class="panel p-8 sm:p-10">
            <p class="hero-kicker">Nutnet Test Task</p>
            <h1 class="hero-title">A curated guide to notable music albums.</h1>
            <p class="hero-copy">
                Browse the collection, open album cards with cover art from external sources, and manage your own entries after authentication.
                Album forms can enrich themselves from Last.fm directly inside the interface.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                @auth
                    <a href="{{ route('albums.create') }}" class="btn-primary">Add album</a>
                @else
                    <a href="{{ route('login') }}" class="btn-primary">Sign in to curate</a>
                    <a href="{{ route('register') }}" class="btn-secondary">Create account</a>
                @endauth
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
            <div class="stat-card">
                <p class="stat-label">Albums</p>
                <p class="stat-value">{{ $albums->total() }}</p>
                <p class="mt-3 text-sm text-stone-400">Paginated catalog with edit and delete controls for owners.</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Access</p>
                <p class="stat-value">{{ auth()->check() ? 'Authorized' : 'Guest' }}</p>
                <p class="mt-3 text-sm text-stone-400">
                    Guests can browse. Authenticated users can add, update, and remove their own albums.
                </p>
            </div>
        </div>
    </section>

    <section class="mt-8">
        @if ($albums->count() === 0)
            <div class="panel p-10 text-center">
                <h2 class="text-2xl font-bold text-white">No albums yet</h2>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-stone-400">
                    Start the catalog with the first release. The creation form can prefill artist, description, and cover art from Last.fm.
                </p>
            </div>
        @else
            <div class="record-grid">
                @foreach($albums as $album)
                    <article class="group record-card">
                        @if($album->cover_url)
                            <img src="{{ $album->cover_url }}" alt="{{ $album->title }} cover" class="record-cover">
                        @else
                            <div class="record-cover-fallback">
                                No cover
                            </div>
                        @endif

                        <div class="record-body">
                            <div>
                                <p class="text-xs uppercase tracking-[0.32em] text-stone-500">Album</p>
                                <h2 class="record-title">{{ $album->title }}</h2>
                            </div>

                            <div class="record-meta">
                                <p>{{ $album->artist ?: 'Artist not specified' }}</p>
                                <p class="mt-1 text-stone-500">
                                    Added by {{ $album->user?->name ?? 'Unknown user' }} · {{ $album->created_at?->format('d.m.Y') }}
                                </p>
                            </div>

                            <p class="record-copy">
                                {{ $album->description ?: 'No description yet.' }}
                            </p>

                            @if(auth()->id() === $album->user_id)
                                <div class="flex flex-wrap gap-3 pt-2">
                                    <a href="{{ route('albums.edit', $album) }}" class="btn-secondary">Edit</a>

                                    <form method="POST" action="{{ route('albums.destroy', $album) }}" onsubmit="return confirm('Delete this album?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Delete</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $albums->links() }}
            </div>
        @endif
    </section>
</x-app-layout>
