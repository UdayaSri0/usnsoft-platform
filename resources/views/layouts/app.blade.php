<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'USNsoft') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|sora:500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="relative min-h-screen overflow-x-hidden">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,_rgba(14,116,144,0.08),_transparent_28%),linear-gradient(180deg,_#f8fafc,_#eef4f8_42%,_#edf2f8)]"></div>
            @include('layouts.navigation')

            @isset($header)
                <header class="border-b border-slate-200/80 bg-white/70 backdrop-blur-xl">
                    <div class="usn-container-wide py-6">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="pb-16">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
