@props(['disabled' => false])

@php
$classes = 'form-control';

if ($attributes->get('type') === 'file') {
    $classes = 'form-control form-control-file';
}
@endphp

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => $classes]) !!}>
