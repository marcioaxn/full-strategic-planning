<div>
    <x-action-section>
        <x-slot name="title">
            {{ __('Browser Sessions') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Manage and log out your active sessions on other browsers and devices.') }}
        </x-slot>

        <x-slot name="content">
        <div class="browser-sessions-modern">
            <p class="info-text">
                {{ __('If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.') }}
            </p>

            @if (count($this->sessions) > 0)
                <div class="sessions-list">
                    @foreach ($this->sessions as $session)
                        <div class="session-item">
                            <div class="session-icon">
                                @if ($session->agent->isDesktop())
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                    </svg>
                                @endif
                            </div>

                            <div class="session-details">
                                <div class="session-device">
                                    <strong>{{ $session->agent->platform() ? $session->agent->platform() : __('Unknown') }}</strong>
                                    <span class="device-separator">•</span>
                                    {{ $session->agent->browser() ? $session->agent->browser() : __('Unknown') }}
                                </div>

                                <div class="session-info">
                                    <span class="session-ip">
                                        <i class="bi bi-geo-alt-fill me-1"></i>{{ $session->ip_address }}
                                    </span>
                                    <span class="info-separator">•</span>
                                    @if ($session->is_current_device)
                                        <span class="current-device">
                                            <i class="bi bi-check-circle-fill me-1"></i>{{ __('This device') }}
                                        </span>
                                    @else
                                        <span class="last-active">
                                            <i class="bi bi-clock-fill me-1"></i>{{ __('Last active') }} {{ $session->last_active }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="session-actions">
                <button type="button" class="btn btn-danger-modern" wire:click="confirmLogout" wire:loading.attr="disabled">
                    <i class="bi bi-box-arrow-right me-2"></i>{{ __('Log Out Other Browser Sessions') }}
                </button>

                <x-action-message class="ms-3" on="loggedOut">
                    <i class="bi bi-check-circle-fill me-1"></i>{{ __('Done.') }}
                </x-action-message>
            </div>
        </div>

        <!-- Log Out Other Devices Confirmation Modal -->
        <x-dialog-modal wire:model.live="confirmingLogout">
            <x-slot name="title">
                {{ __('Log Out Other Browser Sessions') }}
            </x-slot>

            <x-slot name="content">
                <p>{{ __('Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices.') }}</p>

                <div class="mt-3" x-data="{}" x-on:confirming-logout-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
                    <label for="logout_password" class="form-label-profile fw-semibold">
                        <i class="bi bi-shield-lock me-2"></i>{{ __('Password') }}
                    </label>
                    <div class="input-group input-group-profile">
                        <span class="input-group-text">
                            <i class="bi bi-shield-lock"></i>
                        </span>
                        <input id="logout_password"
                               type="password"
                               class="form-control"
                               autocomplete="current-password"
                               placeholder="{{ __('Enter your password') }}"
                               x-ref="password"
                               wire:model="password"
                               wire:keydown.enter="logoutOtherBrowserSessions">
                    </div>
                    <x-input-error for="password" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <button type="button" class="btn btn-outline-secondary btn-modern" wire:click="$toggle('confirmingLogout')" wire:loading.attr="disabled">
                    <i class="bi bi-x-lg me-2"></i>{{ __('Cancel') }}
                </button>

                <button type="button" class="btn btn-danger-modern ms-2" wire:click="logoutOtherBrowserSessions" wire:loading.attr="disabled">
                    <i class="bi bi-box-arrow-right me-2"></i>{{ __('Log Out Other Sessions') }}
                </button>
            </x-slot>
        </x-dialog-modal>
        </x-slot>
    </x-action-section>

    <style>
    .browser-sessions-modern {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .info-text {
        font-size: 0.875rem;
        color: var(--bs-secondary);
        line-height: 1.6;
    }

    [data-bs-theme="dark"] .info-text {
        color: rgba(255, 255, 255, 0.7);
    }

    .sessions-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .session-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        background: rgba(var(--bs-secondary-rgb), 0.03);
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        transition: all 0.2s ease;
    }

    .session-item:hover {
        border-color: var(--bs-primary);
        box-shadow: 0 2px 8px rgba(var(--bs-primary-rgb), 0.1);
    }

    [data-bs-theme="dark"] .session-item {
        background: rgba(255, 255, 255, 0.02);
        border-color: rgba(255, 255, 255, 0.1);
    }

    [data-bs-theme="dark"] .session-item:hover {
        border-color: var(--bs-primary);
        box-shadow: 0 2px 8px rgba(var(--bs-primary-rgb), 0.2);
    }

    .session-icon {
        color: var(--bs-secondary);
        flex-shrink: 0;
    }

    [data-bs-theme="dark"] .session-icon {
        color: rgba(255, 255, 255, 0.5);
    }

    .session-details {
        flex: 1;
    }

    .session-device {
        font-size: 0.9375rem;
        color: var(--bs-body-color);
        margin-bottom: 0.375rem;
    }

    [data-bs-theme="dark"] .session-device {
        color: rgba(255, 255, 255, 0.9);
    }

    .device-separator {
        margin: 0 0.5rem;
        color: var(--bs-secondary);
    }

    .session-info {
        font-size: 0.875rem;
        color: var(--bs-secondary);
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    [data-bs-theme="dark"] .session-info {
        color: rgba(255, 255, 255, 0.6);
    }

    .session-ip,
    .last-active {
        display: flex;
        align-items: center;
    }

    .info-separator {
        margin: 0 0.5rem;
    }

    .current-device {
        color: var(--bs-success);
        font-weight: 600;
        display: flex;
        align-items: center;
    }

    [data-bs-theme="dark"] .current-device {
        color: #75b798;
    }

    .session-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    </style>
</div>
