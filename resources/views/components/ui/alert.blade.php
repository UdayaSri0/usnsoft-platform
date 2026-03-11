@props([
    'tone' => 'info',
    'title' => null,
])

@php
    $toneClass = match ($tone) {
        'success' => 'usn-alert-success',
        'warning' => 'usn-alert-warning',
        'danger' => 'usn-alert-danger',
        default => 'usn-alert-info',
    };
@endphp

<div {{ $attributes->merge(['class' => "usn-alert {$toneClass}"]) }} role="status">
    @if ($title)
        <p class="usn-alert-title">{{ $title }}</p>
    @endif

    @if (trim((string) $slot) !== '')
        <div class="text-sm leading-6">
            {{ $slot }}
        </div>
    @endif
</div>
