<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ops-btn-primary']) }}>
    {{ $slot }}
</button>
