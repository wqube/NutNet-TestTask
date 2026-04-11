<x-app-layout>
    <section class="mb-8 panel p-8 sm:p-10">
        <p class="hero-kicker">Create Album</p>
        <h1 class="hero-title">Build a new album card.</h1>
        <p class="hero-copy">
            Enter the title manually or use Last.fm autofill to pull artist, description, and cover data into the form before saving.
        </p>
    </section>

    @include('albums.partials.form', [
        'album' => null,
        'formAction' => route('albums.store'),
        'formMethod' => 'POST',
        'submitLabel' => 'Save album',
        'panelTitle' => 'New release',
        'panelCopy' => 'Use the title field as the entry point, then enrich the rest with Last.fm in one click.',
    ])
</x-app-layout>
