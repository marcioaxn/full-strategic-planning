<div {{ $attributes->merge(['class' => 'action-section-modern']) }}>
    <div class="card card-profile-modern border-0 shadow-sm">
        <div class="card-header-modern">
            <div class="section-icon section-icon-info">
                <i class="bi bi-info-circle"></i>
            </div>
            <div class="section-title-wrapper">
                <h2 class="section-title">{{ $title }}</h2>
                <p class="section-description">{{ $description }}</p>
            </div>
        </div>

        <div class="card-body p-4">
            {{ $content }}
        </div>
    </div>
</div>

<style>
    .action-section-modern {
        margin-bottom: 2rem;
    }

    .section-icon-info {
        background: linear-gradient(135deg, var(--bs-info), #0891b2) !important;
    }

    [data-bs-theme="dark"] .section-icon-info {
        background: linear-gradient(135deg, #3dd5f3, #0dcaf0) !important;
    }
</style>
