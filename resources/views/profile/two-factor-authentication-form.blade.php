<div>
    <x-action-section>
        <x-slot name="title">
            {{ __('Two Factor Authentication') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Add additional security to your account using two factor authentication.') }}
        </x-slot>

        <x-slot name="content">
        <div class="two-factor-modern">
            <div class="status-badge-modern mb-4">
                <div class="status-icon {{ $this->enabled ? 'status-icon-success' : 'status-icon-warning' }}">
                    <i class="bi bi-{{ $this->enabled ? 'shield-fill-check' : 'shield-exclamation' }}"></i>
                </div>
                <div>
                    <h3 class="status-title">
                        @if ($this->enabled)
                            @if ($showingConfirmation)
                                {{ __('Finish enabling two factor authentication.') }}
                            @else
                                {{ __('Two factor authentication is enabled') }}
                            @endif
                        @else
                            {{ __('Two factor authentication is not enabled') }}
                        @endif
                    </h3>
                    <p class="status-description">
                        {{ __('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
                    </p>
                </div>
            </div>

            @if ($this->enabled)
                @if ($showingQrCode)
                    <div class="qr-code-section">
                        <p class="section-info-text">
                            @if ($showingConfirmation)
                                {{ __('To finish enabling two factor authentication, scan the following QR code using your phone\'s authenticator application or enter the setup key and provide the generated OTP code.') }}
                            @else
                                {{ __('Two factor authentication is now enabled. Scan the following QR code using your phone\'s authenticator application or enter the setup key.') }}
                            @endif
                        </p>

                        <div class="qr-code-wrapper">
                            <div class="qr-code-container">
                                {!! $this->user->twoFactorQrCodeSvg() !!}
                            </div>
                        </div>

                        <div class="setup-key-modern">
                            <div class="setup-key-label">
                                <i class="bi bi-key-fill me-2"></i>{{ __('Setup Key') }}
                            </div>
                            <div class="setup-key-value">
                                {{ decrypt($this->user->two_factor_secret) }}
                            </div>
                        </div>

                        @if ($showingConfirmation)
                            <div class="verification-code-section">
                                <label for="code" class="form-label-profile fw-semibold">
                                    <i class="bi bi-123 me-2"></i>{{ __('Verification Code') }}
                                </label>
                                <div class="input-group input-group-profile">
                                    <span class="input-group-text">
                                        <i class="bi bi-123"></i>
                                    </span>
                                    <input id="code"
                                           type="text"
                                           class="form-control"
                                           inputmode="numeric"
                                           autofocus
                                           autocomplete="one-time-code"
                                           wire:model="code"
                                           wire:keydown.enter="confirmTwoFactorAuthentication"
                                           placeholder="{{ __('Enter 6-digit code') }}">
                                </div>
                                <x-input-error for="code" class="mt-2" />
                            </div>
                        @endif
                    </div>
                @endif

                @if ($showingRecoveryCodes)
                    <div class="recovery-codes-section">
                        <p class="section-info-text">
                            <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
                            {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.') }}
                        </p>

                        <div class="recovery-codes-grid">
                            @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                                <div class="recovery-code-item">
                                    <i class="bi bi-key me-2"></i>{{ $code }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            <div class="two-factor-actions">
                @if (! $this->enabled)
                    <x-confirms-password wire:then="enableTwoFactorAuthentication">
                        <button type="button" class="btn btn-success-modern" wire:loading.attr="disabled">
                            <i class="bi bi-shield-fill-check me-2"></i>{{ __('Enable 2FA') }}
                        </button>
                    </x-confirms-password>
                @else
                    @if ($showingRecoveryCodes)
                        <x-confirms-password wire:then="regenerateRecoveryCodes">
                            <button type="button" class="btn btn-outline-secondary btn-modern">
                                <i class="bi bi-arrow-clockwise me-2"></i>{{ __('Regenerate Codes') }}
                            </button>
                        </x-confirms-password>
                    @elseif ($showingConfirmation)
                        <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                            <button type="button" class="btn btn-primary-modern" wire:loading.attr="disabled">
                                <i class="bi bi-check-lg me-2"></i>{{ __('Confirm') }}
                            </button>
                        </x-confirms-password>
                    @else
                        <x-confirms-password wire:then="showRecoveryCodes">
                            <button type="button" class="btn btn-outline-secondary btn-modern">
                                <i class="bi bi-eye me-2"></i>{{ __('Show Recovery Codes') }}
                            </button>
                        </x-confirms-password>
                    @endif

                    @if ($showingConfirmation)
                        <x-confirms-password wire:then="disableTwoFactorAuthentication">
                            <button type="button" class="btn btn-outline-secondary btn-modern" wire:loading.attr="disabled">
                                <i class="bi bi-x-lg me-2"></i>{{ __('Cancel') }}
                            </button>
                        </x-confirms-password>
                    @else
                        <x-confirms-password wire:then="disableTwoFactorAuthentication">
                            <button type="button" class="btn btn-danger-modern" wire:loading.attr="disabled">
                                <i class="bi bi-shield-x me-2"></i>{{ __('Disable 2FA') }}
                            </button>
                        </x-confirms-password>
                    @endif
                @endif
            </div>
        </div>
        </x-slot>
    </x-action-section>

    <style>
    .two-factor-modern {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .status-badge-modern {
        display: flex;
        align-items: start;
        gap: 1rem;
        padding: 1.25rem;
        background: rgba(var(--bs-secondary-rgb), 0.03);
        border-radius: 12px;
        border: 1px solid var(--bs-border-color);
    }

    [data-bs-theme="dark"] .status-badge-modern {
        background: rgba(255, 255, 255, 0.02);
        border-color: rgba(255, 255, 255, 0.1);
    }

    .status-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .status-icon-success {
        background: rgba(var(--bs-success-rgb), 0.1);
        color: var(--bs-success);
    }

    .status-icon-warning {
        background: rgba(var(--bs-warning-rgb), 0.1);
        color: var(--bs-warning);
    }

    [data-bs-theme="dark"] .status-icon-success {
        background: rgba(25, 135, 84, 0.15);
        color: #75b798;
    }

    [data-bs-theme="dark"] .status-icon-warning {
        background: rgba(255, 193, 7, 0.15);
        color: #ffc107;
    }

    .status-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--bs-body-color);
        margin-bottom: 0.5rem;
    }

    [data-bs-theme="dark"] .status-title {
        color: rgba(255, 255, 255, 0.95);
    }

    .status-description {
        font-size: 0.875rem;
        color: var(--bs-secondary);
        margin-bottom: 0;
    }

    [data-bs-theme="dark"] .status-description {
        color: rgba(255, 255, 255, 0.6);
    }

    .qr-code-section,
    .recovery-codes-section {
        padding: 1.25rem;
        background: rgba(var(--bs-info-rgb), 0.03);
        border: 1px solid rgba(var(--bs-info-rgb), 0.2);
        border-radius: 12px;
    }

    [data-bs-theme="dark"] .qr-code-section,
    [data-bs-theme="dark"] .recovery-codes-section {
        background: rgba(13, 110, 253, 0.05);
        border-color: rgba(13, 110, 253, 0.2);
    }

    .section-info-text {
        font-size: 0.875rem;
        color: var(--bs-body-color);
        margin-bottom: 1.25rem;
    }

    [data-bs-theme="dark"] .section-info-text {
        color: rgba(255, 255, 255, 0.85);
    }

    .qr-code-wrapper {
        display: flex;
        justify-content: center;
        margin: 1.5rem 0;
    }

    .qr-code-container {
        padding: 1.5rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .setup-key-modern {
        background: rgba(var(--bs-secondary-rgb), 0.05);
        border: 1px solid var(--bs-border-color);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1.25rem;
    }

    [data-bs-theme="dark"] .setup-key-modern {
        background: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.1);
    }

    .setup-key-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--bs-body-color);
        margin-bottom: 0.5rem;
    }

    [data-bs-theme="dark"] .setup-key-label {
        color: rgba(255, 255, 255, 0.9);
    }

    .setup-key-value {
        font-family: 'Courier New', monospace;
        font-size: 1rem;
        font-weight: 600;
        color: var(--bs-primary);
        word-break: break-all;
    }

    .verification-code-section {
        max-width: 24rem;
    }

    .recovery-codes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.75rem;
    }

    .recovery-code-item {
        background: rgba(var(--bs-secondary-rgb), 0.05);
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-family: 'Courier New', monospace;
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--bs-body-color);
        display: flex;
        align-items: center;
    }

    [data-bs-theme="dark"] .recovery-code-item {
        background: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.9);
    }

    .two-factor-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .btn-modern {
        border-radius: 10px;
        font-weight: 500;
        padding: 0.625rem 1.25rem;
    }

    .btn-success-modern {
        background: linear-gradient(135deg, var(--bs-success), #198754);
        border: none;
        color: white;
        border-radius: 10px;
        font-weight: 600;
        padding: 0.625rem 1.5rem;
        box-shadow: 0 2px 8px rgba(var(--bs-success-rgb), 0.25);
        transition: all 0.2s ease;
    }

    .btn-success-modern:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(var(--bs-success-rgb), 0.35);
        color: white;
    }

    [data-bs-theme="dark"] .btn-success-modern {
        background: linear-gradient(135deg, #20c997, #198754);
    }

    .btn-danger-modern {
        background: linear-gradient(135deg, var(--bs-danger), #dc3545);
        border: none;
        color: white;
        border-radius: 10px;
        font-weight: 600;
        padding: 0.625rem 1.5rem;
        box-shadow: 0 2px 8px rgba(var(--bs-danger-rgb), 0.25);
        transition: all 0.2s ease;
    }

    .btn-danger-modern:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(var(--bs-danger-rgb), 0.35);
        color: white;
    }

    [data-bs-theme="dark"] .btn-danger-modern {
        background: linear-gradient(135deg, #ea868f, #dc3545);
    }
    </style>
</div>
