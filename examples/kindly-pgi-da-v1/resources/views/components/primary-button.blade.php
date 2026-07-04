<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-brand']) }}>
    {{ $slot }}
</button>
