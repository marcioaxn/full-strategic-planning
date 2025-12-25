@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'autohide' => true,
    'delay' => 5000,
    'closeLabel' => null,
])

@php
    $closeLabel = $closeLabel ?? __('Close notification');
@endphp

<div {{ $attributes->merge([
        'class' => 'toast',
        'role' => 'status',
        'aria-live' => 'polite',
        'aria-atomic' => 'true',
        'data-bs-autohide' => $autohide ? 'true' : 'false',
        'data-bs-delay' => (int) $delay,
    ]) }}>
    <div class="toast-header">
        @if ($icon)
            <i class="bi bi-{{ $icon }} text-primary me-2"></i>
        @endif
        <strong class="me-auto">{{ $title ?? __('Notification') }}</strong>
        @if ($subtitle)
            <small class="text-muted">{{ $subtitle }}</small>
        @endif
        <button type="button"
                class="btn-close ms-2"
                data-bs-dismiss="toast"
                aria-label="{{ $closeLabel }}"></button>
    </div>
    <div class="toast-body">
        {{ $slot }}
    </div>
</div>
