<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ops-btn bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-500']) }}>
    {{ $slot }}
</button>
