@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-xl bg-slate-900 px-3 py-2 text-start text-sm font-semibold text-white'
            : 'block w-full rounded-xl px-3 py-2 text-start text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
