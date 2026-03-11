<button {{ $attributes->merge(['type' => 'submit', 'class' => 'usn-btn-danger']) }}>
    {{ $slot }}
</button>
