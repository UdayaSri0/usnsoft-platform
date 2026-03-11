@php
    $seo = is_array($seo ?? null) ? $seo : [];
    $version = $version ?? null;

    $metaTitle = $seo['meta_title'] ?? ($version->title ?? config('app.name', 'USNsoft'));
    $metaDescription = $seo['meta_description'] ?? 'USNsoft delivers secure software, networking, and platform operations for modern organizations.';
    $canonicalUrl = $seo['canonical_url'] ?? url()->current();
    $robotsIndex = array_key_exists('robots_index', $seo) ? (bool) $seo['robots_index'] : true;
    $robotsFollow = array_key_exists('robots_follow', $seo) ? (bool) $seo['robots_follow'] : true;

    if (!empty($isPreview)) {
        $robotsIndex = false;
        $robotsFollow = false;
    }

    $navItems = [
        ['label' => 'Home', 'url' => url('/'), 'active' => request()->routeIs('home') || request()->path() === '/'],
        ['label' => 'About', 'url' => url('/about'), 'active' => request()->is('about')],
        ['label' => 'Services', 'url' => url('/services'), 'active' => request()->is('services*')],
        ['label' => 'Products', 'url' => url('/products'), 'active' => request()->is('products*')],
        ['label' => 'Blog', 'url' => url('/blog'), 'active' => request()->is('blog*') || request()->is('news*')],
        ['label' => 'Careers', 'url' => url('/careers'), 'active' => request()->is('careers*')],
        ['label' => 'Contact', 'url' => url('/contact'), 'active' => request()->is('contact*')],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}">
        <link rel="canonical" href="{{ $canonicalUrl }}">
        <meta name="robots" content="{{ $robotsIndex ? 'index' : 'noindex' }},{{ $robotsFollow ? 'follow' : 'nofollow' }}">
        <meta property="og:title" content="{{ $seo['og_title'] ?? $metaTitle }}">
        <meta property="og:description" content="{{ $seo['og_description'] ?? $metaDescription }}">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        <meta property="og:type" content="website">
        @if (! empty($seo['og_image_url']))
            <meta property="og:image" content="{{ $seo['og_image_url'] }}">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|sora:500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="relative min-h-screen overflow-x-hidden">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,_rgba(14,116,144,0.14),_transparent_32%),radial-gradient(circle_at_top_right,_rgba(37,99,235,0.12),_transparent_38%),linear-gradient(180deg,_#f8fbfd,_#eef4f8_46%,_#eef3f9)]"></div>

            <header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 backdrop-blur-xl">
                <div class="usn-container-wide flex min-h-[5rem] items-center justify-between gap-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-[linear-gradient(145deg,_#0f5f92,_#0b3b5f)] text-sm font-bold text-white shadow-lg">US</span>
                        <span>
                            <span class="block font-display text-lg font-semibold tracking-tight text-slate-950">USNsoft</span>
                            <span class="block text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Secure Platform Delivery</span>
                        </span>
                    </a>

                    <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary">
                        @foreach ($navItems as $item)
                            <a href="{{ $item['url'] }}" class="{{ $item['active'] ? 'usn-nav-link usn-nav-link-active' : 'usn-nav-link' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </nav>

                    <div class="hidden items-center gap-3 lg:flex">
                        @auth
                            <a href="{{ route('dashboard') }}" class="usn-btn-secondary">Account</a>
                            @if (auth()->user()->isInternalStaff())
                                <a href="{{ route('admin.dashboard') }}" class="usn-btn-primary">Admin</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="usn-btn-secondary">Log in</a>
                            <a href="{{ route('register') }}" class="usn-btn-primary">Get started</a>
                        @endauth
                    </div>

                    <button
                        type="button"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-300 bg-white text-slate-700 lg:hidden"
                        aria-label="Toggle navigation"
                        :aria-expanded="open.toString()"
                        @click="open = ! open"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="open ? 'M6 18L18 6M6 6l12 12' : 'M4 7h16M4 12h16M4 17h16'" />
                        </svg>
                    </button>
                </div>

                <div x-cloak x-show="open" x-transition class="border-t border-slate-200 bg-white/95 lg:hidden">
                    <div class="usn-container-wide space-y-3 py-4">
                        <nav class="space-y-1" aria-label="Mobile primary">
                            @foreach ($navItems as $item)
                                <a href="{{ $item['url'] }}" class="{{ $item['active'] ? 'usn-mobile-nav-link usn-mobile-nav-link-active' : 'usn-mobile-nav-link' }}">{{ $item['label'] }}</a>
                            @endforeach
                        </nav>

                        <div class="usn-divider"></div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            @auth
                                <a href="{{ route('dashboard') }}" class="usn-btn-secondary w-full">Account</a>
                                @if (auth()->user()->isInternalStaff())
                                    <a href="{{ route('admin.dashboard') }}" class="usn-btn-primary w-full">Admin</a>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="usn-btn-secondary w-full">Log in</a>
                                <a href="{{ route('register') }}" class="usn-btn-primary w-full">Get started</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </header>

            @if (!empty($isPreview))
                <div class="usn-container-wide pt-4">
                    <x-ui.alert tone="warning" title="Preview mode active">
                        This content is visible by preview token only and is not publicly published.
                    </x-ui.alert>
                </div>
            @endif

            <main class="relative">
                {{ $slot }}
            </main>

            <footer class="mt-16 border-t border-slate-200/80 bg-white/70 backdrop-blur">
                <div class="usn-container-wide grid gap-10 py-10 lg:grid-cols-[1.2fr_0.8fr_0.8fr]">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-950 text-sm font-bold text-white">US</span>
                            <span class="font-display text-lg font-semibold text-slate-950">USNsoft Platform</span>
                        </div>
                        <p class="max-w-xl text-sm leading-7 text-slate-600">
                            Enterprise-ready software, networking, and secure publishing workflows in one Laravel codebase.
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Navigate</p>
                        <div class="mt-4 grid gap-2 text-sm">
                            <a href="{{ url('/products') }}" class="usn-link">Products</a>
                            <a href="{{ url('/services') }}" class="usn-link">Services</a>
                            <a href="{{ url('/blog') }}" class="usn-link">Blog & News</a>
                            <a href="{{ url('/client-request') }}" class="usn-link">Client Request</a>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Policies</p>
                        <div class="mt-4 grid gap-2 text-sm">
                            <a href="{{ url('/privacy-policy') }}" class="usn-link">Privacy Policy</a>
                            <a href="{{ url('/terms') }}" class="usn-link">Terms</a>
                            <a href="{{ url('/faq') }}" class="usn-link">FAQ</a>
                            <a href="{{ url('/contact') }}" class="usn-link">Contact</a>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200/80 bg-white/60">
                    <div class="usn-container-wide flex flex-col gap-2 py-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                        <p>&copy; {{ now()->year }} USNsoft. All rights reserved.</p>
                        <p>Security-first workflows, approval boundaries, and auditable operations by design.</p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
