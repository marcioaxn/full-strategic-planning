<div class="min-vh-100 d-flex align-items-center justify-content-center guest-container w-100">
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <!-- Mensagem de Boas-vindas -->
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold mb-2">{{ __('Security Update') }}</h1>
            <p class="text-muted mb-0">{{ __('You must change your password to continue') }}</p>
        </div>

        <!-- Alertas de Erro Globais (opcional) -->
        <x-validation-errors class="mb-4" />

        <form wire:submit.prevent="trocarSenha">
            <!-- Senha Atual -->
            <div class="mb-3 form-group-modern">
                <x-label for="senhaAtual" value="{{ __('Current Password') }}" class="form-label-modern" />
                <div class="input-icon-wrapper position-relative">
                    <i class="bi bi-key input-icon"></i>
                    <input id="senhaAtual"
                           type="password"
                           wire:model="senhaAtual"
                           required
                           autofocus
                           class="form-control-modern w-100 ps-5 pe-5 {{ $errors->has('senhaAtual') ? 'is-invalid' : '' }}"
                           placeholder="••••••••••••">
                    <button type="button"
                            class="btn-password-toggle"
                            onclick="togglePassword('senhaAtual', 'toggleIconCurrent')">
                        <i class="bi bi-eye" id="toggleIconCurrent"></i>
                    </button>
                </div>
                @error('senhaAtual') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
            </div>

            <hr class="my-4 opacity-10">

            <!-- Nova Senha -->
            <div class="mb-3 form-group-modern">
                <x-label for="novaSenha" value="{{ __('New Password') }}" class="form-label-modern" />
                <div class="input-icon-wrapper position-relative">
                    <i class="bi bi-lock input-icon"></i>
                    <input id="novaSenha"
                           type="password"
                           wire:model="novaSenha"
                           required
                           class="form-control-modern w-100 ps-5 pe-5 {{ $errors->has('novaSenha') ? 'is-invalid' : '' }}"
                           placeholder="••••••••••••"
                           onkeyup="validatePasswordStrength()">
                    <button type="button"
                            class="btn-password-toggle"
                            onclick="togglePassword('novaSenha', 'toggleIconNew')">
                        <i class="bi bi-eye" id="toggleIconNew"></i>
                    </button>
                </div>
                @error('novaSenha') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror

                <!-- Força da Senha -->
                <div class="password-strength mt-2" id="passwordStrength" style="display: none;" wire:ignore>
                    <div class="strength-bar-container">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="strength-text" id="strengthText"></div>
                </div>
            </div>

            <!-- Confirmar Nova Senha -->
            <div class="mb-4 form-group-modern">
                <x-label for="novaSenha_confirmation" value="{{ __('Confirm New Password') }}" class="form-label-modern" />
                <div class="input-icon-wrapper position-relative">
                    <i class="bi bi-lock-fill input-icon"></i>
                    <input id="novaSenha_confirmation"
                           type="password"
                           wire:model="novaSenha_confirmation"
                           required
                           class="form-control-modern w-100 ps-5 pe-5"
                           placeholder="••••••••••••"
                           onkeyup="checkPasswordsMatch()">
                    <button type="button"
                            class="btn-password-toggle"
                            onclick="togglePassword('novaSenha_confirmation', 'toggleIconConfirm')">
                        <i class="bi bi-eye" id="toggleIconConfirm"></i>
                    </button>
                </div>
                <div class="password-match mt-3" id="passwordMatch" style="display: none;" wire:ignore></div>
            </div>

            <!-- Botão Salvar -->
            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-primary btn-register gradient-theme-btn" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="trocarSenha">
                        <i class="bi bi-shield-check me-2"></i>
                        {{ __('Update Password') }}
                    </span>
                    <span wire:loading wire:target="trocarSenha" class="btn-loading">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        {{ __('Processing...') }}
                    </span>
                </button>
            </div>
        </form>

        <!-- Logout -->
        <div class="text-center mt-3 border-top pt-3">
            <button type="button" 
                    wire:click="logout"
                    class="btn btn-link link-register text-muted text-decoration-none small">
                <i class="bi bi-box-arrow-right me-1"></i>
                {{ __('Logout and change later') }}
            </button>
        </div>

        <!-- Security Badge -->
        <div class="security-badge text-center mt-4">
            <i class="bi bi-shield-fill-check text-success me-2"></i>
            <span class="small text-muted">{{ __('Highly secure encrypted connection') }}</span>
        </div>
    </x-authentication-card>

    <style>
        .form-group-modern { position: relative; }
        .form-label-modern { font-weight: 600; color: var(--bs-body-color); margin-bottom: 0.5rem; font-size: 0.875rem; }
        .input-icon-wrapper { position: relative; }
        .input-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--bs-secondary); font-size: 1.125rem; pointer-events: none; z-index: 5; }
        [data-bs-theme="dark"] .input-icon { color: rgba(255, 255, 255, 0.5); }
        .form-control-modern { height: 48px; border-radius: 12px; border: 2px solid var(--bs-border-color); font-size: 0.9375rem; transition: all 0.3s ease; background-color: var(--bs-body-bg); color: var(--bs-body-color); }
        .form-control-modern:focus { border-color: var(--bs-primary); outline: none; box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.15); }
        [data-bs-theme="dark"] .form-control-modern { background-color: rgba(255, 255, 255, 0.08); border-color: rgba(255, 255, 255, 0.15); color: rgba(255, 255, 255, 0.95); }
        .btn-password-toggle { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--bs-secondary); font-size: 1.125rem; cursor: pointer; padding: 0.25rem; z-index: 5; }
        .password-strength { padding: 0.75rem; background: rgba(var(--bs-secondary-rgb), 0.05); border-radius: 8px; }
        .strength-bar-container { height: 6px; background: rgba(var(--bs-secondary-rgb), 0.2); border-radius: 3px; overflow: hidden; margin-bottom: 0.5rem; }
        .strength-bar { height: 100%; transition: all 0.3s ease; border-radius: 3px; }
        .strength-text { font-size: 0.875rem; font-weight: 600; text-align: center; }
        .password-match { margin-top: 0.75rem; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.875rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; }
        .password-match.match { background: rgba(var(--bs-success-rgb), 0.1); border: 1px solid rgba(var(--bs-success-rgb), 0.3); color: var(--bs-success); }
        .password-match.no-match { background: rgba(var(--bs-danger-rgb), 0.1); border: 1px solid rgba(var(--bs-danger-rgb), 0.3); color: var(--bs-danger); }
        .btn-register { height: 52px; border-radius: 12px; font-weight: 600; font-size: 1rem; position: relative; overflow: hidden; }
        .security-badge { padding: 0.75rem; background: rgba(var(--bs-success-rgb), 0.1); border: 1px solid rgba(var(--bs-success-rgb), 0.2); border-radius: 12px; }
        [data-bs-theme="dark"] .security-badge { background: rgba(25, 135, 84, 0.15); border-color: rgba(25, 135, 84, 0.3); }
    </style>

    <script>
        function togglePassword(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        function validatePasswordStrength() {
            const password = document.getElementById('novaSenha').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const container = document.getElementById('passwordStrength');

            if (password.length > 0) container.style.display = 'block';
            else { container.style.display = 'none'; return; }

            let strength = 0;
            if (password.length >= 8) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/\d/.test(password)) strength += 25;
            if (/[!@#$%^&*()]/.test(password)) strength += 25;

            let color = '#dc3545';
            let label = 'Muito Fraca';
            if (strength >= 100) { color = '#198754'; label = 'Forte'; }
            else if (strength >= 75) { color = '#20c997'; label = 'Boa'; }
            else if (strength >= 50) { color = '#ffc107'; label = 'Razoável'; }
            else if (strength >= 25) { color = '#fd7e14'; label = 'Fraca'; }

            strengthBar.style.width = strength + '%';
            strengthBar.style.backgroundColor = color;
            strengthText.textContent = label;
            strengthText.style.color = color;
            checkPasswordsMatch();
        }

        function checkPasswordsMatch() {
            const p1 = document.getElementById('novaSenha').value;
            const p2 = document.getElementById('novaSenha_confirmation').value;
            const indicator = document.getElementById('passwordMatch');

            if (!p2) { indicator.style.display = 'none'; return; }
            indicator.style.display = 'block';

            if (p1 === p2) {
                indicator.className = 'password-match match';
                indicator.innerHTML = '<i class="bi bi-check-circle-fill"></i> Senhas coincidem';
            } else {
                indicator.className = 'password-match no-match';
                indicator.innerHTML = '<i class="bi bi-x-circle-fill"></i> Senhas não coincidem';
            }
        }
    </script>
</div>