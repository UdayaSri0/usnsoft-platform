@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'usn-empty-state']) }}>
    <div class="usn-empty-state-icon">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 7.75A1.75 1.75 0 0 1 5.75 6h12.5A1.75 1.75 0 0 1 20 7.75v8.5A1.75 1.75 0 0 1 18.25 18H5.75A1.75 1.75 0 0 1 4 16.25v-8.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 10h8M8 14h5" />
        </svg>
    </div>

    <h3 class="mt-4 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">{{ $title }}</h3>

    @if ($description)
        <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $description }}</p>
    @endif

    @isset($actions)
        <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
            {{ $actions }}
        </div>
    @endisset
</div>
