<x-app-layout>
    <section class="mb-8 panel p-8 sm:p-10">
        <p class="hero-kicker">Edit Album</p>
        <h1 class="hero-title">Refine “{{ $album->title }}”.</h1>
        <p class="hero-copy">
            Update the record, refresh metadata from Last.fm if needed, and keep a lightweight audit trail of all changes.
        </p>
    </section>

    @include('albums.partials.form', [
        'album' => $album,
        'formAction' => route('albums.update', $album),
        'formMethod' => 'PUT',
        'submitLabel' => 'Update album',
        'panelTitle' => 'Editing session',
        'panelCopy' => 'Only the creator of an album can modify or remove it.',
    ])
</x-app-layout>
