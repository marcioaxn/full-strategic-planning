@props([
    'icon' => null,
    'variant' => 'primary',
    'size' => 'sm',
    'tooltip' => null,
    'href' => null,
    'type' => 'button',
    'target' => null,
    'rel' => null,
])

@php
    $classes = collect([
        'btn',
        'btn-' . $variant,
        'btn-' . $size,
    ]);

    $iconOnly = trim($slot) === '';

    $classes->push(
        $iconOnly
            ? 'btn-icon-only d-inline-flex align-items-center justify-content-center'
            : 'd-inline-flex align-items-center gap-2'
    );

    $attributes = $attributes->class($classes->implode(' '));

    if ($tooltip) {
        $attributes = $attributes->merge([
            'data-bs-toggle' => 'tooltip',
            'data-bs-placement' => 'bottom',
            'data-bs-trigger' => 'hover focus',
            'title' => $tooltip,
        ]);
    }
@endphp

@if($href)
    <a href="{{ $href }}"
       role="button"
       @if($target) target="{{ $target }}" @endif
       @if($rel) rel="{{ $rel }}" @endif
       {{ $attributes }}>
        @if($icon)
            <i class="bi bi-{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes }}>
        @if($icon)
            <i class="bi bi-{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </button>
@endif
