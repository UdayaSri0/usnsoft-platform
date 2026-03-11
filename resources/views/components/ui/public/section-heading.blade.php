@props([
    'title' => null,
    'intro' => null,
    'eyebrow' => null,
    'align' => 'left',
    'inverted' => false,
])

@php
    $alignment = $align === 'center'
        ? 'mx-auto max-w-3xl text-center items-center'
        : 'max-w-3xl text-left';

    $eyebrowClass = $inverted
        ? 'inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/75'
        : 'usn-overline';

    $titleClass = $inverted
        ? 'font-display text-2xl font-semibold text-white sm:text-3xl'
        : 'usn-title';

    $introClass = $inverted
        ? 'text-base leading-7 text-white/75'
        : 'usn-copy';
@endphp

<div {{ $attributes->merge(['class' => "flex flex-col gap-3 {$alignment}"]) }}>
    @if ($eyebrow)
        <p class="{{ $eyebrowClass }} {{ $align === 'center' ? 'mx-auto' : '' }}">{{ $eyebrow }}</p>
    @endif

    @if ($title)
        <h2 class="{{ $titleClass }}">{{ $title }}</h2>
    @endif

    @if ($intro)
        <p class="{{ $introClass }}">{{ $intro }}</p>
    @endif
</div>
