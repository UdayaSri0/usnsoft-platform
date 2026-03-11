@props(['value'])

<label {{ $attributes->merge(['class' => 'usn-label']) }}>
    {{ $value ?? $slot }}
</label>
