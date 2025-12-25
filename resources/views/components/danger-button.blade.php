<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-danger d-inline-flex align-items-center gap-2']) }}>
    {{ $slot }}
</button>
