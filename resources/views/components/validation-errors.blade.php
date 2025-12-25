@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'alert alert-danger']) }} role="alert">
        <h6 class="alert-heading mb-2">{{ __('Whoops! Something went wrong.') }}</h6>

        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
