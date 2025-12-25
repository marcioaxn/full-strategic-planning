@props(['submit'])

<div {{ $attributes->merge(['class' => 'form-section-modern']) }}>
    <div class="card card-profile-modern border-0 shadow-sm">
        <div class="card-header-modern">
            <div class="section-icon gradient-theme-icon">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div class="section-title-wrapper">
                <h2 class="section-title">{{ $title }}</h2>
                <p class="section-description">{{ $description }}</p>
            </div>
        </div>

        <form wire:submit.prevent="{{ $submit }}">
            <div class="card-body p-4">
                <div class="row g-4">
                    {{ $form }}
                </div>
            </div>

            @isset($actions)
                <div class="card-footer-modern">
                    {{ $actions }}
                </div>
            @endisset
        </form>
    </div>
</div>

<style>
    .form-section-modern {
        margin-bottom: 2rem;
    }

    .card-profile-modern {
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    [data-bs-theme="dark"] .card-profile-modern {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .card-profile-modern:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
    }

    [data-bs-theme="dark"] .card-profile-modern:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4) !important;
    }

    .card-header-modern {
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.05), rgba(var(--bs-primary-rgb), 0.02));
        border-bottom: 1px solid var(--bs-border-color);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    [data-bs-theme="dark"] .card-header-modern {
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.1), rgba(var(--bs-primary-rgb), 0.05));
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    .section-icon {
        width: 48px;
        height: 48px;
        /* Gradient handled by global .gradient-theme-icon class */
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    [data-bs-theme="dark"] .section-icon {
        /* Gradient handled by global .gradient-theme-icon class */
    }

    .section-title-wrapper {
        flex: 1;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--bs-body-color);
        margin-bottom: 0.25rem;
    }

    [data-bs-theme="dark"] .section-title {
        color: rgba(255, 255, 255, 0.95);
    }

    .section-description {
        font-size: 0.875rem;
        color: var(--bs-secondary);
        margin-bottom: 0;
    }

    [data-bs-theme="dark"] .section-description {
        color: rgba(255, 255, 255, 0.6);
    }

    .card-footer-modern {
        background: rgba(var(--bs-secondary-rgb), 0.03);
        border-top: 1px solid var(--bs-border-color);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 1rem;
    }

    [data-bs-theme="dark"] .card-footer-modern {
        background: rgba(255, 255, 255, 0.02);
        border-top-color: rgba(255, 255, 255, 0.1);
    }
</style>
