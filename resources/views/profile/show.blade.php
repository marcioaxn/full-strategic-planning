<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <div class="profile-header-icon gradient-theme-icon">
                <i class="bi bi-person-circle"></i>
            </div>
            <div>
                <h1 class="h3 fw-bold mb-0">{{ __('Profile Settings') }}</h1>
                <p class="text-muted small mb-0">{{ __('Manage your account settings and preferences') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="profile-page">
        <div class="container py-4">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                <div class="profile-section">
                    @livewire('profile.update-profile-information-form')
                </div>
            @endif

            <div class="profile-section">
                @livewire('profile.update-theme-color-form')
            </div>

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="profile-section">
                    @livewire('profile.update-password-form')
                </div>
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="profile-section">
                    @livewire('profile.two-factor-authentication-form')
                </div>
            @endif

            <div class="profile-section">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <div class="profile-section">
                    @livewire('profile.delete-user-form')
                </div>
            @endif
        </div>
    </div>

    <style>
        /* Profile Page Styles */
        .profile-page {
            background: var(--bs-body-bg);
            min-height: calc(100vh - 200px);
        }

        [data-bs-theme="dark"] .profile-page {
            background: var(--bs-body-bg);
        }

        .profile-header-icon {
            width: 56px;
            height: 56px;
            /* Gradient handled by global .gradient-theme-icon class */
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }

        [data-bs-theme="dark"] .profile-header-icon {
            /* Gradient handled by global .gradient-theme-icon class */
        }

        .profile-section {
            margin-bottom: 2rem;
        }

        .profile-section:last-child {
            margin-bottom: 0;
        }
    </style>
</x-app-layout>
