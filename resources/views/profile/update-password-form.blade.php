<div>
    <x-form-section submit="updatePassword">
        <x-slot name="title">
            {{ __('Update Password') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </x-slot>

        <x-slot name="form">
        <!-- Current Password -->
        <div class="col-12">
            <label for="current_password" class="form-label-profile fw-semibold">
                <i class="bi bi-shield-lock me-2"></i>{{ __('Current Password') }}
            </label>
            <div class="input-group input-group-profile position-relative">
                <span class="input-group-text">
                    <i class="bi bi-shield-lock"></i>
                </span>
                <input id="current_password"
                       type="password"
                       class="form-control @error('current_password') is-invalid @enderror"
                       wire:model="state.current_password"
                       autocomplete="current-password"
                       placeholder="••••••••••••">
                <button type="button"
                        class="btn-password-toggle-profile"
                        onclick="togglePasswordProfile('current_password', 'toggleCurrentIcon')"
                        aria-label="{{ __('Toggle password visibility') }}">
                    <i class="bi bi-eye" id="toggleCurrentIcon"></i>
                </button>
            </div>
            <x-input-error for="current_password" class="mt-2" />
        </div>

        <!-- New Password -->
        <div class="col-12 col-md-6">
            <label for="password" class="form-label-profile fw-semibold">
                <i class="bi bi-key me-2"></i>{{ __('New Password') }}
            </label>
            <div class="input-group input-group-profile position-relative">
                <span class="input-group-text">
                    <i class="bi bi-key"></i>
                </span>
                <input id="password"
                       type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       wire:model="state.password"
                       autocomplete="new-password"
                       placeholder="••••••••••••"
                       onkeyup="validatePasswordProfile()">
                <button type="button"
                        class="btn-password-toggle-profile"
                        onclick="togglePasswordProfile('password', 'toggleNewIcon')"
                        aria-label="{{ __('Toggle password visibility') }}">
                    <i class="bi bi-eye" id="toggleNewIcon"></i>
                </button>
            </div>
            <x-input-error for="password" class="mt-2" />

            <!-- Password Strength Indicator -->
            <div class="password-strength-profile mt-3" id="passwordStrengthProfile" style="display: none;">
                <div class="strength-bar-container-profile">
                    <div class="strength-bar-profile" id="strengthBarProfile"></div>
                </div>
                <div class="strength-text-profile" id="strengthTextProfile"></div>
            </div>

            <!-- Password Requirements -->
            <div class="password-requirements-profile mt-3" id="passwordRequirementsProfile">
                <div class="requirement-title-profile">{{ __('Password must contain:') }}</div>
                <div class="requirement-item-profile" id="req-length-profile">
                    <i class="bi bi-circle"></i>
                    <span>{{ __('At least 12 characters') }}</span>
                </div>
                <div class="requirement-item-profile" id="req-uppercase-profile">
                    <i class="bi bi-circle"></i>
                    <span>{{ __('At least 1 uppercase letter') }}</span>
                </div>
                <div class="requirement-item-profile" id="req-number-profile">
                    <i class="bi bi-circle"></i>
                    <span>{{ __('At least 1 number') }}</span>
                </div>
                <div class="requirement-item-profile" id="req-special-profile">
                    <i class="bi bi-circle"></i>
                    <span>{{ __('At least 2 special characters') }}</span>
                </div>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="col-12 col-md-6">
            <label for="password_confirmation" class="form-label-profile fw-semibold">
                <i class="bi bi-key-fill me-2"></i>{{ __('Confirm Password') }}
            </label>
            <div class="input-group input-group-profile position-relative">
                <span class="input-group-text">
                    <i class="bi bi-key-fill"></i>
                </span>
                <input id="password_confirmation"
                       type="password"
                       class="form-control @error('password_confirmation') is-invalid @enderror"
                       wire:model="state.password_confirmation"
                       autocomplete="new-password"
                       placeholder="••••••••••••"
                       onkeyup="checkPasswordMatchProfile()">
                <button type="button"
                        class="btn-password-toggle-profile"
                        onclick="togglePasswordProfile('password_confirmation', 'toggleConfirmIcon')"
                        aria-label="{{ __('Toggle password visibility') }}">
                    <i class="bi bi-eye" id="toggleConfirmIcon"></i>
                </button>
            </div>
            <x-input-error for="password_confirmation" class="mt-2" />

            <!-- Password Match Indicator -->
            <div class="password-match-profile mt-3" id="passwordMatchProfile" style="display: none;"></div>
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            <i class="bi bi-check-circle-fill me-1"></i>{{ __('Password updated successfully!') }}
        </x-action-message>

        <button type="submit" class="btn btn-primary-modern">
            <span wire:loading.remove wire:target="updatePassword">
                <i class="bi bi-shield-check me-2"></i>{{ __('Update Password') }}
            </span>
            <span wire:loading wire:target="updatePassword">
                <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Updating...') }}
            </span>
        </button>
    </x-slot>
    </x-form-section>

    <style>
    /* Password Toggle Button */
    .btn-password-toggle-profile {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--bs-secondary);
        font-size: 1.125rem;
        cursor: pointer;
        padding: 0.25rem;
        z-index: 5;
        transition: color 0.2s ease;
    }

    .btn-password-toggle-profile:hover {
        color: var(--bs-primary);
    }

    [data-bs-theme="dark"] .btn-password-toggle-profile {
        color: rgba(255, 255, 255, 0.5);
    }

    [data-bs-theme="dark"] .btn-password-toggle-profile:hover {
        color: var(--bs-primary);
    }

    .input-group-profile.position-relative .form-control {
        padding-right: 3rem;
    }

    /* Password Strength Indicator */
    .password-strength-profile {
        padding: 0.75rem;
        background: rgba(var(--bs-secondary-rgb), 0.05);
        border-radius: 8px;
    }

    [data-bs-theme="dark"] .password-strength-profile {
        background: rgba(255, 255, 255, 0.03);
    }

    .strength-bar-container-profile {
        height: 6px;
        background: rgba(var(--bs-secondary-rgb), 0.2);
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    [data-bs-theme="dark"] .strength-bar-container-profile {
        background: rgba(255, 255, 255, 0.1);
    }

    .strength-bar-profile {
        height: 100%;
        transition: all 0.3s ease;
        border-radius: 3px;
    }

    .strength-text-profile {
        font-size: 0.875rem;
        font-weight: 600;
        text-align: center;
    }

    /* Password Requirements */
    .password-requirements-profile {
        padding: 1rem;
        background: #ffffff;
        border: 1px solid rgba(var(--bs-info-rgb), 0.3);
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    [data-bs-theme="dark"] .password-requirements-profile {
        background: rgba(13, 110, 253, 0.1);
        border-color: rgba(13, 110, 253, 0.2);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .requirement-title-profile {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--bs-body-color);
        margin-bottom: 0.75rem;
    }

    [data-bs-theme="dark"] .requirement-title-profile {
        color: rgba(255, 255, 255, 0.9);
    }

    .requirement-item-profile {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--bs-secondary);
        margin-bottom: 0.5rem;
        transition: all 0.2s ease;
    }

    .requirement-item-profile:last-child {
        margin-bottom: 0;
    }

    .requirement-item-profile i {
        font-size: 0.625rem;
    }

    .requirement-item-profile.valid {
        color: var(--bs-success);
    }

    .requirement-item-profile.valid i {
        color: var(--bs-success);
    }

    .requirement-item-profile.valid i::before {
        content: "\f26b";
    }

    [data-bs-theme="dark"] .requirement-item-profile {
        color: rgba(255, 255, 255, 0.6);
    }

    [data-bs-theme="dark"] .requirement-item-profile.valid {
        color: #75b798;
    }

    /* Password Match Indicator */
    .password-match-profile {
        margin-top: 0.75rem;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .password-match-profile.match {
        background: rgba(var(--bs-success-rgb), 0.1);
        border: 1px solid rgba(var(--bs-success-rgb), 0.3);
        color: var(--bs-success);
    }

    .password-match-profile.no-match {
        background: rgba(var(--bs-danger-rgb), 0.1);
        border: 1px solid rgba(var(--bs-danger-rgb), 0.3);
        color: var(--bs-danger);
    }

    [data-bs-theme="dark"] .password-match-profile.match {
        background: rgba(25, 135, 84, 0.15);
        border-color: rgba(25, 135, 84, 0.3);
        color: #75b798;
    }

    [data-bs-theme="dark"] .password-match-profile.no-match {
        background: rgba(220, 53, 69, 0.15);
        border-color: rgba(220, 53, 69, 0.3);
        color: #ea868f;
    }
</style>

<script>
    // Toggle password visibility
    function togglePasswordProfile(fieldId, iconId) {
        const passwordInput = document.getElementById(fieldId);
        const toggleIcon = document.getElementById(iconId);

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        }
    }

    // Validate password strength in real-time
    function validatePasswordProfile() {
        const password = document.getElementById('password').value;
        const strengthBar = document.getElementById('strengthBarProfile');
        const strengthText = document.getElementById('strengthTextProfile');
        const strengthContainer = document.getElementById('passwordStrengthProfile');

        // Show strength indicator
        if (password.length > 0) {
            strengthContainer.style.display = 'block';
        } else {
            strengthContainer.style.display = 'none';
            return;
        }

        // Check requirements
        const requirements = {
            length: password.length >= 12,
            uppercase: /[A-Z]/.test(password),
            number: /\d/.test(password),
            special: (password.match(/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/g) || []).length >= 2
        };

        // Update requirement items
        updateRequirementProfile('req-length-profile', requirements.length);
        updateRequirementProfile('req-uppercase-profile', requirements.uppercase);
        updateRequirementProfile('req-number-profile', requirements.number);
        updateRequirementProfile('req-special-profile', requirements.special);

        // Calculate strength
        const validRequirements = Object.values(requirements).filter(Boolean).length;
        let strength = 0;
        let strengthLabel = '';
        let strengthColor = '';

        if (validRequirements === 0) {
            strength = 0;
            strengthLabel = 'Very Weak';
            strengthColor = '#dc3545';
        } else if (validRequirements === 1) {
            strength = 25;
            strengthLabel = 'Weak';
            strengthColor = '#fd7e14';
        } else if (validRequirements === 2) {
            strength = 50;
            strengthLabel = 'Fair';
            strengthColor = '#ffc107';
        } else if (validRequirements === 3) {
            strength = 75;
            strengthLabel = 'Good';
            strengthColor = '#20c997';
        } else {
            strength = 100;
            strengthLabel = 'Strong';
            strengthColor = '#198754';
        }

        strengthBar.style.width = strength + '%';
        strengthBar.style.backgroundColor = strengthColor;
        strengthText.textContent = strengthLabel;
        strengthText.style.color = strengthColor;

        // Check password match if confirmation field has value
        checkPasswordMatchProfile();
    }

    // Update requirement item status
    function updateRequirementProfile(elementId, isValid) {
        const element = document.getElementById(elementId);
        if (element) {
            if (isValid) {
                element.classList.add('valid');
            } else {
                element.classList.remove('valid');
            }
        }
    }

    // Check if passwords match
    function checkPasswordMatchProfile() {
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;
        const matchIndicator = document.getElementById('passwordMatchProfile');

        if (!matchIndicator) return;

        if (confirmation.length === 0) {
            matchIndicator.style.display = 'none';
            return;
        }

        matchIndicator.style.display = 'block';

        if (password === confirmation) {
            matchIndicator.className = 'password-match-profile match';
            matchIndicator.innerHTML = '<i class="bi bi-check-circle-fill"></i> Passwords match';
        } else {
            matchIndicator.className = 'password-match-profile no-match';
            matchIndicator.innerHTML = '<i class="bi bi-x-circle-fill"></i> Passwords do not match';
        }
    }
    </script>
</div>
