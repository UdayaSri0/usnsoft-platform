<button {{ $attributes->merge(['type' => 'submit', 'class' => 'usn-btn-primary']) }}>
    {{ $slot }}
</button>
