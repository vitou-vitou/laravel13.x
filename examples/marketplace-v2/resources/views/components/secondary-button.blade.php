<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-brand-outline disabled:opacity-50']) }}>
    {{ $slot }}
</button>
