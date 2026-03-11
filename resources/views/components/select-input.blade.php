@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'usn-select']) }}>
    {{ $slot }}
</select>
