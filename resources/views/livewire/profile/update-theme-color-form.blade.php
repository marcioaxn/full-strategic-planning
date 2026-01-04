<div>
    <x-form-section submit="updateThemeColor">
        <x-slot name="title">
            {{ __('Theme Color') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Customize the appearance of your dashboard by selecting your preferred theme color.') }}
            <span class="d-block mt-2 text-muted small">
                <i class="bi bi-info-circle me-1"></i>{{ __('Changes are saved automatically when you select a color.') }}
            </span>
        </x-slot>

        <x-slot name="form">
            <div class="col-12">
                <label class="form-label-profile fw-semibold mb-3">
                    <i class="bi bi-palette me-2"></i>{{ __('Select Your Theme Color') }}
                </label>

                <div class="theme-color-grid">
                    @foreach($availableColors as $colorKey => $colorData)
                        <div class="theme-color-option {{ $themeColor === $colorKey ? 'selected' : '' }}"
                             wire:click="$set('themeColor', '{{ $colorKey }}')">
                            <div class="d-flex align-items-center gap-3">
                                <div class="theme-color-preview-circle shadow-sm" style="background: {{ $colorData['preview'] }};">
                                    @if($themeColor === $colorKey)
                                        <i class="bi bi-check-lg"></i>
                                    @endif
                                </div>
                                <div class="theme-color-info text-start">
                                    <h6 class="theme-color-name mb-0">{{ $colorData['name'] }}</h6>
                                    <p class="theme-color-description">{{ $colorData['description'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <x-input-error for="themeColor" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="actions">
            <div class="d-flex align-items-center gap-3">
                <span wire:loading wire:target="themeColor" class="text-muted small">
                    <span class="spinner-border spinner-border-sm me-2 text-primary"></span>{{ __('Applying Theme...') }}
                </span>

                <x-action-message class="mb-0" on="saved">
                    <i class="bi bi-check-circle-fill me-1 text-success"></i>
                    <span class="text-success fw-semibold">{{ __('Theme updated!') }}</span>
                </x-action-message>
            </div>
        </x-slot>
    </x-form-section>

    <style>
        .theme-color-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.25rem;
        }

        .theme-color-option {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 16px;
            padding: 1.25rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .theme-color-option:hover {
            border-color: var(--bs-primary);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            transform: translateY(-3px);
        }

        .theme-color-option.selected {
            border-color: var(--bs-primary);
            border-width: 2px;
            background: rgba(var(--bs-primary-rgb), 0.03);
            box-shadow: 0 10px 30px rgba(var(--bs-primary-rgb), 0.1);
        }

        [data-bs-theme="dark"] .theme-color-option {
            background: #1e2227;
            border-color: rgba(255, 255, 255, 0.1);
        }

        [data-bs-theme="dark"] .theme-color-option.selected {
            background: rgba(var(--bs-primary-rgb), 0.1);
            border-color: var(--bs-primary);
        }

        .theme-color-preview-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.3s ease;
            border: 2px solid white;
        }

        [data-bs-theme="dark"] .theme-color-preview-circle {
            border-color: #2d3238;
        }

        .theme-color-preview-circle i {
            font-size: 1.25rem;
            color: white;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .theme-color-option:hover .theme-color-preview-circle {
            transform: scale(1.1) rotate(5deg);
        }

        .theme-color-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--bs-body-color);
        }

        .theme-color-description {
            font-size: 0.75rem;
            color: var(--bs-secondary-color);
            margin-bottom: 0;
            opacity: 0.8;
        }

        @media (max-width: 576px) {
            .theme-color-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</div>
