@props(['for'])

@error($for)
    <p {{ $attributes->merge(['class' => 'invalid-feedback d-block']) }}>{{ $message }}</p>
@enderror
