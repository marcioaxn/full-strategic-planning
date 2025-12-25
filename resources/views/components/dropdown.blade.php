@props([
    'align' => 'right',
    'width' => '48',
    'contentClasses' => 'py-1 bg-body',
    'dropdownClasses' => ''
])

@php
    $alignmentClasses = match ($align) {
        'left' => 'dropdown-menu-start',
        'top' => '',
        'none', 'false' => '',
        default => 'dropdown-menu-end',
    };

    $widthStyle = match ($width) {
        '48' => 'min-width: 12rem;',
        '60' => 'min-width: 15rem;',
        'auto' => '',
        default => 'min-width: ' . $width,
    };

    $contentClasses = is_array($contentClasses) ? implode(' ', $contentClasses) : $contentClasses;
    $dropdownClasses = is_array($dropdownClasses) ? implode(' ', $dropdownClasses) : $dropdownClasses;
@endphp

<div {{ $attributes->merge(['class' => 'dropdown']) }}
     x-data="{ open: false }"
     @click.outside="open = false"
     @keydown.escape.window="open = false">

    <div @click="open = !open"
         role="button"
         aria-haspopup="true"
         :aria-expanded="open.toString()">
        {{ $trigger }}
    </div>

    <div x-cloak
         x-show="open"
         x-transition:enter="fade"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="fade"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="dropdown-menu {{ trim($alignmentClasses . ' ' . $dropdownClasses) }} show end-0"
         style="display: none; {{ $widthStyle }}"
         role="menu"
         @click="open = false">
        <div class="{{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>