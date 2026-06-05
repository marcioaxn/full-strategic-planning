<x-guest-layout>
<div class="lp-login">

    @include('auth.partials.auth-left-panel', [
        'tituloHtml' => 'Cadastre sua senha<br>com <span>seguranca</span><br>e clareza.',
        'lead' => 'Este link confirma sua identidade e permite criar a senha pessoal de acesso. A senha e validada em tempo real com os mesmos criterios usados no cadastro institucional.',
    ])

    <div class="lp-login-right lp-reset-right d-flex justify-content-center p-4 p-lg-5">
        <div class="lp-login-card">

            <div class="d-xl-none text-center mb-4">
                <div class="lp-mobile-brand">
                    <div class="lp-mobile-brand-icon">
                        <i class="bi bi-diagram-3 text-white"></i>
                    </div>
                    <span class="lp-mobile-brand-text">SEAE</span>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <div class="lp-mini-kicker mb-2">
                        <i class="bi bi-shield-lock-fill"></i>
                        Validacao de acesso
                    </div>
                    <h2 class="lp-form-title mb-1">Cadastrar senha</h2>
                    <p class="text-muted mb-0 lp-form-subtitle">
                        Defina sua senha pessoal para concluir o acesso ao sistema.
                    </p>
                </div>
                <a href="{{ route('login') }}" class="lp-pill-link ms-3 flex-shrink-0">
                    <i class="bi bi-arrow-left"></i>
                    Login
                </a>
            </div>

            <div class="lp-alert lp-alert-info mb-4">
                <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                <div>
                    <strong>Link individual.</strong>
                    Use o e-mail que recebeu o convite. O sistema nunca envia senha por e-mail.
                </div>
            </div>

            @if ($errors->any())
                <div class="lp-alert lp-alert-error mb-4" id="resetErros">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                    <div>
                        @foreach ($errors->all() as $erro)
                            <div>{{ $erro }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm" data-pwd-scope>
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="mb-4">
                    <label class="lp-field-label" for="email">E-mail do convite</label>
                    <div class="lp-field-wrap">
                        <i class="bi bi-envelope-check lp-field-icon"></i>
                        <input type="email" name="email" id="email"
                               class="lp-field-input @error('email') lp-field-error @enderror"
                               value="{{ old('email', $request->email) }}"
                               placeholder="seu@orgao.gov.br" required autofocus
                               autocomplete="username" data-pwd-require-filled>
                    </div>
                    @error('email')
                        <div class="lp-field-help lp-field-help-error">{{ $message }}</div>
                    @else
                        <div class="lp-field-help">Informe exatamente o e-mail que recebeu o link de acesso.</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="lp-field-label" for="password">Nova senha</label>
                    <div class="lp-field-wrap">
                        <i class="bi bi-lock lp-field-icon"></i>
                        <input type="password" name="password" id="password"
                               class="lp-field-input @error('password') lp-field-error @enderror"
                               placeholder="Crie uma senha forte" required autocomplete="new-password"
                               data-pwd-input>
                        <button type="button" class="lp-pass-toggle" onclick="lpToggleField('password','icPwdReset')" title="Mostrar ou ocultar senha">
                            <i class="bi bi-eye" id="icPwdReset"></i>
                        </button>
                    </div>
                    @include('auth.partials.password-checklist')
                    @error('password')
                        <div class="lp-field-help lp-field-help-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="lp-field-label" for="password_confirmation">Confirmar nova senha</label>
                    <div class="lp-field-wrap">
                        <i class="bi bi-shield-check lp-field-icon"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="lp-field-input" placeholder="Repita a senha criada" required
                               autocomplete="new-password" data-pwd-confirm>
                        <button type="button" class="lp-pass-toggle" onclick="lpToggleField('password_confirmation','icPwdResetConfirm')" title="Mostrar ou ocultar confirmacao">
                            <i class="bi bi-eye" id="icPwdResetConfirm"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="lp-submit-btn w-100" id="resetPasswordButton" data-pwd-submit disabled>
                    <span class="lp-btn-text">
                        <i class="bi bi-check2-circle"></i>
                        Salvar senha e acessar
                    </span>
                    <span class="lp-btn-loading">
                        <span class="spinner-border spinner-border-sm"></span>
                        Validando...
                    </span>
                </button>
            </form>

            <div class="lp-secure-seal">
                <i class="bi bi-shield-check"></i>
                <span>Senha criptografada e validada pelo padrao institucional do sistema</span>
            </div>
        </div>
    </div>
</div>

@include('auth.partials.auth-styles')
@include('auth.partials.password-script')

<script>
    (function () {
        var form = document.getElementById('resetPasswordForm');
        var btn = document.getElementById('resetPasswordButton');
        if (!form || !btn) return;

        var txt = btn.querySelector('.lp-btn-text');
        var load = btn.querySelector('.lp-btn-loading');

        @if ($errors->any())
            var box = document.getElementById('resetErros');
            if (box) {
                box.classList.add('lp-shake');
                setTimeout(function () { box.classList.remove('lp-shake'); }, 500);
            }
        @endif

        form.addEventListener('submit', function () {
            if (btn.disabled || !form.checkValidity()) return;
            if (txt) txt.style.display = 'none';
            if (load) load.style.display = 'flex';
            setTimeout(function () { btn.disabled = true; }, 0);
        });
    })();
</script>
</x-guest-layout>
