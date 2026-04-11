<nav class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-6 sm:px-6 lg:px-8">
    <a href="{{ route('albums.index') }}" class="brand-mark">
        <span class="brand-mark__disc"></span>
        <span class="brand-mark__text">Album Guide</span>
    </a>

    <div class="flex flex-wrap items-center justify-end gap-3">
        <a href="{{ route('albums.index') }}" class="nav-chip {{ request()->routeIs('albums.index') ? 'nav-chip-active' : '' }}">
            Collection
        </a>

        @auth
            <a href="{{ route('albums.create') }}" class="nav-chip {{ request()->routeIs('albums.create') ? 'nav-chip-active' : '' }}">
                New Album
            </a>

            <a href="{{ route('profile.edit') }}" class="nav-chip {{ request()->routeIs('profile.*') ? 'nav-chip-active' : '' }}">
                Profile
            </a>

            <span class="nav-user">{{ auth()->user()->name }}</span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-secondary">
                    Log out
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="nav-chip">
                Sign in
            </a>

            <a href="{{ route('register') }}" class="btn-primary">
                Create account
            </a>
        @endauth
    </div>
</nav>
