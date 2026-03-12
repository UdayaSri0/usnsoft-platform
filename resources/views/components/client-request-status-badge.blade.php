@props(['status'])

@php
    $tone = $status?->badge_tone ?? $status?->system_status?->badgeTone() ?? 'muted';
    $class = match ($tone) {
        'success' => 'usn-badge-success',
        'warning' => 'usn-badge-warning',
        'danger' => 'usn-badge-danger',
        'info' => 'usn-badge-info',
        default => 'usn-badge-muted',
    };
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>
    {{ $status?->name ?? 'Unknown status' }}
</span>
