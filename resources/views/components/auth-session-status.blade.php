@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'usn-alert usn-alert-success text-sm font-medium']) }}>
        {{ $status }}
    </div>
@endif
