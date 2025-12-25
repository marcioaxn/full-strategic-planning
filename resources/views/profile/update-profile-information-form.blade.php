<div>
    <x-form-section submit="updateProfileInformation">
        <x-slot name="title">
            {{ __('Profile Information') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Update your account\'s profile information and email address.') }}
        </x-slot>

        <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{photoName: null, photoPreview: null}" class="col-12">
                <input type="file" id="photo" class="d-none"
                            wire:model.live="photo"
                            x-ref="photo"
                            x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        photoPreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.photo.files[0]);
                            " />

                <label for="photo" class="form-label-profile fw-semibold">
                    <i class="bi bi-camera me-2"></i>{{ __('Profile Photo') }}
                </label>

                <div class="d-flex align-items-center gap-4 mt-3">
                    <!-- Current/Preview Photo -->
                    <div class="profile-photo-wrapper">
                        <div class="profile-photo-container" x-show="! photoPreview">
                            <img src="{{ $this->user->profile_photo_url }}"
                                 alt="{{ $this->user->name }}"
                                 class="profile-photo">
                            <div class="profile-photo-overlay">
                                <i class="bi bi-camera-fill"></i>
                            </div>
                        </div>

                        <div class="profile-photo-container" x-show="photoPreview" style="display: none;">
                            <div class="profile-photo"
                                  x-bind:style="'background-image: url(' + photoPreview + ');'">
                            </div>
                            <div class="profile-photo-overlay">
                                <i class="bi bi-camera-fill"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Photo Actions -->
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-profile-modern gradient-theme-btn btn-sm" x-on:click.prevent="$refs.photo.click()">
                            <i class="bi bi-upload me-2"></i>{{ __('Upload Photo') }}
                        </button>

                        @if ($this->user->profile_photo_path)
                            <button type="button" class="btn btn-outline-danger btn-sm" wire:click="deleteProfilePhoto">
                                <i class="bi bi-trash me-2"></i>{{ __('Remove') }}
                            </button>
                        @endif

                        <p class="small text-muted mb-0 mt-1">
                            {{ __('JPG, PNG or GIF (MAX. 1MB)') }}
                        </p>
                    </div>
                </div>

                <x-input-error for="photo" class="mt-2" />
            </div>
        @endif

        <!-- Name -->
        <div class="col-12 col-md-6">
            <label for="name" class="form-label-profile fw-semibold">
                <i class="bi bi-person me-2"></i>{{ __('Name') }}
            </label>
            <div class="input-group input-group-profile">
                <span class="input-group-text">
                    <i class="bi bi-person"></i>
                </span>
                <input id="name"
                       type="text"
                       class="form-control @error('name') is-invalid @enderror"
                       wire:model.defer="state.name"
                       required
                       autocomplete="name"
                       placeholder="{{ __('Your full name') }}">
            </div>
            <x-input-error for="name" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="col-12 col-md-6">
            <label for="email" class="form-label-profile fw-semibold">
                <i class="bi bi-envelope me-2"></i>{{ __('Email') }}
            </label>
            <div class="input-group input-group-profile">
                <span class="input-group-text">
                    <i class="bi bi-envelope"></i>
                </span>
                <input id="email"
                       type="email"
                       class="form-control @error('email') is-invalid @enderror"
                       wire:model.defer="state.email"
                       required
                       autocomplete="username"
                       placeholder="your@email.com">
            </div>
            <x-input-error for="email" class="mt-2" />

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                <div class="alert alert-warning-modern mt-3 mb-0 d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                    <div>
                        <p class="small mb-2">
                            {{ __('Your email address is unverified.') }}
                        </p>
                        <button type="button" class="btn btn-sm btn-warning" wire:click.prevent="sendEmailVerification">
                            <i class="bi bi-envelope-check me-1"></i>{{ __('Resend Verification Email') }}
                        </button>

                        @if ($this->verificationLinkSent)
                            <p class="mt-2 mb-0 small text-success fw-semibold">
                                <i class="bi bi-check-circle me-1"></i>{{ __('A new verification link has been sent!') }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            <i class="bi bi-check-circle-fill me-1"></i>{{ __('Saved successfully!') }}
        </x-action-message>

        <button type="submit" class="btn btn-primary-modern gradient-theme-btn" wire:loading.attr="disabled" wire:target="photo">
            <span wire:loading.remove wire:target="updateProfileInformation">
                <i class="bi bi-check-lg me-2"></i>{{ __('Save Changes') }}
            </span>
            <span wire:loading wire:target="updateProfileInformation">
                <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Saving...') }}
            </span>
        </button>
    </x-slot>
    </x-form-section>

    <style>
    /* Profile Form Styles */
    .form-label-profile {
        color: var(--bs-body-color);
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
    }

    [data-bs-theme="dark"] .form-label-profile {
        color: rgba(255, 255, 255, 0.9);
    }

    .input-group-profile {
        border-radius: 10px;
        overflow: hidden;
    }

    .input-group-profile .input-group-text {
        background: transparent;
        border: 2px solid var(--bs-border-color);
        border-right: none;
        color: var(--bs-secondary);
        border-radius: 10px 0 0 10px;
    }

    .input-group-profile .form-control {
        border: 2px solid var(--bs-border-color);
        border-left: none;
        padding: 0.625rem 1rem;
        border-radius: 0 10px 10px 0;
    }

    .input-group-profile .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: none;
    }

    .input-group-profile:focus-within .input-group-text {
        border-color: var(--bs-primary);
    }

    [data-bs-theme="dark"] .input-group-profile .input-group-text,
    [data-bs-theme="dark"] .input-group-profile .form-control {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.9);
    }

    [data-bs-theme="dark"] .input-group-profile .input-group-text {
        color: rgba(255, 255, 255, 0.5);
    }

    /* Profile Photo */
    .profile-photo-wrapper {
        position: relative;
    }

    .profile-photo-container {
        position: relative;
        width: 120px;
        height: 120px;
        border-radius: 16px;
        overflow: hidden;
        border: 3px solid var(--bs-border-color);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .profile-photo-container:hover {
        border-color: var(--bs-primary);
        box-shadow: 0 6px 20px rgba(var(--bs-primary-rgb), 0.3);
    }

    [data-bs-theme="dark"] .profile-photo-container {
        border-color: rgba(255, 255, 255, 0.2);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    [data-bs-theme="dark"] .profile-photo-container:hover {
        border-color: var(--bs-primary);
        box-shadow: 0 6px 20px rgba(var(--bs-primary-rgb), 0.4);
    }

    .profile-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        background-size: cover;
        background-position: center;
    }

    .profile-photo-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .profile-photo-container:hover .profile-photo-overlay {
        opacity: 1;
    }

    .profile-photo-overlay i {
        color: white;
        font-size: 1.5rem;
    }

    .btn-profile-modern {
        /* Gradient handled by global .gradient-theme-btn class */
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1.25rem;
    }

    .btn-profile-modern:hover {
        color: white;
    }

    [data-bs-theme="dark"] .btn-profile-modern {
        /* Gradient handled by global .gradient-theme-btn class */
    }

    .btn-primary-modern {
        /* Gradient handled by global .gradient-theme-btn class */
        border-radius: 10px;
        font-weight: 600;
        padding: 0.625rem 1.5rem;
    }

    .btn-primary-modern:hover {
        color: white;
    }

    [data-bs-theme="dark"] .btn-primary-modern {
        /* Gradient handled by global .gradient-theme-btn class */
    }

    .alert-warning-modern {
        background: rgba(var(--bs-warning-rgb), 0.1);
        border: 1px solid rgba(var(--bs-warning-rgb), 0.3);
        border-radius: 10px;
        padding: 1rem;
        color: var(--bs-warning);
    }

    [data-bs-theme="dark"] .alert-warning-modern {
        background: rgba(255, 193, 7, 0.15);
        border-color: rgba(255, 193, 7, 0.3);
        color: #ffc107;
    }
    </style>
</div>
