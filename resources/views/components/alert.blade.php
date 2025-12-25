@props([
    'variant' => 'primary',
    'dismissible' => false,
    'icon' => null,
    'heading' => null,
])

@php
    $baseClasses = 'alert d-flex gap-3 align-items-start';
    $classes = $dismissible ? $baseClasses . ' alert-dismissible fade show' : $baseClasses;
@endphp

<div {{ $attributes->merge(['class' => $classes . ' alert-' . $variant, 'role' => 'alert']) }}>
    @if ($icon)
        <i class="bi bi-{{ $icon }} fs-4 flex-shrink-0"></i>
    @endif

    <div class="flex-grow-1">
        @if ($heading)
            <h6 class="fw-semibold mb-1">{{ $heading }}</h6>
        @endif
        <div>{{ $slot }}</div>
    </div>

    @if ($dismissible)
        <button type="button"
                class="btn-close mt-1"
                data-bs-dismiss="alert"
                aria-label="{{ __('Close alert') }}">
        </button>
    @endif
</div>
