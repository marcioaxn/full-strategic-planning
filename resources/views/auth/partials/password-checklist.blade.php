{{-- Painel reativo de requisitos de senha. Sempre visível, atualiza em tempo real
     conforme o usuário digita (via partials/password-script). --}}
<div class="pwd-panel" data-pwd-panel>
    <div class="pwd-strength-track"><div class="pwd-strength-fill" data-pwd-fill></div></div>
    <div class="pwd-strength-label" data-pwd-strength>Força da senha</div>
    <div class="pwd-reqs">
        <div class="pwd-req" data-req="length"><span class="pwd-req-ic"><i class="bi bi-check"></i></span>Mínimo de 8 caracteres</div>
        <div class="pwd-req" data-req="upper"><span class="pwd-req-ic"><i class="bi bi-check"></i></span>Uma letra maiúscula</div>
        <div class="pwd-req" data-req="lower"><span class="pwd-req-ic"><i class="bi bi-check"></i></span>Uma letra minúscula</div>
        <div class="pwd-req" data-req="number"><span class="pwd-req-ic"><i class="bi bi-check"></i></span>Um número</div>
        <div class="pwd-req" data-req="special"><span class="pwd-req-ic"><i class="bi bi-check"></i></span>Um caractere especial</div>
    </div>
    <div class="pwd-match" data-pwd-match></div>
</div>
