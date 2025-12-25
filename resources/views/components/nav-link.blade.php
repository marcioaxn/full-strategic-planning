@props(['active' => false])

@php
$classes = 'nav-link px-0 px-lg-2';

if ($active) {
    $classes .= ' active fw-semibold';
}
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if($active) aria-current="page" @endif>
    {{ $slot }}
</a>
