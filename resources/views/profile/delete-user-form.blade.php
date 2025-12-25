<div>
    <x-action-section>
        <x-slot name="title">
            {{ __('Delete Account') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Permanently delete your account.') }}
        </x-slot>

        <x-slot name="content">
        <div class="delete-account-modern">
            <div class="danger-zone">
                <div class="danger-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <h4 class="danger-title">{{ __('Danger Zone') }}</h4>
                    <p class="danger-description">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
                    </p>
                </div>
            </div>

            <button type="button" class="btn btn-danger-delete-modern" wire:click="confirmUserDeletion" wire:loading.attr="disabled">
                <i class="bi bi-trash me-2"></i>{{ __('Delete Account') }}
            </button>
        </div>

        <!-- Delete User Confirmation Modal -->
        <x-dialog-modal wire:model.live="confirmingUserDeletion">
            <x-slot name="title">
                <div class="modal-header-danger">
                    <div class="modal-icon-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 fw-bold">{{ __('Delete Account') }}</h5>
                        <p class="text-muted small mb-0">{{ __('This action cannot be undone') }}</p>
                    </div>
                </div>
            </x-slot>

            <x-slot name="content">
                <div class="delete-confirmation-content">
                    <p>
                        {{ __('Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                    </p>

                    <div class="mt-4" x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                        <label for="delete_password" class="form-label-profile fw-semibold">
                            <i class="bi bi-shield-lock me-2"></i>{{ __('Password') }}
                        </label>
                        <div class="input-group input-group-profile">
                            <span class="input-group-text">
                                <i class="bi bi-shield-lock"></i>
                            </span>
                            <input id="delete_password"
                                   type="password"
                                   class="form-control"
                                   autocomplete="current-password"
                                   placeholder="{{ __('Enter your password to confirm') }}"
                                   x-ref="password"
                                   wire:model="password"
                                   wire:keydown.enter="deleteUser">
                        </div>
                        <x-input-error for="password" class="mt-2" />
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <button type="button" class="btn btn-outline-secondary btn-modern" wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled">
                    <i class="bi bi-x-lg me-2"></i>{{ __('Cancel') }}
                </button>

                <button type="button" class="btn btn-danger-delete-modern ms-2" wire:click="deleteUser" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="deleteUser">
                        <i class="bi bi-trash me-2"></i>{{ __('Delete Account') }}
                    </span>
                    <span wire:loading wire:target="deleteUser">
                        <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Deleting...') }}
                    </span>
                </button>
            </x-slot>
        </x-dialog-modal>
        </x-slot>
    </x-action-section>

    <style>
    .delete-account-modern {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .danger-zone {
        display: flex;
        align-items: start;
        gap: 1rem;
        padding: 1.25rem;
        background: rgba(var(--bs-danger-rgb), 0.05);
        border: 2px solid rgba(var(--bs-danger-rgb), 0.2);
        border-radius: 12px;
    }

    [data-bs-theme="dark"] .danger-zone {
        background: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.3);
    }

    .danger-icon {
        width: 48px;
        height: 48px;
        background: rgba(var(--bs-danger-rgb), 0.1);
        color: var(--bs-danger);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    [data-bs-theme="dark"] .danger-icon {
        background: rgba(220, 53, 69, 0.2);
        color: #ea868f;
    }

    .danger-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--bs-danger);
        margin-bottom: 0.5rem;
    }

    [data-bs-theme="dark"] .danger-title {
        color: #ea868f;
    }

    .danger-description {
        font-size: 0.875rem;
        color: var(--bs-body-color);
        margin-bottom: 0;
        line-height: 1.6;
    }

    [data-bs-theme="dark"] .danger-description {
        color: rgba(255, 255, 255, 0.85);
    }

    .btn-danger-delete-modern {
        background: linear-gradient(135deg, var(--bs-danger), #dc3545);
        border: none;
        color: white;
        border-radius: 10px;
        font-weight: 600;
        padding: 0.625rem 1.5rem;
        box-shadow: 0 2px 8px rgba(var(--bs-danger-rgb), 0.25);
        transition: all 0.2s ease;
        align-self: flex-start;
    }

    .btn-danger-delete-modern:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(var(--bs-danger-rgb), 0.35);
        color: white;
    }

    [data-bs-theme="dark"] .btn-danger-delete-modern {
        background: linear-gradient(135deg, #ea868f, #dc3545);
    }

    .modal-header-danger {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .modal-icon-danger {
        width: 48px;
        height: 48px;
        background: rgba(var(--bs-danger-rgb), 0.1);
        color: var(--bs-danger);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    [data-bs-theme="dark"] .modal-icon-danger {
        background: rgba(220, 53, 69, 0.2);
        color: #ea868f;
    }

    .delete-confirmation-content {
        padding: 0.5rem 0;
    }

    .delete-confirmation-content p {
        font-size: 0.9375rem;
        color: var(--bs-body-color);
        line-height: 1.6;
    }

    [data-bs-theme="dark"] .delete-confirmation-content p {
        color: rgba(255, 255, 255, 0.85);
    }
    </style>
</div>
