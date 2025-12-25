<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <!-- Welcome Message -->
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold mb-2">{{ __('Welcome back!') }}</h1>
            <p class="text-muted mb-0">{{ __('Enter your credentials to access your account') }}</p>
        </div>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="alert alert-success alert-modern mb-4 d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <span>{{ $value }}</span>
            </div>
        @endsession

        <!-- Session Expired Alert -->
        <div id="sessionExpiredAlert" class="alert alert-info alert-modern alert-dismissible fade mb-4 d-none" role="alert">
            <div class="d-flex align-items-start">
                <i class="bi bi-info-circle-fill me-2 fs-5 flex-shrink-0 mt-1"></i>
                <div class="flex-grow-1">
                    <strong class="d-block mb-1">Sessão expirada por segurança</strong>
                    <p class="mb-0 small">Por segurança, sua sessão foi encerrada automaticamente. Por favor, faça login novamente para continuar.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>

        <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate id="loginForm">
            @csrf

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
                             autofocus
                             autocomplete="username"
                             class="form-control-modern ps-5"
                             placeholder="seu@email.com" />
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
                             autocomplete="current-password"
                             class="form-control-modern ps-5 pe-5"
                             placeholder="••••••••" />
                    <button type="button"
                            class="btn-password-toggle"
                            onclick="togglePasswordVisibility()"
                            aria-label="{{ __('Toggle password visibility') }}">
                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
                <x-input-error for="password" />
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check form-check-modern">
                    <x-checkbox id="remember_me" name="remember" class="form-check-input-modern" />
                    <label class="form-check-label" for="remember_me">
                        &nbsp;&nbsp;{{ __('Remember me') }}
                    </label>
                </div>

                @if (Route::has('password.request'))
                    <a class="link-forgot" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <!-- Submit Button -->
            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-primary btn-login gradient-theme-btn" id="loginButton">
                    <span class="btn-text">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        {{ __('Sign in to your account') }}
                    </span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        {{ __('We validate your credentials...') }}
                    </span>
                </button>
            </div>

            <!-- Register Link -->
            @if (Route::has('register'))
                <div class="text-center">
                    <p class="text-muted mb-0">
                        {{ __("Don't have an account?") }}
                        <a href="{{ route('register') }}" class="link-register">
                            {{ __('Create one now') }}
                        </a>
                    </p>
                </div>
            @endif
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

        /* Modern Checkbox */
        .form-check-modern {
            display: flex;
            align-items: center;
        }

        .form-check-input-modern {
            width: 1.125rem;
            height: 1.125rem;
            cursor: pointer;
            border: 2px solid var(--bs-border-color);
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
            margin-bottom: 0;
        }

        [data-bs-theme="dark"] .form-check-modern label {
            color: rgba(255, 255, 255, 0.85);
        }

        /* Links */
        .link-forgot {
            color: var(--bs-primary);
            text-decoration: none;
            font-size: 0.9375rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .link-forgot:hover {
            color: var(--bs-primary);
            text-decoration: underline;
        }

        [data-bs-theme="dark"] .link-forgot {
            color: #6ea8fe;
        }

        [data-bs-theme="dark"] .link-forgot:hover {
            color: #9ec5fe;
        }

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

        /* Login Button - uses global .gradient-theme-btn class */
        .btn-login {
            height: 52px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
        }

        .btn-login:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.8;
            cursor: not-allowed;
        }

        .btn-loading {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Alert Modern */
        .alert-modern {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        [data-bs-theme="dark"] .alert-modern {
            background-color: rgba(25, 135, 84, 0.15);
            border: 1px solid rgba(25, 135, 84, 0.3);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.4);
        }

        [data-bs-theme="dark"] .alert-modern.alert-success {
            color: #75b798;
        }

        [data-bs-theme="dark"] .alert-modern.alert-info {
            background-color: rgba(13, 202, 240, 0.15);
            border: 1px solid rgba(13, 202, 240, 0.3);
            color: #9eeaf9;
        }

        .alert-modern.alert-info .bi-info-circle-fill {
            color: var(--bs-info);
        }

        [data-bs-theme="dark"] .alert-modern.alert-info .bi-info-circle-fill {
            color: #9eeaf9;
        }

        .alert-modern .btn-close {
            font-size: 0.75rem;
        }

        [data-bs-theme="dark"] .alert-modern .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
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

        /* Animations */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .shake {
            animation: shake 0.4s ease-in-out;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .form-control-modern {
                height: 46px;
                font-size: 0.875rem;
            }

            .btn-login {
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
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePasswordIcon');

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

        // Form submission handling
        document.addEventListener('DOMContentLoaded', function() {
            // Check for expired session
            const sessionExpired = localStorage.getItem('session_expired');
            if (sessionExpired === 'true') {
                const alert = document.getElementById('sessionExpiredAlert');
                if (alert) {
                    alert.classList.remove('d-none');
                    alert.classList.add('show');

                    // Clear the flag
                    localStorage.removeItem('session_expired');

                    // Auto-hide after 15 seconds
                    setTimeout(() => {
                        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                        bsAlert.close();
                    }, 15000);
                }
            }

            const loginForm = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');

            if (loginForm && loginButton) {
                loginForm.addEventListener('submit', function(e) {
                    // Check if form is valid
                    if (loginForm.checkValidity()) {
                        // Disable button
                        loginButton.disabled = true;

                        // Toggle text and loading state
                        const btnText = loginButton.querySelector('.btn-text');
                        const btnLoading = loginButton.querySelector('.btn-loading');

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

            // Check for validation errors and shake the form
            @if ($errors->any())
                const form = document.getElementById('loginForm');
                if (form) {
                    form.classList.add('shake');
                    setTimeout(() => {
                        form.classList.remove('shake');
                    }, 400);
                }
            @endif
        });
    </script>
</x-guest-layout>
