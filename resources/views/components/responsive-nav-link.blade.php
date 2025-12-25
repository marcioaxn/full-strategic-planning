@props(['active' => false])

@php
$classes = 'list-group-item list-group-item-action border-0 rounded';

if ($active) {
    $classes .= ' active';
}
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if($active) aria-current="true" @endif>
    {{ $slot }}
</a>
