<div class="d-flex flex-column gap-2">
    <div>
        <h3 class="h5 text-body-emphasis mb-1">{{ $title }}</h3>

        <p class="text-muted small mb-0">
            {{ $description }}
        </p>
    </div>

    @isset($aside)
        <div>
            {{ $aside }}
        </div>
    @endisset
</div>
