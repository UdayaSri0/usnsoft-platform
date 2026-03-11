<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $metaTitle = $seo['meta_title'] ?? ($version->title ?? config('app.name', 'USNsoft'));
            $metaDescription = $seo['meta_description'] ?? 'USNsoft builds software, networking, and security solutions for modern teams.';
            $canonicalUrl = $seo['canonical_url'] ?? url()->current();
            $robotsIndex = array_key_exists('robots_index', $seo ?? []) ? (bool) $seo['robots_index'] : true;
            $robotsFollow = array_key_exists('robots_follow', $seo ?? []) ? (bool) $seo['robots_follow'] : true;

            if (!empty($isPreview)) {
                $robotsIndex = false;
                $robotsFollow = false;
            }
        @endphp

        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}">
        <link rel="canonical" href="{{ $canonicalUrl }}">
        <meta name="robots" content="{{ $robotsIndex ? 'index' : 'noindex' }},{{ $robotsFollow ? 'follow' : 'nofollow' }}">
        <meta property="og:title" content="{{ $seo['og_title'] ?? $metaTitle }}">
        <meta property="og:description" content="{{ $seo['og_description'] ?? $metaDescription }}">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        <meta property="og:type" content="website">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|sora:500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 text-slate-900 antialiased">
        <div class="relative min-h-screen overflow-x-hidden">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,_rgba(14,116,144,0.15),_transparent_36%),radial-gradient(circle_at_top_right,_rgba(30,64,175,0.16),_transparent_40%),linear-gradient(180deg,_#f8fafc,_#eef2ff)]"></div>

            <header class="border-b border-slate-200/80 bg-white/85 backdrop-blur-lg">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-600 to-blue-700 text-sm font-bold text-white shadow-lg">US</span>
                        <span class="font-display text-lg font-semibold tracking-tight text-slate-900">USNsoft</span>
                    </a>

                    <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 lg:flex">
                        <a href="{{ url('/') }}" class="transition hover:text-slate-900">Home</a>
                        <a href="{{ url('/about') }}" class="transition hover:text-slate-900">About</a>
                        <a href="{{ url('/services') }}" class="transition hover:text-slate-900">Services</a>
                        <a href="{{ url('/products') }}" class="transition hover:text-slate-900">Products</a>
                        <a href="{{ url('/blog') }}" class="transition hover:text-slate-900">Blog</a>
                        <a href="{{ url('/careers') }}" class="transition hover:text-slate-900">Careers</a>
                        <a href="{{ url('/contact') }}" class="transition hover:text-slate-900">Contact</a>
                    </nav>

                    <div class="flex items-center gap-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Account</a>
                            @if (auth()->user()->isInternalStaff())
                                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white shadow">Admin</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Log in</a>
                            <a href="{{ route('register') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white shadow">Get started</a>
                        @endauth
                    </div>
                </div>
            </header>

            @if (!empty($isPreview))
                <div class="border-y border-amber-300 bg-amber-50 px-4 py-2 text-center text-xs font-semibold tracking-wide text-amber-800">
                    Preview mode active. This content is not publicly published.
                </div>
            @endif

            <main>
                {{ $slot }}
            </main>

            <footer class="mt-16 border-t border-slate-200 bg-white/80">
                <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-8 text-sm text-slate-600 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <p>&copy; {{ now()->year }} USNsoft. All rights reserved.</p>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ url('/privacy-policy') }}" class="transition hover:text-slate-900">Privacy Policy</a>
                        <a href="{{ url('/terms') }}" class="transition hover:text-slate-900">Terms</a>
                        <a href="{{ url('/faq') }}" class="transition hover:text-slate-900">FAQ</a>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
