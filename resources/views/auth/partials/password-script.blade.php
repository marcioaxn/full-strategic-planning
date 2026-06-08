{{-- Validação reativa e instantânea de senha (cliente), robusta a re-renders do Livewire.
     Usa delegação de eventos no document, então não quebra após "morph".
     Container marcado com [data-pwd-scope] contendo:
       [data-pwd-input]          → nova senha
       [data-pwd-confirm]        → confirmação (opcional)
       [data-pwd-require-filled] → campos que também devem estar preenchidos (ex.: senha atual)
       [data-pwd-submit]         → botão de envio (habilita só quando tudo válido)
     Painel de requisitos: partials/password-checklist. --}}
<script>
(function () {
    var LABELS = ['Muito fraca', 'Muito fraca', 'Fraca', 'Razoável', 'Boa', 'Forte'];
    var COLORS = ['#dc3545', '#dc3545', '#fb923c', '#f59e0b', '#16a34a', '#22a06b'];

    function evaluate(scope) {
        var input = scope.querySelector('[data-pwd-input]');
        if (!input) return;
        var confirm = scope.querySelector('[data-pwd-confirm]');
        var submit  = scope.querySelector('[data-pwd-submit]');
        var matchEl = scope.querySelector('[data-pwd-match]');
        var fill    = scope.querySelector('[data-pwd-fill]');
        var label   = scope.querySelector('[data-pwd-strength]');
        var reqEls  = {
            length:  scope.querySelector('[data-req="length"]'),
            upper:   scope.querySelector('[data-req="upper"]'),
            lower:   scope.querySelector('[data-req="lower"]'),
            number:  scope.querySelector('[data-req="number"]'),
            special: scope.querySelector('[data-req="special"]')
        };

        var v = input.value || '';
        var checks = {
            length:  v.length >= 8,
            upper:   /[A-Z]/.test(v),
            lower:   /[a-z]/.test(v),
            number:  /[0-9]/.test(v),
            special: /[^A-Za-z0-9]/.test(v)
        };

        var passed = 0;
        Object.keys(checks).forEach(function (k) {
            if (reqEls[k]) reqEls[k].classList.toggle('ok', checks[k]);
            if (checks[k]) passed++;
        });

        if (fill)  { fill.style.width = (passed / 5 * 100) + '%'; fill.style.background = COLORS[passed]; }
        if (label) { label.textContent = v.length ? ('Força: ' + LABELS[passed]) : 'Força da senha'; }

        var allReqsOk = (passed === 5);

        var matchOk = true;
        if (confirm) {
            var c = confirm.value || '';
            if (c.length) {
                matchOk = (c === v);
                if (matchEl) {
                    matchEl.classList.add('show');
                    matchEl.classList.toggle('ok', matchOk);
                    matchEl.classList.toggle('no', !matchOk);
                    matchEl.innerHTML = matchOk
                        ? '<i class="bi bi-check-circle-fill"></i> As senhas coincidem'
                        : '<i class="bi bi-x-circle-fill"></i> As senhas não coincidem';
                }
            } else {
                if (matchEl) { matchEl.classList.remove('show', 'ok', 'no'); matchEl.innerHTML = ''; }
                matchOk = false;
            }
        }

        var extrasOk = Array.prototype.slice
            .call(scope.querySelectorAll('[data-pwd-require-filled]'))
            .every(function (el) { return (el.value || '').trim().length > 0; });

        if (submit) submit.disabled = !(allReqsOk && matchOk && extrasOk);
    }

    function evaluateAll() {
        document.querySelectorAll('[data-pwd-scope]').forEach(evaluate);
    }

    // Delegação: sobrevive a re-renders do Livewire (morph) e navegação.
    document.addEventListener('input', function (e) {
        var scope = e.target.closest && e.target.closest('[data-pwd-scope]');
        if (scope) evaluate(scope);
    });
    document.addEventListener('DOMContentLoaded', evaluateAll);
    document.addEventListener('livewire:navigated', evaluateAll);
    if (window.Livewire) document.addEventListener('livewire:init', function () {
        Livewire.hook('morph.updated', evaluateAll);
    });
    evaluateAll();
})();

// Alterna a visibilidade de um campo de senha (ícone olho).
function lpToggleField(inputId, iconId) {
    var input = document.getElementById(inputId);
    var icon  = document.getElementById(iconId);
    if (!input) return;
    if (input.type === 'password') { input.type = 'text';  if (icon) icon.className = 'bi bi-eye-slash'; }
    else                           { input.type = 'password'; if (icon) icon.className = 'bi bi-eye'; }
}
</script>
