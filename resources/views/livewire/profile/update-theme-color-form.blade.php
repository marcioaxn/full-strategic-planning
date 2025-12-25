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
                            <div class="theme-color-preview" style="background: {{ $colorData['preview'] }};">
                                @if($themeColor === $colorKey)
                                    <i class="bi bi-check-circle-fill"></i>
                                @endif
                            </div>
                            <div class="theme-color-info">
                                <h6 class="theme-color-name">{{ $colorData['name'] }}</h6>
                                <p class="theme-color-description">{{ $colorData['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <x-input-error for="themeColor" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="actions">
            <div class="d-flex align-items-center gap-3">
                <span wire:loading wire:target="updateThemeColor" class="text-muted small">
                    <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Saving...') }}
                </span>

                <x-action-message class="mb-0" on="saved">
                    <i class="bi bi-check-circle-fill me-1 text-success"></i>
                    <span class="text-success fw-semibold">{{ __('Theme updated automatically!') }}</span>
                </x-action-message>
            </div>
        </x-slot>
    </x-form-section>

    <style>
        .theme-color-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .theme-color-option {
            background: rgba(var(--bs-secondary-rgb), 0.03);
            border: 2px solid var(--bs-border-color);
            border-radius: 14px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .theme-color-option:hover {
            border-color: var(--bs-primary);
            box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.15);
            transform: translateY(-2px);
        }

        .theme-color-option.selected {
            border-color: var(--bs-success);
            background: rgba(var(--bs-success-rgb), 0.05);
            box-shadow: 0 4px 12px rgba(var(--bs-success-rgb), 0.2);
        }

        [data-bs-theme="dark"] .theme-color-option {
            background: rgba(255, 255, 255, 0.02);
            border-color: rgba(255, 255, 255, 0.1);
        }

        [data-bs-theme="dark"] .theme-color-option:hover {
            border-color: var(--bs-primary);
            box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.25);
        }

        [data-bs-theme="dark"] .theme-color-option.selected {
            border-color: #75b798;
            background: rgba(25, 135, 84, 0.1);
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
        }

        .theme-color-preview {
            width: 100%;
            height: 80px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
        }

        .theme-color-preview i {
            font-size: 2rem;
            color: white;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        .theme-color-option:hover .theme-color-preview {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .theme-color-info {
            text-align: center;
        }

        .theme-color-name {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--bs-body-color);
            margin-bottom: 0.25rem;
        }

        [data-bs-theme="dark"] .theme-color-name {
            color: rgba(255, 255, 255, 0.95);
        }

        .theme-color-description {
            font-size: 0.75rem;
            color: var(--bs-secondary);
            margin-bottom: 0;
        }

        [data-bs-theme="dark"] .theme-color-description {
            color: rgba(255, 255, 255, 0.6);
        }

        @media (max-width: 576px) {
            .theme-color-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</div>
