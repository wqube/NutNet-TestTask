<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Music Album Guide</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|sora:400,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <script src="//unpkg.com/alpinejs" defer></script>
    <body class="app-body font-sans antialiased">
        <div class="app-shell">
            <div class="app-glow app-glow-one"></div>
            <div class="app-glow app-glow-two"></div>

            @include('layouts.navigation')

            @isset($header)
                <header class="mx-auto w-full max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                    {{ $header }}
                </header>
            @endisset

            <main class="mx-auto w-full max-w-7xl px-4 pb-20 pt-8 sm:px-6 lg:px-8">

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
