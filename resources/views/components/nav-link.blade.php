@props(['active'])

@php
$classes = ($active ?? false)
            ? 'usn-nav-link usn-nav-link-active'
            : 'usn-nav-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
