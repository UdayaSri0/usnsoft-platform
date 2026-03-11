@props(['active'])

@php
$classes = ($active ?? false)
            ? 'usn-mobile-nav-link usn-mobile-nav-link-active'
            : 'usn-mobile-nav-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
