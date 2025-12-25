<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <!-- Welcome Message -->
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold mb-2">{{ __('Create your account') }}</h1>
            <p class="text-muted mb-0">{{ __('Join us and start your journey') }}</p>
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate id="registerForm">
            @csrf

            <!-- Name Field -->
            <div class="mb-3 form-group-modern">
                <x-label for="name" value="{{ __('Name') }}" class="form-label-modern" />
                <div class="input-icon-wrapper">
                    <i class="bi bi-person input-icon"></i>
                    <x-input id="name"
                             type="text"
                             name="name"
                             :value="old('name')"
                             required
                             autofocus
                             autocomplete="name"
                             class="form-control-modern ps-5"
                             placeholder="{{ __('Your full name') }}" />
                </div>
                <x-input-error for="name" />
            </div>

            <!-- Email Field -->
            <div class="mb-3 form-group-modern">
                <x-label for="email" value="{{ __('Email') }}" class="form-label-modern" />
                <div class="input-icon-wrapper">
                    <i class="bi bi-envelope input-icon"></i>
                    <x-input id="email"
                             type="email"
                             name="email"
                             :value="old('email')"
                             required
                             autocomplete="username"
                             class="form-control-modern ps-5"
                             placeholder="your@email.com" />
                </div>
                <x-input-error for="email" />
            </div>

            <!-- Password Field -->
            <div class="mb-3 form-group-modern">
                <x-label for="password" value="{{ __('Password') }}" class="form-label-modern" />
                <div class="input-icon-wrapper position-relative">
                    <i class="bi bi-lock input-icon"></i>
                    <x-input id="password"
                             type="password"
                             name="password"
                             required
                             autocomplete="new-password"
                             class="form-control-modern ps-5 pe-5"
                             placeholder="••••••••••••"
                             onkeyup="validatePassword()" />
                    <button type="button"
                            class="btn-password-toggle"
                            onclick="togglePassword('password', 'togglePasswordIcon')"
                            aria-label="{{ __('Toggle password visibility') }}">
                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
                <x-input-error for="password" />

                <!-- Password Strength Indicator -->
                <div class="password-strength mt-2" id="passwordStrength" style="display: none;">
                    <div class="strength-bar-container">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="strength-text" id="strengthText"></div>
                </div>

                <!-- Password Requirements -->
                <div class="password-requirements mt-3" id="passwordRequirements">
                    <div class="requirement-title">{{ __('Password must contain:') }}</div>
                    <div class="requirement-item" id="req-length">
                        <i class="bi bi-circle"></i>
                        <span>{{ __('At least 12 characters') }}</span>
                    </div>
                    <div class="requirement-item" id="req-uppercase">
                        <i class="bi bi-circle"></i>
                        <span>{{ __('At least 1 uppercase letter') }}</span>
                    </div>
                    <div class="requirement-item" id="req-number">
                        <i class="bi bi-circle"></i>
                        <span>{{ __('At least 1 number') }}</span>
                    </div>
                    <div class="requirement-item" id="req-special">
                        <i class="bi bi-circle"></i>
                        <span>{{ __('At least 2 special characters') }}</span>
                    </div>
                </div>
            </div>

            <!-- Confirm Password Field -->
            <div class="mb-3 form-group-modern">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" class="form-label-modern" />
                <div class="input-icon-wrapper position-relative">
                    <i class="bi bi-lock-fill input-icon"></i>
                    <x-input id="password_confirmation"
                             type="password"
                             name="password_confirmation"
                             required
                             autocomplete="new-password"
                             class="form-control-modern ps-5 pe-5"
                             placeholder="••••••••••••"
                             onkeyup="checkPasswordMatch()" />
                    <button type="button"
                            class="btn-password-toggle"
                            onclick="togglePassword('password_confirmation', 'togglePasswordConfirmIcon')"
                            aria-label="{{ __('Toggle password visibility') }}">
                        <i class="bi bi-eye" id="togglePasswordConfirmIcon"></i>
                    </button>
                </div>
                <div class="password-match mt-3" id="passwordMatch" style="display: none;"></div>
            </div>

            <!-- Terms and Privacy Policy -->
            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="form-check form-check-modern mb-4">
                    <x-checkbox name="terms" id="terms" required class="form-check-input-modern" />
                    <label class="form-check-label" for="terms">
                        {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="link-primary">'.__('Terms of Service').'</a>',
                                'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="link-primary">'.__('Privacy Policy').'</a>',
                        ]) !!}
                    </label>
                </div>
            @endif

            <!-- Submit Button -->
            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-primary btn-register gradient-theme-btn" id="registerButton">
                    <span class="btn-text">
                        <i class="bi bi-person-plus me-2"></i>
                        {{ __('Create account') }}
                    </span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        {{ __('Creating your account...') }}
                    </span>
                </button>
            </div>

            <!-- Login Link -->
            <div class="text-center">
                <p class="text-muted mb-0">
                    {{ __('Already have an account?') }}
                    <a href="{{ route('login') }}" class="link-register">
                        {{ __('Sign in here') }}
                    </a>
                </p>
            </div>
        </form>

        <!-- Security Badge -->
        <div class="security-badge text-center mt-4">
            <i class="bi bi-shield-check text-success me-2"></i>
            <span class="small text-muted">{{ __('Your data is protected with encryption') }}</span>
        </div>
    </x-authentication-card>

    <!-- Custom Styles -->
    <style>
        /* Modern Form Elements */
        .form-group-modern {
            position: relative;
        }

        .form-label-modern {
            font-weight: 600;
            color: var(--bs-body-color);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--bs-secondary);
            font-size: 1.125rem;
            pointer-events: none;
            z-index: 5;
        }

        [data-bs-theme="dark"] .input-icon {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-control-modern {
            height: 48px;
            border-radius: 12px;
            border: 2px solid var(--bs-border-color);
            font-size: 0.9375rem;
            transition: all 0.3s ease;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
        }

        .form-control-modern:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.15);
            background-color: var(--bs-body-bg);
        }

        .form-control-modern::placeholder {
            color: var(--bs-secondary);
            opacity: 0.6;
        }

        [data-bs-theme="dark"] .form-control-modern {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
            color: rgba(255, 255, 255, 0.95);
        }

        [data-bs-theme="dark"] .form-control-modern:focus {
            background-color: rgba(255, 255, 255, 0.12);
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
        }

        [data-bs-theme="dark"] .form-control-modern::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        /* Password Toggle Button */
        .btn-password-toggle {
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

        .btn-password-toggle:hover {
            color: var(--bs-primary);
        }

        [data-bs-theme="dark"] .btn-password-toggle {
            color: rgba(255, 255, 255, 0.5);
        }

        [data-bs-theme="dark"] .btn-password-toggle:hover {
            color: var(--bs-primary);
        }

        /* Password Strength Indicator */
        .password-strength {
            padding: 0.75rem;
            background: rgba(var(--bs-secondary-rgb), 0.05);
            border-radius: 8px;
        }

        [data-bs-theme="dark"] .password-strength {
            background: rgba(255, 255, 255, 0.03);
        }

        .strength-bar-container {
            height: 6px;
            background: rgba(var(--bs-secondary-rgb), 0.2);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        [data-bs-theme="dark"] .strength-bar-container {
            background: rgba(255, 255, 255, 0.1);
        }

        .strength-bar {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 3px;
        }

        .strength-text {
            font-size: 0.875rem;
            font-weight: 600;
            text-align: center;
        }

        /* Password Requirements */
        .password-requirements {
            padding: 1rem;
            background: #ffffff;
            border: 1px solid rgba(var(--bs-info-rgb), 0.3);
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        [data-bs-theme="dark"] .password-requirements {
            background: rgba(13, 110, 253, 0.1);
            border-color: rgba(13, 110, 253, 0.2);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .requirement-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--bs-body-color);
            margin-bottom: 0.75rem;
        }

        [data-bs-theme="dark"] .requirement-title {
            color: rgba(255, 255, 255, 0.9);
        }

        .requirement-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--bs-secondary);
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
        }

        .requirement-item:last-child {
            margin-bottom: 0;
        }

        .requirement-item i {
            font-size: 0.625rem;
        }

        .requirement-item.valid {
            color: var(--bs-success);
        }

        .requirement-item.valid i {
            color: var(--bs-success);
        }

        .requirement-item.valid i::before {
            content: "\f26b";
        }

        [data-bs-theme="dark"] .requirement-item {
            color: rgba(255, 255, 255, 0.6);
        }

        [data-bs-theme="dark"] .requirement-item.valid {
            color: #75b798;
        }

        /* Password Match Indicator */
        .password-match {
            margin-top: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .password-match.match {
            background: rgba(var(--bs-success-rgb), 0.1);
            border: 1px solid rgba(var(--bs-success-rgb), 0.3);
            color: var(--bs-success);
        }

        .password-match.no-match {
            background: rgba(var(--bs-danger-rgb), 0.1);
            border: 1px solid rgba(var(--bs-danger-rgb), 0.3);
            color: var(--bs-danger);
        }

        [data-bs-theme="dark"] .password-match.match {
            background: rgba(25, 135, 84, 0.15);
            border-color: rgba(25, 135, 84, 0.3);
            color: #75b798;
        }

        [data-bs-theme="dark"] .password-match.no-match {
            background: rgba(220, 53, 69, 0.15);
            border-color: rgba(220, 53, 69, 0.3);
            color: #ea868f;
        }

        /* Modern Checkbox */
        .form-check-modern {
            display: flex;
            align-items: start;
            gap: 0.75rem;
        }

        .form-check-input-modern {
            width: 1.125rem;
            height: 1.125rem;
            cursor: pointer;
            border: 2px solid var(--bs-border-color);
            margin-top: 0.125rem;
            flex-shrink: 0;
        }

        [data-bs-theme="dark"] .form-check-input-modern {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
        }

        [data-bs-theme="dark"] .form-check-input-modern:checked {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }

        .form-check-modern label {
            font-size: 0.9375rem;
            color: var(--bs-body-color);
            cursor: pointer;
            line-height: 1.5;
        }

        [data-bs-theme="dark"] .form-check-modern label {
            color: rgba(255, 255, 255, 0.85);
        }

        .form-check-modern label a {
            font-weight: 600;
        }

        /* Links */
        .link-register {
            color: var(--bs-primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .link-register:hover {
            color: var(--bs-primary);
            text-decoration: underline;
        }

        [data-bs-theme="dark"] .link-register {
            color: #6ea8fe;
        }

        [data-bs-theme="dark"] .link-register:hover {
            color: #9ec5fe;
        }

        /* Register Button - uses global .gradient-theme-btn class */
        .btn-register {
            height: 52px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn-register:hover:not(:disabled) {
            transform: translateY(-2px);
        }

        .btn-register:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-register:disabled {
            opacity: 0.8;
            cursor: not-allowed;
        }

        .btn-loading {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Security Badge */
        .security-badge {
            padding: 0.75rem;
            background: rgba(var(--bs-success-rgb), 0.1);
            border: 1px solid rgba(var(--bs-success-rgb), 0.2);
            border-radius: 12px;
        }

        [data-bs-theme="dark"] .security-badge {
            background: rgba(25, 135, 84, 0.15);
            border-color: rgba(25, 135, 84, 0.3);
        }

        [data-bs-theme="dark"] .security-badge .text-success {
            color: #75b798 !important;
        }

        [data-bs-theme="dark"] .security-badge .text-muted {
            color: rgba(255, 255, 255, 0.6) !important;
        }

        /* Welcome Message */
        h1 {
            color: var(--bs-body-color);
        }

        [data-bs-theme="dark"] h1 {
            color: rgba(255, 255, 255, 0.95);
        }

        [data-bs-theme="dark"] .text-muted {
            color: rgba(255, 255, 255, 0.6) !important;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .form-control-modern {
                height: 46px;
                font-size: 0.875rem;
            }

            .btn-register {
                height: 48px;
                font-size: 0.9375rem;
            }

            h1.h3 {
                font-size: 1.5rem;
            }
        }
    </style>

    <!-- JavaScript -->
    <script>
        // Toggle password visibility
        function togglePassword(fieldId, iconId) {
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
        function validatePassword() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const strengthContainer = document.getElementById('passwordStrength');

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
            updateRequirement('req-length', requirements.length);
            updateRequirement('req-uppercase', requirements.uppercase);
            updateRequirement('req-number', requirements.number);
            updateRequirement('req-special', requirements.special);

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
            checkPasswordMatch();
        }

        // Update requirement item status
        function updateRequirement(elementId, isValid) {
            const element = document.getElementById(elementId);
            if (isValid) {
                element.classList.add('valid');
            } else {
                element.classList.remove('valid');
            }
        }

        // Check if passwords match
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;
            const matchIndicator = document.getElementById('passwordMatch');

            if (confirmation.length === 0) {
                matchIndicator.style.display = 'none';
                return;
            }

            matchIndicator.style.display = 'block';

            if (password === confirmation) {
                matchIndicator.className = 'password-match match';
                matchIndicator.innerHTML = '<i class="bi bi-check-circle-fill"></i> Passwords match';
            } else {
                matchIndicator.className = 'password-match no-match';
                matchIndicator.innerHTML = '<i class="bi bi-x-circle-fill"></i> Passwords do not match';
            }
        }

        // Form submission handling
        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.getElementById('registerForm');
            const registerButton = document.getElementById('registerButton');

            if (registerForm && registerButton) {
                registerForm.addEventListener('submit', function(e) {
                    // Check if form is valid
                    if (registerForm.checkValidity()) {
                        // Disable button
                        registerButton.disabled = true;

                        // Toggle text and loading state
                        const btnText = registerButton.querySelector('.btn-text');
                        const btnLoading = registerButton.querySelector('.btn-loading');

                        if (btnText && btnLoading) {
                            btnText.classList.add('d-none');
                            btnLoading.classList.remove('d-none');
                        }
                    }
                });
            }

            // Add floating label effect
            const inputs = document.querySelectorAll('.form-control-modern');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });

                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });
        });
    </script>
</x-guest-layout>
