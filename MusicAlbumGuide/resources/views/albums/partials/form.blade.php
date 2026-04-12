@php
    $isEditing = filled($album);
    $recentLogs = $isEditing ? $album->logs : collect();
@endphp

<section class="form-shell">
    <div class="panel p-8">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="hero-kicker">{{ $panelTitle }}</p>
                <p class="mt-3 max-w-xl text-sm leading-7 text-stone-400">{{ $panelCopy }}</p>
            </div>

            <a href="{{ route('albums.index') }}" class="btn-secondary">Back to collection</a>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-[1.5rem] border border-rose-400/20 bg-rose-500/10 px-5 py-4 text-sm text-rose-100">
                Please correct the highlighted fields and try again.
            </div>
        @endif

        <form method="POST" action="{{ $formAction }}" class="space-y-6" id="album-form">
            @csrf
            @if ($formMethod !== 'POST')
                @method($formMethod)
            @endif

            <div class="grid gap-6 md:grid-cols-[1fr_auto]">
                <div>
                    <label for="title" class="field-label">Album title</label>
                    <input
                        id="title"
                        name="title"
                        type="text"
                        value="{{ old('title', $album?->title) }}"
                        class="field-input"
                        placeholder="Random Access Memories"
                        required
                    >
                    @error('title')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:pt-8">
                    <button type="button" class="btn-secondary w-full sm:w-auto" id="prefill-button">
                        Autofill from Last.fm
                    </button>
                </div>
            </div>

            <div id="prefill-status" class="hidden rounded-[1.5rem] border px-4 py-3 text-sm"></div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label for="artist" class="field-label">Artist</label>
                    <input
                        id="artist"
                        name="artist"
                        type="text"
                        value="{{ old('artist', $album?->artist) }}"
                        class="field-input"
                        placeholder="Daft Punk"
                    >
                    @error('artist')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cover_url" class="field-label">Cover URL</label>
                    <input
                        id="cover_url"
                        name="cover_url"
                        type="url"
                        value="{{ old('cover_url', $album?->cover_url) }}"
                        class="field-input"
                        placeholder="https://..."
                    >
                    @error('cover_url')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="field-label">Description</label>
                <textarea
                    id="description"
                    name="description"
                    class="field-input field-textarea"
                    placeholder="Short context about the album, its era, sound, or impact."
                >{{ old('description', $album?->description) }}</textarea>
                @error('description')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="btn-primary">{{ $submitLabel }}</button>
                @if ($isEditing)
                    <a href="{{ route('albums.index') }}" class="btn-secondary">Cancel</a>
                @endif
            </div>
        </form>
    </div>

    <aside class="space-y-6">
        <div class="panel p-6">
            <p class="hero-kicker">Live Preview</p>
            <div class="mt-5 overflow-hidden rounded-[1.75rem] border border-white/10 bg-black/20">
                <img
                    id="cover-preview"
                    src="{{ old('cover_url', $album?->cover_url) }}"
                    alt="Album cover preview"
                    class="record-cover {{ old('cover_url', $album?->cover_url) ? '' : 'hidden' }}"
                >
                <div id="cover-placeholder" class="record-cover-fallback {{ old('cover_url', $album?->cover_url) ? 'hidden' : '' }}">
                    Cover preview
                </div>
            </div>

            <div class="mt-5">
                <h2 id="preview-title" class="text-2xl font-bold text-white">
                    {{ old('title', $album?->title ?: 'Untitled album') }}
                </h2>
                <p id="preview-artist" class="mt-2 text-sm text-stone-300">
                    {{ old('artist', $album?->artist ?: 'Artist will appear here') }}
                </p>
                <p id="preview-description" class="mt-4 text-sm leading-7 text-stone-400">
                    {{ old('description', $album?->description ?: 'Description preview updates while you type.') }}
                </p>
            </div>
        </div>

        <div class="panel p-6">
            <p class="hero-kicker">Metadata</p>
            <p class="mt-4 muted-note">
                Last.fm autofill requires a valid <code>LASTFM_API_KEY</code> in the project environment. If the service is unavailable,
                you can still complete the form manually.
            </p>
        </div>

        @if ($isEditing)
            <div class="panel p-6">
                <p class="hero-kicker">Change Log</p>

                <div class="mt-5 space-y-4">
                    @forelse ($recentLogs as $log)
                        <div class="log-item">
                            <p class="log-action">{{ $log->action }}</p>
                            <p class="log-meta">
                                {{ $log->created_at?->format('d.m.Y H:i') }} · {{ $log->user?->name ?? 'System' }}
                            </p>
                        </div>
                    @empty
                        <p class="muted-note">No changes have been logged for this album yet.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </aside>
</section>

<script>
    (() => {
        const title = document.getElementById('title');
        const artist = document.getElementById('artist');
        const description = document.getElementById('description');
        const coverUrl = document.getElementById('cover_url');
        const previewTitle = document.getElementById('preview-title');
        const previewArtist = document.getElementById('preview-artist');
        const previewDescription = document.getElementById('preview-description');
        const coverPreview = document.getElementById('cover-preview');
        const coverPlaceholder = document.getElementById('cover-placeholder');
        const prefillButton = document.getElementById('prefill-button');
        const statusBox = document.getElementById('prefill-status');

        const showStatus = (message, tone) => {
            statusBox.textContent = message;
            statusBox.className = 'rounded-[1.5rem] border px-4 py-3 text-sm';
            statusBox.classList.remove('hidden');

            if (tone === 'error') {
                statusBox.classList.add('border-rose-400/20', 'bg-rose-500/10', 'text-rose-100');
                return;
            }

            statusBox.classList.add('border-emerald-400/20', 'bg-emerald-500/10', 'text-emerald-100');
        };

        const syncPreview = () => {
            previewTitle.textContent = title.value.trim() || 'Untitled album';
            previewArtist.textContent = artist.value.trim() || 'Artist will appear here';
            previewDescription.textContent = description.value.trim() || 'Description preview updates while you type.';

            const url = coverUrl.value.trim();

            if (url) {
                coverPreview.src = url;
                coverPreview.classList.remove('hidden');
                coverPlaceholder.classList.add('hidden');
            } else {
                coverPreview.classList.add('hidden');
                coverPlaceholder.classList.remove('hidden');
            }
        };

        [title, artist, description, coverUrl].forEach((field) => {
            field.addEventListener('input', syncPreview);
        });

        coverPreview.addEventListener('error', () => {
            coverPreview.classList.add('hidden');
            coverPlaceholder.classList.remove('hidden');
        });

        prefillButton.addEventListener('click', async () => {
            if (!title.value.trim()) {
                showStatus('Enter an album title first.', 'error');
                return;
            }

            prefillButton.disabled = true;
            prefillButton.textContent = 'Fetching...';

            try {
                const response = await fetch('{{ route('albums.prefill') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ title: title.value.trim() }),
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'Unable to fetch metadata.');
                }

                title.value = payload.title || title.value;
                artist.value = payload.artist || artist.value;
                description.value = payload.description || description.value;
                coverUrl.value = payload.cover_url || coverUrl.value;

                syncPreview();
                showStatus('Metadata loaded from Last.fm.', 'success');
            } catch (error) {
                showStatus(error.message, 'error');
            } finally {
                prefillButton.disabled = false;
                prefillButton.textContent = 'Autofill from Last.fm';
            }
        });

        syncPreview();
    })();
</script>
