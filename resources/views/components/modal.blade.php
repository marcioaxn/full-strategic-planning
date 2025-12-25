@props(['id', 'maxWidth'])

@php
    $id = $id ?? md5($attributes->wire('model'));

    $maxWidths = [
        'sm' => 'max-width: 24rem;',
        'md' => 'max-width: 28rem;',
        'lg' => 'max-width: 32rem;',
        'xl' => 'max-width: 36rem;',
        '2xl' => 'max-width: 42rem;',
    ];

    $dialogWidth = $maxWidths[$maxWidth ?? '2xl'] ?? $maxWidths['2xl'];
@endphp

<div
    x-data="{ show: @entangle($attributes->wire('model')) }"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    id="{{ $id }}"
    class="jetstream-modal position-fixed top-0 start-0 w-100 h-100 overflow-auto py-5 px-3"
    style="display: none; z-index: 1050;"
>
    <div x-show="show"
         class="modal-backdrop-modern position-fixed top-0 start-0 w-100 h-100"
         x-on:click="show = false"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <div x-show="show"
         class="modal-dialog-modern position-relative mx-auto overflow-hidden"
         style="{{ $dialogWidth }}"
         x-trap.inert.noscroll="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95">
        {{ $slot }}
    </div>
</div>

<style>
    .modal-backdrop-modern {
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }

    [data-bs-theme="dark"] .modal-backdrop-modern {
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
    }

    .modal-dialog-modern {
        background: var(--bs-body-bg);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        border: 1px solid var(--bs-border-color);
    }

    [data-bs-theme="dark"] .modal-dialog-modern {
        background: #1a1d29;
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
    }
</style>
