<div>
<div class="lp-login">

    {{-- Blobs decorativos --}}
    <div class="lp-blob" style="width:520px;height:520px;background:#4361EE;top:-140px;right:-100px;filter:blur(90px);opacity:.18;"></div>
    <div class="lp-blob" style="width:380px;height:380px;background:#e07b39;bottom:40px;left:30%;filter:blur(80px);opacity:.15;"></div>
    <div class="lp-blob" style="width:260px;height:260px;background:#22a06b;top:38%;left:-70px;filter:blur(70px);opacity:.2;"></div>

    {{-- Painel institucional (esquerda) --}}
    @include('auth.partials.auth-left-panel', [
        'tituloHtml' => 'Atualize sua senha<br>e mantenha sua conta<br><span>protegida</span>.',
        'lead' => 'Por segurança, defina uma nova senha forte para continuar. Siga os requisitos ao lado — eles são validados automaticamente enquanto você digita.',
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
            <div class="mb-4">
                <span class="lp-hero-badge mb-3"><i class="bi bi-shield-lock-fill" style="font-size:.85rem;color:#e07b39;"></i> Segurança da Conta</span>
                <h2 class="lp-form-title mb-1 mt-3">Troca de Senha</h2>
                <p class="text-muted mb-0" style="font-size:.88rem;line-height:1.5;">
                    Você precisa definir uma nova senha para continuar acessando o sistema.
                </p>
            </div>

            {{-- Erros de validação --}}
            @if ($errors->any())
                <div class="lp-alert lp-alert-error mb-4" id="trocaErros">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                    <div>
                        @foreach ($errors->all() as $erro)
                            <div>{{ $erro }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form wire:submit.prevent="trocarSenha" data-pwd-scope>

                {{-- Senha atual --}}
                <div class="mb-4">
                    <label class="lp-field-label" for="senhaAtual">Senha Atual</label>
                    <div class="lp-field-wrap">
                        <i class="bi bi-key lp-field-icon"></i>
                        <input type="password" id="senhaAtual" wire:model="senhaAtual"
                               class="lp-field-input @error('senhaAtual') lp-field-error @enderror"
                               placeholder="Sua senha atual" autocomplete="current-password" autofocus
                               data-pwd-require-filled>
                        <button type="button" class="lp-pass-toggle" onclick="lpToggleField('senhaAtual','icSenhaAtual')" title="Mostrar/ocultar">
                            <i class="bi bi-eye" id="icSenhaAtual"></i>
                        </button>
                    </div>
                    @error('senhaAtual') <span class="d-block mt-1" style="font-size:.78rem;color:#dc2626;font-weight:600;">{{ $message }}</span> @enderror
                </div>

                {{-- Nova senha --}}
                <div class="mb-3">
                    <label class="lp-field-label" for="novaSenha">Nova Senha</label>
                    <div class="lp-field-wrap">
                        <i class="bi bi-lock lp-field-icon"></i>
                        <input type="password" id="novaSenha" wire:model="novaSenha"
                               class="lp-field-input @error('novaSenha') lp-field-error @enderror"
                               placeholder="Crie uma senha forte" autocomplete="new-password"
                               data-pwd-input>
                        <button type="button" class="lp-pass-toggle" onclick="lpToggleField('novaSenha','icNovaSenha')" title="Mostrar/ocultar">
                            <i class="bi bi-eye" id="icNovaSenha"></i>
                        </button>
                    </div>

                    {{-- Checklist reativo (ignorado pelo Livewire; atualizado via JS) --}}
                    <div wire:ignore>
                        @include('auth.partials.password-checklist')
                    </div>
                </div>

                {{-- Confirmação --}}
                <div class="mb-4">
                    <label class="lp-field-label" for="novaSenha_confirmation">Confirmar Nova Senha</label>
                    <div class="lp-field-wrap">
                        <i class="bi bi-lock-fill lp-field-icon"></i>
                        <input type="password" id="novaSenha_confirmation" wire:model="novaSenha_confirmation"
                               class="lp-field-input" placeholder="Repita a nova senha" autocomplete="new-password"
                               data-pwd-confirm>
                        <button type="button" class="lp-pass-toggle" onclick="lpToggleField('novaSenha_confirmation','icConfirma')" title="Mostrar/ocultar">
                            <i class="bi bi-eye" id="icConfirma"></i>
                        </button>
                    </div>
                </div>

                {{-- Botão --}}
                <button type="submit" class="lp-submit-btn w-100 mb-3" data-pwd-submit disabled wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="trocarSenha"><i class="bi bi-shield-check"></i> Atualizar Senha</span>
                    <span wire:loading wire:target="trocarSenha"><span class="spinner-border spinner-border-sm"></span> Processando...</span>
                </button>

                {{-- Sair --}}
                <div class="text-center">
                    <button type="button" wire:click="logout" class="lp-secondary-btn">
                        <i class="bi bi-box-arrow-right me-1"></i>Sair e trocar depois
                    </button>
                </div>
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

@if ($errors->any())
<script>
    (function () {
        var box = document.getElementById('trocaErros');
        if (box) { box.classList.add('lp-shake'); setTimeout(function(){ box.classList.remove('lp-shake'); }, 500); }
    })();
</script>
@endif
</div>{{-- /raiz única do componente Livewire --}}
