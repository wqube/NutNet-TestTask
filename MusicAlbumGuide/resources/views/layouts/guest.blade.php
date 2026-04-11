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
    <body class="app-body font-sans antialiased">
        <div class="auth-shell">
            <div class="app-glow app-glow-one"></div>
            <div class="app-glow app-glow-two"></div>

            <div class="auth-card">
                <a href="{{ route('albums.index') }}" class="brand-mark">
                    <span class="brand-mark__disc"></span>
                    <span class="brand-mark__text">Album Guide</span>
                </a>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
