<x-guest-layout>
<div class="lp-login">

    {{-- ── Floating blobs (idênticos ao hero da landing page) ─────────────── --}}
    <div class="lp-blob" style="width:520px;height:520px;background:#4361EE;top:-140px;right:-100px;filter:blur(90px);opacity:.18;"></div>
    <div class="lp-blob" style="width:380px;height:380px;background:#e07b39;bottom:40px;left:30%;filter:blur(80px);opacity:.15;"></div>
    <div class="lp-blob" style="width:260px;height:260px;background:#22a06b;top:38%;left:-70px;filter:blur(70px);opacity:.2;"></div>

    {{-- ══════════════════════════════════════════════════════════════════════
         LADO ESQUERDO — visual idêntico ao hero da landing page
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="lp-login-left d-none d-xl-flex flex-column justify-content-between p-5">

        {{-- Brand --}}
        <a href="{{ url('/') }}" class="d-flex align-items-center gap-3 text-decoration-none">
            <div style="width:46px;height:46px;background:linear-gradient(135deg,#1B408E,#2e5aa8);border-radius:.875rem;
                        display:flex;align-items:center;justify-content:center;
                        box-shadow:0 4px 12px rgba(27,64,142,.25);">
                <i class="bi bi-diagram-3 text-white" style="font-size:1.3rem;"></i>
            </div>
            <div>
                <div class="lp-login-bname">SEAE</div>
                <div class="lp-login-bsub">Sistema de Gestão Estratégica</div>
            </div>
        </a>

        {{-- Centro --}}
        <div>
            {{-- Badge pill (mesmo do hero LP) --}}
            <div class="lp-hero-badge mb-4">
                <i class="bi bi-patch-check-fill" style="font-size:.9rem;color:#e07b39;"></i>
                GPPEI/MGI 2025 · Gestão Pública Federal
            </div>

            {{-- Título com gradient text (mesmo estilo LP) --}}
            <h1 class="lp-login-title mb-3">
                Bem-vindo à sua<br>
                central de <span>governança</span><br>
                estratégica.
            </h1>
            <p class="lp-login-lead mb-5">
                Gerencie ciclos PEI, monitore indicadores SMART e execute planos de ação
                com o rigor metodológico que a Administração Pública Federal exige.
            </p>

            {{-- Cards de módulo empilhados (cópia exata do hero LP) --}}
            <div class="position-relative">
                @foreach([
                    ['num'=>'01','title'=>'Inaugurar e Integrar','sub'=>'Planejar o processo · PPA/LOA/ODS','icon'=>'flag-fill','c'=>'#1a3a5c','tx'=>0],
                    ['num'=>'02','title'=>'Planejar','sub'=>'Cadeia de Valor · SWOT · BSC · Planos','icon'=>'diagram-3-fill','c'=>'#1B408E','tx'=>28],
                    ['num'=>'03','title'=>'Monitorar e Avaliar','sub'=>'Dashboard · Indicadores · RAE','icon'=>'graph-up-arrow','c'=>'#2e8b57','tx'=>56],
                ] as $m)
                <div class="lp-login-modcard d-flex align-items-center gap-3 mb-2 p-3 rounded-3"
                     style="border-left:4px solid {{ $m['c'] }};
                            transform:translateX({{ $m['tx'] }}px);max-width:390px;">
                    <div style="min-width:42px;height:42px;background:{{ $m['c'] }};
                                border-radius:.75rem;display:flex;align-items:center;
                                justify-content:center;flex-shrink:0;">
                        <i class="bi bi-{{ $m['icon'] }} text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <div class="lp-stack-eyebrow">Módulo {{ $m['num'] }}</div>
                        <div class="lp-stack-title">{{ $m['title'] }}</div>
                        <div class="lp-stack-sub">{{ $m['sub'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Stats bar (cópia do hero LP) --}}
        <div>
            <div class="lp-stats mb-3">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="lp-stat-value">3</div>
                        <div class="lp-stat-label">Módulos GPPEI</div>
                    </div>
                    <div class="col-4">
                        <div class="lp-stat-value">20+</div>
                        <div class="lp-stat-label">Ferramentas</div>
                    </div>
                    <div class="col-4">
                        <div class="lp-stat-value">100%</div>
                        <div class="lp-stat-label">Metodologia APF</div>
                    </div>
                </div>
            </div>
            <p style="font-size:.7rem;color:#94a3b8;margin:0;">
                &copy; {{ date('Y') }} Strategic Planning System. Todos os direitos reservados.
            </p>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         LADO DIREITO — formulário de login
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="lp-login-right d-flex align-items-center justify-content-center p-4 p-lg-5">
        <div class="lp-login-card">

            {{-- Logo mobile (apenas < xl) --}}
            <div class="d-xl-none text-center mb-5">
                <div class="d-inline-flex align-items-center gap-2 mb-1">
                    <div style="width:38px;height:38px;background:linear-gradient(135deg,#1a3a5c,#1B408E);
                                border-radius:.75rem;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-diagram-3 text-white"></i>
                    </div>
                    <span class="fw-bold" style="font-size:1.1rem;color:#0d1b2e;letter-spacing:-.02em;">SEAE</span>
                </div>
                <div style="font-size:.65rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">
                    Sistema de Gestão Estratégica
                </div>
            </div>

            {{-- Cabeçalho do form --}}
            <div class="d-flex justify-content-between align-items-start mb-5">
                <div>
                    <h2 class="lp-form-title mb-1">Entrar no Sistema</h2>
                    <p class="text-muted mb-0" style="font-size:.88rem;line-height:1.5;">
                        Acesse com suas credenciais institucionais.
                    </p>
                </div>
                <a href="{{ url('/') }}"
                   class="btn btn-sm rounded-pill px-3 ms-3 flex-shrink-0"
                   style="font-size:.75rem;border:1.5px solid #e2e8f0;color:#64748b;background:#f8fafc;
                          white-space:nowrap;font-weight:600;transition:all .2s;"
                   onmouseover="this.style.borderColor='#1B408E';this.style.color='#1B408E'"
                   onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#64748b'">
                    <i class="bi bi-arrow-left me-1"></i>Início
                </a>
            </div>

            {{-- Validações --}}
            <x-validation-errors class="mb-4" />

            @session('status')
            <div class="d-flex align-items-center gap-2 rounded-3 mb-4 p-3"
                 style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;" role="alert">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <span style="font-size:.85rem;font-weight:500;">{{ $value }}</span>
            </div>
            @endsession

            {{-- ── FORMULÁRIO ── --}}
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                {{-- E-mail --}}
                <div class="mb-4">
                    <label class="lp-field-label" for="email">E-mail Corporativo</label>
                    <div class="lp-field-wrap">
                        <i class="bi bi-envelope lp-field-icon"></i>
                        <input type="email"
                               name="email"
                               id="email"
                               class="lp-field-input @error('email') lp-field-error @enderror"
                               value="{{ old('email') }}"
                               placeholder="seu@orgao.gov.br"
                               required autofocus autocomplete="username">
                    </div>
                </div>

                {{-- Senha --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="lp-field-label mb-0" for="password">Senha de Acesso</label>
                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="lp-forgot">
                            Esqueci minha senha
                        </a>
                        @endif
                    </div>
                    <div class="lp-field-wrap">
                        <i class="bi bi-lock lp-field-icon"></i>
                        <input type="password"
                               name="password"
                               id="password"
                               class="lp-field-input @error('password') lp-field-error @enderror"
                               placeholder="••••••••"
                               required autocomplete="current-password">
                        <button type="button" class="lp-pass-toggle" id="passToggle"
                                onclick="lpTogglePass()" title="Mostrar/ocultar senha">
                            <i class="bi bi-eye" id="passIcon"></i>
                        </button>
                    </div>
                </div>

                {{-- Manter conectado --}}
                <div class="mb-5">
                    <label class="d-flex align-items-center gap-2" style="cursor:pointer;user-select:none;">
                        <input type="checkbox" name="remember" id="remember_me"
                               class="form-check-input m-0"
                               style="width:1.05rem;height:1.05rem;cursor:pointer;
                                      border-color:#cbd5e1;border-radius:.3rem;">
                        <span style="font-size:.85rem;color:#64748b;font-weight:500;">
                            Manter conectado por 30 dias
                        </span>
                    </label>
                </div>

                {{-- Botão (mesmo estilo CTA laranja da LP) --}}
                <button type="submit" class="lp-submit-btn w-100 mb-4" id="loginButton">
                    <span class="lp-btn-text" style="display:flex;align-items:center;gap:.5rem;">
                        <i class="bi bi-box-arrow-in-right"></i>Acessar o Sistema
                    </span>
                    <span class="lp-btn-loading" style="display:none;align-items:center;gap:.5rem;">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Validando credenciais...
                    </span>
                </button>

                @if (Route::has('register'))
                <p class="text-center mb-0" style="font-size:.85rem;color:#64748b;">
                    Ainda não tem acesso?
                    <a href="{{ route('register') }}"
                       style="color:#1B408E;font-weight:700;text-decoration:none;">
                        Criar conta
                    </a>
                </p>
                @endif
            </form>

            {{-- Selo de segurança --}}
            <div class="lp-secure-seal">
                <i class="bi bi-shield-check" style="color:#22a06b;font-size:1rem;flex-shrink:0;"></i>
                <span style="font-size:.72rem;color:#64748b;font-weight:500;">
                    Conexão segura · criptografia ponta a ponta
                </span>
            </div>

        </div>
    </div>
</div>

{{-- ══════════════ ESTILOS ══════════════ --}}
<style>
/* ── Base ─────────────────────────────────────────────────────────────────── */
.lp-login {
    position: fixed; inset: 0; z-index: 100;
    display: flex;
    font-family: 'Figtree', 'Inter', system-ui, sans-serif;
    background: linear-gradient(135deg, #cfe0f6 0%, #dde9fa 40%, #e9f1fc 70%, #f4f8fd 100%);
    overflow: hidden;
}
.lp-login::before {
    content: '';
    position: absolute; inset: 0; pointer-events: none;
    background:
        radial-gradient(ellipse 80% 60% at 70% 40%, rgba(67,97,238,.10) 0%, transparent 60%),
        radial-gradient(ellipse 50% 50% at 20% 80%, rgba(224,123,57,.08) 0%, transparent 55%);
}
.lp-blob {
    position: absolute; border-radius: 50%; pointer-events: none;
}

/* ── Layout ───────────────────────────────────────────────────────────────── */
.lp-login-left {
    width: 56%;
    position: relative; z-index: 1;
    overflow: hidden;
}
.lp-login-right {
    flex: 1;
    position: relative; z-index: 1;
    background: rgba(255,255,255,.05);
    backdrop-filter: blur(2px);
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
}
@media (max-width: 1199.98px) {
    .lp-login-left { display: none !important; }
    .lp-login-right { flex: 1; background: transparent; backdrop-filter: none; }
}

/* ── Card do formulário ───────────────────────────────────────────────────── */
.lp-login-card {
    width: 100%; max-width: 448px;
    background: #fff;
    border-radius: 1.5rem;
    padding: 2.5rem;
    box-shadow:
        0 24px 56px rgba(27,64,142,.16),
        0 8px 24px rgba(27,64,142,.10),
        0 0 0 1px rgba(27,64,142,.05);
    animation: lp-slide-up .45s cubic-bezier(.22,1,.36,1) both;
}
@keyframes lp-slide-up {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Hero badge (idêntico à landing page) ─────────────────────────────────── */
.lp-hero-badge {
    display: inline-flex; align-items: center; gap: .5rem;
    background: rgba(27,64,142,.08);
    border: 1px solid rgba(27,64,142,.18);
    backdrop-filter: blur(8px);
    border-radius: 999px;
    padding: .35rem 1rem;
    font-size: .78rem; font-weight: 700;
    color: #1B408E;
    letter-spacing: .04em;
    text-transform: uppercase;
}

/* ── Título com gradient text (idêntico à landing page) ──────────────────── */
.lp-login-title {
    font-size: clamp(1.85rem, 2.8vw, 2.75rem);
    font-weight: 800;
    line-height: 1.15;
    color: #16306b;
    letter-spacing: -.03em;
}
.lp-login-title span {
    background: linear-gradient(90deg, #1B408E, #e07b39);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* ── Stats bar (idêntico à landing page) ─────────────────────────────────── */
.lp-stats {
    background: rgba(255,255,255,.7);
    border: 1px solid rgba(27,64,142,.12);
    backdrop-filter: blur(12px);
    border-radius: 1.25rem;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 8px 24px rgba(27,64,142,.08);
}
.lp-stat-value { font-size: 1.75rem; font-weight: 800; color: #16306b; line-height: 1; }
.lp-stat-label { font-size: .7rem; color: #64748b; text-transform: uppercase; letter-spacing: .05em; margin-top: .2rem; }

/* ── Título do form ───────────────────────────────────────────────────────── */
.lp-form-title {
    font-size: 1.65rem; font-weight: 800;
    color: #0d1b2e; letter-spacing: -.03em; line-height: 1.15;
}

/* ── Labels ───────────────────────────────────────────────────────────────── */
.lp-field-label {
    display: block;
    font-size: .7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em;
    color: #64748b; margin-bottom: .5rem;
}

/* ── Inputs ───────────────────────────────────────────────────────────────── */
.lp-field-wrap { position: relative; }
.lp-field-icon {
    position: absolute; left: 1rem; top: 50%;
    transform: translateY(-50%);
    color: #94a3b8; font-size: 1rem;
    pointer-events: none; z-index: 1;
    transition: color .2s;
}
.lp-field-input {
    display: block; width: 100%;
    padding: .9rem 1rem .9rem 2.8rem;
    font-size: .95rem; font-weight: 500;
    font-family: inherit;
    color: #0d1b2e;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: .875rem;
    outline: none;
    transition: border-color .25s, box-shadow .25s, background .25s;
    appearance: none;
    -webkit-appearance: none;
}
.lp-field-input:focus {
    border-color: #1B408E;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(27,64,142,.09);
}
.lp-field-input:focus ~ .lp-field-icon,
.lp-field-wrap:focus-within .lp-field-icon { color: #1B408E; }
.lp-field-input::placeholder { color: #94a3b8; }
.lp-field-error { border-color: #dc3545 !important; }
.lp-field-input:focus.lp-field-error { box-shadow: 0 0 0 4px rgba(220,53,69,.09); }

/* ── Toggle senha ─────────────────────────────────────────────────────────── */
.lp-pass-toggle {
    position: absolute; right: .875rem; top: 50%;
    transform: translateY(-50%);
    background: none; border: none;
    color: #94a3b8; cursor: pointer;
    padding: .25rem; z-index: 2;
    font-size: 1.05rem; line-height: 1;
    display: flex; align-items: center;
    transition: color .2s;
}
.lp-pass-toggle:hover { color: #1B408E; }

/* ── Link "Esqueci" ───────────────────────────────────────────────────────── */
.lp-forgot {
    font-size: .72rem; font-weight: 700;
    color: #1B408E; text-decoration: none;
    text-transform: uppercase; letter-spacing: .04em;
    transition: opacity .2s;
}
.lp-forgot:hover { opacity: .7; color: #1B408E; }

/* ── Botão submit — laranja igual ao CTA da landing page ─────────────────── */
.lp-submit-btn {
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #e07b39 0%, #f5a623 100%);
    border: none; color: #fff;
    font-weight: 700; font-size: 1rem;
    font-family: inherit;
    padding: .95rem 2rem;
    border-radius: 999px;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(224,123,57,.4);
    transition: all .25s ease;
    letter-spacing: -.01em;
}
.lp-submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 32px rgba(224,123,57,.52);
}
.lp-submit-btn:active { transform: translateY(0); }
.lp-submit-btn:disabled { opacity: .75; cursor: not-allowed; transform: none; }
.lp-submit-btn.is-loading { opacity: .9; cursor: progress; box-shadow: 0 6px 18px rgba(224,123,57,.35); }

/* ── Selo de segurança ────────────────────────────────────────────────────── */
.lp-secure-seal {
    margin-top: 1.5rem;
    padding: .8rem 1rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: .875rem;
    display: flex; align-items: center;
    justify-content: center; gap: .5rem;
}

/* ── Shake em erros ───────────────────────────────────────────────────────── */
@keyframes lp-shake {
    0%, 100% { transform: translateX(0); }
    20%  { transform: translateX(-8px); }
    40%  { transform: translateX(8px); }
    60%  { transform: translateX(-5px); }
    80%  { transform: translateX(5px); }
}
.lp-shake { animation: lp-shake .45s ease-in-out; }

/* ════════════ DARK MODE ════════════ */
[data-bs-theme="dark"] .lp-login {
    background: linear-gradient(135deg, #0a1422 0%, #14233b 40%, #1a3a5c 70%, #244270 100%);
}
[data-bs-theme="dark"] .lp-login::before {
    background:
        radial-gradient(ellipse 80% 60% at 70% 40%, rgba(67,97,238,.22) 0%, transparent 60%),
        radial-gradient(ellipse 50% 50% at 20% 80%, rgba(224,123,57,.14) 0%, transparent 55%);
}
[data-bs-theme="dark"] .lp-login-right { background: rgba(255,255,255,.02); }
[data-bs-theme="dark"] .lp-login-card {
    background: #1e293b;
    box-shadow: 0 24px 56px rgba(0,0,0,.45), 0 0 0 1px rgba(255,255,255,.06);
}
[data-bs-theme="dark"] .lp-form-title { color: #f1f5f9; }
[data-bs-theme="dark"] .lp-form-title + p,
[data-bs-theme="dark"] .lp-login-card p { color: #94a3b8 !important; }
[data-bs-theme="dark"] .lp-field-label { color: #94a3b8; }
[data-bs-theme="dark"] .lp-field-input {
    background: #0f172a; border-color: rgba(255,255,255,.12); color: #e2e8f0;
}
[data-bs-theme="dark"] .lp-field-input:focus { background: #0f172a; border-color: #7fb3f5; box-shadow: 0 0 0 4px rgba(127,179,245,.15); }
[data-bs-theme="dark"] .lp-field-input::placeholder { color: #64748b; }
[data-bs-theme="dark"] .lp-field-icon { color: #64748b; }
[data-bs-theme="dark"] .lp-field-input:focus ~ .lp-field-icon,
[data-bs-theme="dark"] .lp-field-wrap:focus-within .lp-field-icon { color: #7fb3f5; }
[data-bs-theme="dark"] .lp-forgot { color: #7fb3f5; }
[data-bs-theme="dark"] .lp-pass-toggle { color: #64748b; }
[data-bs-theme="dark"] .lp-pass-toggle:hover { color: #7fb3f5; }
[data-bs-theme="dark"] .lp-secure-seal { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.08); }
[data-bs-theme="dark"] .lp-login-card .form-check-input { background-color: #0f172a; border-color: rgba(255,255,255,.2); }
[data-bs-theme="dark"] .lp-login-card a[style*="border"] { background: rgba(255,255,255,.05) !important; border-color: rgba(255,255,255,.15) !important; color: #cbd5e1 !important; }

/* ── Painel esquerdo — textos e cards tema-aware ── */
.lp-login-lead  { font-size: .98rem; color: #3f5170; line-height: 1.75; max-width: 400px; }
.lp-login-bname { font-size: 1rem; letter-spacing: -.02em; color: #16306b; font-weight: 700; }
.lp-login-bsub  { font-size: .6rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #64748b; }
.lp-login-modcard { background: #fff; border: 1px solid rgba(27,64,142,.1); box-shadow: 0 6px 18px rgba(27,64,142,.08); }
.lp-stack-eyebrow { font-size: .58rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; }
.lp-stack-title   { font-weight: 700; color: #16306b; font-size: .9rem; line-height: 1.25; }
.lp-stack-sub     { font-size: .73rem; color: #64748b; }

[data-bs-theme="dark"] .lp-login-title { color: #f1f5f9; }
[data-bs-theme="dark"] .lp-login-title span { background: linear-gradient(90deg,#7fb3f5,#f5a623); -webkit-background-clip: text; background-clip: text; }
[data-bs-theme="dark"] .lp-hero-badge { background: rgba(255,255,255,.1); border-color: rgba(255,255,255,.2); color: rgba(255,255,255,.92); }
[data-bs-theme="dark"] .lp-login-lead  { color: #cbd5e1; }
[data-bs-theme="dark"] .lp-login-bname { color: #f1f5f9; }
[data-bs-theme="dark"] .lp-login-bsub  { color: #94a3b8; }
[data-bs-theme="dark"] .lp-login-modcard { background: #162132; border-color: rgba(255,255,255,.08); box-shadow: 0 6px 18px rgba(0,0,0,.3); }
[data-bs-theme="dark"] .lp-stack-eyebrow { color: #64748b; }
[data-bs-theme="dark"] .lp-stack-title   { color: #e2e8f0; }
[data-bs-theme="dark"] .lp-stack-sub     { color: #94a3b8; }

/* ════════════ RESPONSIVIDADE ════════════ */
@media (max-width: 575.98px) {
    .lp-login-card { padding: 1.5rem; border-radius: 1.25rem; }
    .lp-login-right { padding: 1rem !important; }
    .lp-form-title { font-size: 1.4rem; }
    .lp-field-input { padding: .8rem .9rem .8rem 2.6rem; }
}
@media (max-height: 720px) and (min-width: 1200px) {
    /* Telas baixas em desktop: painel esquerdo rola se necessário */
    .lp-login-left { overflow-y: auto; }
}
</style>

{{-- ══════════════ SCRIPTS ══════════════ --}}
<script>
    function lpTogglePass() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('passIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }

    (function () {
        const form = document.getElementById('loginForm');
        const btn  = document.getElementById('loginButton');
        if (!form || !btn) return;

        const txt     = btn.querySelector('.lp-btn-text');
        const loading = btn.querySelector('.lp-btn-loading');

        @if ($errors->any())
            form.classList.add('lp-shake');
            setTimeout(() => form.classList.remove('lp-shake'), 500);
        @endif

        // Reatividade ao enviar: mostra o estado "carregando" no botão.
        form.addEventListener('submit', function () {
            // Se houver campos inválidos, o navegador bloqueia o envio — não trava o botão.
            if (!form.checkValidity()) return;

            btn.classList.add('is-loading');
            btn.setAttribute('aria-busy', 'true');
            if (txt)     txt.style.display = 'none';
            if (loading) loading.style.display = 'flex';

            // Trava cliques repetidos sem desabilitar antes do envio nativo concluir.
            setTimeout(() => { btn.disabled = true; }, 0);
        });
    })();
</script>
</x-guest-layout>
