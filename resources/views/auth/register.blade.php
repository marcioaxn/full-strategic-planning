<x-guest-layout>
<div class="lp-login">

    {{-- Blobs decorativos --}}
    <div class="lp-blob" style="width:520px;height:520px;background:#4361EE;top:-140px;right:-100px;filter:blur(90px);opacity:.18;"></div>
    <div class="lp-blob" style="width:380px;height:380px;background:#e07b39;bottom:40px;left:30%;filter:blur(80px);opacity:.15;"></div>
    <div class="lp-blob" style="width:260px;height:260px;background:#22a06b;top:38%;left:-70px;filter:blur(70px);opacity:.2;"></div>

    {{-- Painel institucional (esquerda) --}}
    @include('auth.partials.auth-left-panel', [
        'tituloHtml' => 'Crie sua conta e<br>planeje com<br><span>metodologia</span>.',
        'lead' => 'Junte-se aos gestores que utilizam o sistema para governança estratégica e monitoramento em tempo real. Defina uma senha forte — os requisitos são validados automaticamente.',
    ])

    {{-- Formulário (direita) --}}
    <div class="lp-login-right d-flex align-items-center justify-content-center p-4 p-lg-5">
        <div class="lp-login-card">

            {{-- Logo mobile --}}
            <div class="d-xl-none text-center mb-4">
                <div class="d-inline-flex align-items-center gap-2 mb-1">
                    <div style="width:38px;height:38px;background:linear-gradient(135deg,#1a3a5c,#1B408E);border-radius:.75rem;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-diagram-3 text-white"></i>
                    </div>
                    <span class="fw-bold" style="font-size:1.1rem;color:#0d1b2e;letter-spacing:-.02em;">SEAE</span>
                </div>
            </div>

            {{-- Cabeçalho --}}
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h2 class="lp-form-title mb-1">Criar sua conta</h2>
                    <p class="text-muted mb-0" style="font-size:.88rem;line-height:1.5;">Preencha os dados para iniciar.</p>
                </div>
                <a href="{{ url('/') }}" class="btn btn-sm rounded-pill px-3 ms-3 flex-shrink-0"
                   style="font-size:.75rem;border:1.5px solid #e2e8f0;color:#64748b;background:#f8fafc;white-space:nowrap;font-weight:600;">
                    <i class="bi bi-arrow-left me-1"></i>Início
                </a>
            </div>

            {{-- Erros --}}
            @if ($errors->any())
                <div class="lp-alert lp-alert-error mb-4" id="regErros">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                    <div>
                        @foreach ($errors->all() as $erro)
                            <div>{{ $erro }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="registerForm" data-pwd-scope>
                @csrf

                {{-- Nome --}}
                <div class="mb-4">
                    <label class="lp-field-label" for="name">Nome Completo</label>
                    <div class="lp-field-wrap">
                        <i class="bi bi-person lp-field-icon"></i>
                        <input type="text" name="name" id="name"
                               class="lp-field-input @error('name') lp-field-error @enderror"
                               value="{{ old('name') }}" placeholder="Seu nome completo" required autofocus
                               autocomplete="name" data-pwd-require-filled>
                    </div>
                </div>

                {{-- E-mail --}}
                <div class="mb-4">
                    <label class="lp-field-label" for="email">E-mail Corporativo</label>
                    <div class="lp-field-wrap">
                        <i class="bi bi-envelope lp-field-icon"></i>
                        <input type="email" name="email" id="email"
                               class="lp-field-input @error('email') lp-field-error @enderror"
                               value="{{ old('email') }}" placeholder="seu@orgao.gov.br" required
                               autocomplete="username" data-pwd-require-filled>
                    </div>
                </div>

                {{-- Senha --}}
                <div class="mb-3">
                    <label class="lp-field-label" for="password">Senha de Acesso</label>
                    <div class="lp-field-wrap">
                        <i class="bi bi-lock lp-field-icon"></i>
                        <input type="password" name="password" id="password"
                               class="lp-field-input @error('password') lp-field-error @enderror"
                               placeholder="Crie uma senha forte" required autocomplete="new-password"
                               data-pwd-input>
                        <button type="button" class="lp-pass-toggle" onclick="lpToggleField('password','icPwd')" title="Mostrar/ocultar">
                            <i class="bi bi-eye" id="icPwd"></i>
                        </button>
                    </div>
                    @include('auth.partials.password-checklist')
                </div>

                {{-- Confirmar senha --}}
                <div class="mb-4">
                    <label class="lp-field-label" for="password_confirmation">Confirmar Senha</label>
                    <div class="lp-field-wrap">
                        <i class="bi bi-shield-lock lp-field-icon"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="lp-field-input" placeholder="Repita sua senha" required
                               autocomplete="new-password" data-pwd-confirm>
                        <button type="button" class="lp-pass-toggle" onclick="lpToggleField('password_confirmation','icPwdC')" title="Mostrar/ocultar">
                            <i class="bi bi-eye" id="icPwdC"></i>
                        </button>
                    </div>
                </div>

                {{-- Termos --}}
                @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mb-4">
                    <label class="d-flex align-items-start gap-2" style="cursor:pointer;user-select:none;">
                        <input type="checkbox" name="terms" id="terms" required
                               class="form-check-input m-0 mt-1" style="width:1.05rem;height:1.05rem;flex-shrink:0;border-color:#cbd5e1;border-radius:.3rem;">
                        <span style="font-size:.82rem;color:#64748b;font-weight:500;line-height:1.45;">
                            {!! __('Li e concordo com os :terms e a :policy', [
                                'terms' => '<a target="_blank" href="'.route('terms.show').'" style="color:#1B408E;font-weight:700;text-decoration:none;">Termos de Uso</a>',
                                'policy' => '<a target="_blank" href="'.route('policy.show').'" style="color:#1B408E;font-weight:700;text-decoration:none;">Política de Privacidade</a>',
                            ]) !!}
                        </span>
                    </label>
                </div>
                @endif

                {{-- Botão --}}
                <button type="submit" class="lp-submit-btn w-100 mb-4" id="registerButton" data-pwd-submit disabled>
                    <span class="lp-btn-text" style="display:flex;align-items:center;gap:.5rem;">
                        <i class="bi bi-person-plus-fill"></i>Finalizar Cadastro
                    </span>
                    <span class="lp-btn-loading" style="display:none;align-items:center;gap:.5rem;">
                        <span class="spinner-border spinner-border-sm"></span>Cadastrando...
                    </span>
                </button>

                <p class="text-center mb-0" style="font-size:.85rem;color:#64748b;">
                    Já possui uma conta?
                    <a href="{{ route('login') }}" style="color:#1B408E;font-weight:700;text-decoration:none;">Fazer login</a>
                </p>
            </form>

            {{-- Selo --}}
            <div class="lp-secure-seal">
                <i class="bi bi-shield-check" style="color:#22a06b;font-size:1rem;flex-shrink:0;"></i>
                <span style="font-size:.72rem;color:#64748b;font-weight:500;">Conexão segura · criptografia ponta a ponta</span>
            </div>
        </div>
    </div>
</div>

@include('auth.partials.auth-styles')
@include('auth.partials.password-script')

<script>
    (function () {
        var form = document.getElementById('registerForm');
        var btn  = document.getElementById('registerButton');
        if (!form || !btn) return;
        var txt = btn.querySelector('.lp-btn-text');
        var load = btn.querySelector('.lp-btn-loading');

        @if ($errors->any())
            var box = document.getElementById('regErros');
            if (box) { box.classList.add('lp-shake'); setTimeout(function(){ box.classList.remove('lp-shake'); }, 500); }
        @endif

        form.addEventListener('submit', function () {
            if (btn.disabled || !form.checkValidity()) return;
            if (txt)  txt.style.display = 'none';
            if (load) load.style.display = 'flex';
            setTimeout(function () { btn.disabled = true; }, 0);
        });
    })();
</script>
</x-guest-layout>
