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
    <body class="usn-auth-shell">
        <div class="mx-auto grid min-h-[calc(100vh-5rem)] max-w-6xl items-center gap-8 px-4 py-8 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div class="hidden rounded-3xl border border-white/50 bg-gradient-to-br from-slate-900 via-sky-900 to-cyan-800 p-8 text-white shadow-2xl lg:block">
                <a href="/" class="inline-flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 text-sm font-bold">US</span>
                    <span class="font-display text-xl font-semibold">USNsoft Platform</span>
                </a>
                <h1 class="mt-8 font-display text-3xl font-semibold leading-tight">Secure company platform and publishing workspace</h1>
                <p class="mt-4 max-w-md text-sm text-slate-200">
                    Access your account to manage requests, downloads, and internal operations with auditable workflows.
                </p>
                <ul class="mt-8 space-y-3 text-sm text-slate-200">
                    <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-cyan-300"></span>Approval-driven publishing</li>
                    <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-cyan-300"></span>Role and permission boundaries</li>
                    <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-cyan-300"></span>Security event and audit logging</li>
                </ul>
            </div>

            <div class="usn-auth-card">
                <div class="mb-6 text-center lg:hidden">
                    <a href="/" class="inline-flex items-center gap-2">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-xs font-bold text-white">US</span>
                        <span class="font-display text-lg font-semibold text-slate-900">USNsoft</span>
                    </a>
                </div>
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
