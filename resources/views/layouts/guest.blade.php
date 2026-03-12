<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'USNsoft') }}</title>

        <x-theme.head />

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|sora:500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="usn-auth-shell">
        <div class="usn-container-wide py-4 sm:py-6">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-700 hover:text-slate-950 dark:text-slate-200 dark:hover:text-white">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10.78 4.47a.75.75 0 0 1 0 1.06L7.31 9H16a.75.75 0 0 1 0 1.5H7.31l3.47 3.47a.75.75 0 1 1-1.06 1.06l-4.75-4.75a.75.75 0 0 1 0-1.06l4.75-4.75a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                    </svg>
                    <span>Back to public site</span>
                </a>

                <x-theme.toggle align="right" />
            </div>
        </div>

        <div class="usn-container-wide grid min-h-[calc(100vh-4rem)] items-center gap-8 py-4 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="hidden rounded-[2rem] border border-white/40 bg-[radial-gradient(circle_at_top_left,_rgba(103,232,249,0.22),_transparent_30%),linear-gradient(150deg,_rgba(8,47,73,0.95),_rgba(15,23,42,0.98))] p-8 text-white lg:block">
                <a href="/" class="inline-flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/15 text-sm font-bold">US</span>
                    <span class="font-display text-xl font-semibold">USNsoft Platform</span>
                </a>
                <h1 class="mt-10 font-display text-4xl font-semibold leading-tight">Secure company platform, publishing workspace, and customer access layer.</h1>
                <p class="mt-4 max-w-xl text-base leading-7 text-slate-200">
                    Sign in to manage protected requests, approval-driven publishing, and internal operations without weakening privileged boundaries.
                </p>
                <div class="mt-10 grid gap-4 sm:grid-cols-3">
                    <div class="usn-auth-note">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-200">Publishing</p>
                        <p class="mt-2 text-sm leading-6">Preview, approval, schedule, and publish flows stay auditable.</p>
                    </div>
                    <div class="usn-auth-note">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-200">Access</p>
                        <p class="mt-2 text-sm leading-6">Role and permission boundaries separate staff, editors, and public users.</p>
                    </div>
                    <div class="usn-auth-note">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-200">Security</p>
                        <p class="mt-2 text-sm leading-6">Sessions, devices, verification, and notifications are designed for long-term operations.</p>
                    </div>
                </div>
            </div>

            <div class="usn-auth-card">
                <div class="mb-8 text-center lg:hidden">
                    <a href="/" class="inline-flex items-center gap-2">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-slate-900 text-xs font-bold text-white">US</span>
                        <span class="font-display text-lg font-semibold text-slate-900 dark:text-slate-100">USNsoft</span>
                    </a>
                </div>
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
