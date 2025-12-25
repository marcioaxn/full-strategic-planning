@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'card app-surface shadow-sm border-0 table-card']) }}>
    @if($title || $description || isset($actions))
        <div class="card-header bg-transparent border-bottom-0 d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
            <div>
                @if($title)
                    <h2 class="card-title h5 mb-1 text-body-emphasis">{{ $title }}</h2>
                @endif

                @if($description)
                    <p class="text-muted small mb-0">{{ $description }}</p>
                @endif
            </div>

            @isset($actions)
                <div class="d-flex flex-wrap gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    @isset($filters)
        <div class="card-body border-bottom pb-3">
            {{ $filters }}
        </div>
    @endisset

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-nowrap align-middle mb-0">
                @isset($head)
                    <thead class="table-light small text-uppercase">
                        {{ $head }}
                    </thead>
                @endisset

                <tbody>
                    {{ $slot }}
                </tbody>
            </table>
        </div>

        @isset($empty)
            <div class="p-4 text-center text-muted small border-top">
                {{ $empty }}
            </div>
        @endisset
    </div>

    @isset($footer)
        <div class="card-footer bg-body-tertiary border-0 d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
            {{ $footer }}
        </div>
    @endisset
</div>
