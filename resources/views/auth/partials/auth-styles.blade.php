{{-- Estilos compartilhados das telas de autenticação (login/registro/troca de senha).
     Mantém o mesmo padrão visual "lp-*" da landing page e da tela de login. --}}
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
    content: ''; position: absolute; inset: 0; pointer-events: none;
    background:
        radial-gradient(ellipse 80% 60% at 70% 40%, rgba(67,97,238,.10) 0%, transparent 60%),
        radial-gradient(ellipse 50% 50% at 20% 80%, rgba(224,123,57,.08) 0%, transparent 55%);
}
.lp-blob { position: absolute; border-radius: 50%; pointer-events: none; }

/* ── Layout ───────────────────────────────────────────────────────────────── */
.lp-login-left { width: 56%; position: relative; z-index: 1; overflow: hidden; }
.lp-login-right {
    flex: 1; position: relative; z-index: 1;
    background: rgba(255,255,255,.05); backdrop-filter: blur(2px);
    overflow-y: auto; -webkit-overflow-scrolling: touch;
}
@media (max-width: 1199.98px) {
    .lp-login-left { display: none !important; }
    .lp-login-right { flex: 1; background: transparent; backdrop-filter: none; }
}

/* ── Card do formulário ───────────────────────────────────────────────────── */
.lp-login-card {
    width: 100%; max-width: 460px; background: #fff;
    border-radius: 1.5rem; padding: 2.5rem;
    box-shadow: 0 24px 56px rgba(27,64,142,.16), 0 8px 24px rgba(27,64,142,.10), 0 0 0 1px rgba(27,64,142,.05);
    animation: lp-slide-up .45s cubic-bezier(.22,1,.36,1) both;
}
@keyframes lp-slide-up { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }

/* ── Painel esquerdo ──────────────────────────────────────────────────────── */
.lp-hero-badge {
    display: inline-flex; align-items: center; gap: .5rem;
    background: rgba(27,64,142,.08); border: 1px solid rgba(27,64,142,.18);
    backdrop-filter: blur(8px); border-radius: 999px; padding: .35rem 1rem;
    font-size: .78rem; font-weight: 700; color: #1B408E;
    letter-spacing: .04em; text-transform: uppercase;
}
.lp-login-title { font-size: clamp(1.85rem, 2.8vw, 2.75rem); font-weight: 800; line-height: 1.15; color: #16306b; letter-spacing: -.03em; }
.lp-login-title span { background: linear-gradient(90deg, #1B408E, #e07b39); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.lp-login-lead  { font-size: .98rem; color: #3f5170; line-height: 1.75; max-width: 400px; }
.lp-login-bname { font-size: 1rem; letter-spacing: -.02em; color: #16306b; font-weight: 700; }
.lp-login-bsub  { font-size: .6rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #64748b; }
.lp-login-modcard { background: #fff; border: 1px solid rgba(27,64,142,.1); box-shadow: 0 6px 18px rgba(27,64,142,.08); }
.lp-stack-eyebrow { font-size: .58rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; }
.lp-stack-title   { font-weight: 700; color: #16306b; font-size: .9rem; line-height: 1.25; }
.lp-stack-sub     { font-size: .73rem; color: #64748b; }
.lp-stats { background: rgba(255,255,255,.7); border: 1px solid rgba(27,64,142,.12); backdrop-filter: blur(12px); border-radius: 1.25rem; padding: 1.25rem 1.5rem; box-shadow: 0 8px 24px rgba(27,64,142,.08); }
.lp-stat-value { font-size: 1.75rem; font-weight: 800; color: #16306b; line-height: 1; }
.lp-stat-label { font-size: .7rem; color: #64748b; text-transform: uppercase; letter-spacing: .05em; margin-top: .2rem; }

/* ── Título do form ───────────────────────────────────────────────────────── */
.lp-form-title { font-size: 1.65rem; font-weight: 800; color: #0d1b2e; letter-spacing: -.03em; line-height: 1.15; }
.lp-form-subtitle { font-size: .88rem; line-height: 1.5; }
.lp-mini-kicker { display: inline-flex; align-items: center; gap: .4rem; color: #1B408E; font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
.lp-pill-link { display: inline-flex; align-items: center; gap: .35rem; border: 1.5px solid #e2e8f0; color: #64748b; background: #f8fafc; border-radius: 999px; padding: .35rem .8rem; font-size: .75rem; font-weight: 700; line-height: 1.2; text-decoration: none; white-space: nowrap; transition: all .2s; }
.lp-pill-link:hover { color: #1B408E; border-color: rgba(27,64,142,.28); background: #fff; }
.lp-mobile-brand { display: inline-flex; align-items: center; gap: .5rem; }
.lp-mobile-brand-icon { width: 38px; height: 38px; background: linear-gradient(135deg,#1a3a5c,#1B408E); border-radius: .75rem; display: flex; align-items: center; justify-content: center; }
.lp-mobile-brand-text { font-size: 1.1rem; color: #0d1b2e; font-weight: 800; }

/* ── Labels e inputs ──────────────────────────────────────────────────────── */
.lp-field-label { display: block; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: .5rem; }
.lp-field-wrap { position: relative; }
.lp-field-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1rem; pointer-events: none; z-index: 1; transition: color .2s; }
.lp-field-input {
    display: block; width: 100%; padding: .9rem 1rem .9rem 2.8rem;
    font-size: .95rem; font-weight: 500; font-family: inherit; color: #0d1b2e;
    background: #f8fafc; border: 2px solid #e2e8f0; border-radius: .875rem;
    outline: none; transition: border-color .25s, box-shadow .25s, background .25s; appearance: none; -webkit-appearance: none;
}
.lp-field-input:focus { border-color: #1B408E; background: #fff; box-shadow: 0 0 0 4px rgba(27,64,142,.09); }
.lp-field-wrap:focus-within .lp-field-icon { color: #1B408E; }
.lp-field-input::placeholder { color: #94a3b8; }
.lp-field-error { border-color: #dc3545 !important; }
.lp-field-input:focus.lp-field-error { box-shadow: 0 0 0 4px rgba(220,53,69,.09); }
.lp-field-help { margin-top: .45rem; color: #64748b; font-size: .78rem; font-weight: 500; line-height: 1.4; }
.lp-field-help-error { color: #b91c1c; }
.lp-pass-toggle { position: absolute; right: .875rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; padding: .25rem; z-index: 2; font-size: 1.05rem; line-height: 1; display: flex; align-items: center; transition: color .2s; }
.lp-pass-toggle:hover { color: #1B408E; }

/* ── Botão submit ─────────────────────────────────────────────────────────── */
.lp-submit-btn {
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    background: linear-gradient(135deg, #e07b39 0%, #f5a623 100%); border: none; color: #fff;
    font-weight: 700; font-size: 1rem; font-family: inherit; padding: .95rem 2rem;
    border-radius: 999px; cursor: pointer; box-shadow: 0 8px 24px rgba(224,123,57,.4); transition: all .25s ease; letter-spacing: -.01em;
}
.lp-submit-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 14px 32px rgba(224,123,57,.52); }
.lp-submit-btn:active:not(:disabled) { transform: translateY(0); }
.lp-submit-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; box-shadow: none; filter: grayscale(.3); }
.lp-btn-text, .lp-btn-loading { display: flex; align-items: center; gap: .5rem; }
.lp-btn-loading { display: none; }

.lp-secondary-btn { background: none; border: none; color: #64748b; font-size: .82rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: color .2s; }
.lp-secondary-btn:hover { color: #1B408E; }

.lp-secure-seal { margin-top: 1.5rem; padding: .8rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: .875rem; display: flex; align-items: center; justify-content: center; gap: .5rem; }
.lp-secure-seal i { color: #22a06b; font-size: 1rem; flex-shrink: 0; }
.lp-secure-seal span { font-size: .72rem; color: #64748b; font-weight: 600; line-height: 1.35; }

/* ── Alertas ──────────────────────────────────────────────────────────────── */
.lp-alert { display: flex; align-items: flex-start; gap: .6rem; border-radius: .75rem; padding: .85rem 1rem; font-size: .85rem; font-weight: 500; }
.lp-alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
.lp-alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
.lp-alert-info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }

/* ── Checklist de requisitos de senha (reativo) ───────────────────────────── */
.pwd-panel { margin-top: .75rem; background: #f8fafc; border: 1px solid #e9eef5; border-radius: .875rem; padding: .85rem 1rem; }
.pwd-strength-track { height: 6px; border-radius: 999px; background: #e2e8f0; overflow: hidden; margin-bottom: .35rem; }
.pwd-strength-fill { height: 100%; width: 0; border-radius: 999px; transition: width .3s ease, background .3s ease; background: #dc3545; }
.pwd-strength-label { font-size: .72rem; font-weight: 700; text-align: right; color: #64748b; margin-bottom: .6rem; }
.pwd-reqs { display: grid; grid-template-columns: 1fr 1fr; gap: .3rem .9rem; }
@media (max-width: 420px) { .pwd-reqs { grid-template-columns: 1fr; } }
.pwd-req { display: flex; align-items: center; gap: .45rem; font-size: .78rem; font-weight: 500; color: #94a3b8; transition: color .2s; }
.pwd-req .pwd-req-ic { display: inline-flex; align-items: center; justify-content: center; width: 16px; height: 16px; border-radius: 50%; border: 1.5px solid #cbd5e1; font-size: .6rem; color: transparent; flex-shrink: 0; transition: all .2s; }
.pwd-req.ok { color: #15803d; }
.pwd-req.ok .pwd-req-ic { background: #22a06b; border-color: #22a06b; color: #fff; }
.pwd-match { margin-top: .6rem; font-size: .8rem; font-weight: 600; display: none; align-items: center; gap: .4rem; }
.pwd-match.show { display: flex; }
.pwd-match.ok { color: #15803d; }
.pwd-match.no { color: #dc2626; }

/* ── Shake em erros ───────────────────────────────────────────────────────── */
@keyframes lp-shake { 0%,100%{transform:translateX(0);} 20%{transform:translateX(-8px);} 40%{transform:translateX(8px);} 60%{transform:translateX(-5px);} 80%{transform:translateX(5px);} }
.lp-shake { animation: lp-shake .45s ease-in-out; }

/* ════════════ DARK MODE ════════════ */
[data-bs-theme="dark"] .lp-login { background: linear-gradient(135deg, #0a1422 0%, #14233b 40%, #1a3a5c 70%, #244270 100%); }
[data-bs-theme="dark"] .lp-login::before { background: radial-gradient(ellipse 80% 60% at 70% 40%, rgba(67,97,238,.22) 0%, transparent 60%), radial-gradient(ellipse 50% 50% at 20% 80%, rgba(224,123,57,.14) 0%, transparent 55%); }
[data-bs-theme="dark"] .lp-login-right { background: rgba(255,255,255,.02); }
[data-bs-theme="dark"] .lp-login-card { background: #1e293b; box-shadow: 0 24px 56px rgba(0,0,0,.45), 0 0 0 1px rgba(255,255,255,.06); }
[data-bs-theme="dark"] .lp-form-title { color: #f1f5f9; }
[data-bs-theme="dark"] .lp-login-card p { color: #94a3b8 !important; }
[data-bs-theme="dark"] .lp-mini-kicker { color: #7fb3f5; }
[data-bs-theme="dark"] .lp-pill-link { color: #94a3b8; background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.1); }
[data-bs-theme="dark"] .lp-pill-link:hover { color: #7fb3f5; background: rgba(255,255,255,.07); border-color: rgba(127,179,245,.35); }
[data-bs-theme="dark"] .lp-mobile-brand-text { color: #f1f5f9; }
[data-bs-theme="dark"] .lp-field-label { color: #94a3b8; }
[data-bs-theme="dark"] .lp-field-input { background: #0f172a; border-color: rgba(255,255,255,.12); color: #e2e8f0; }
[data-bs-theme="dark"] .lp-field-input:focus { background: #0f172a; border-color: #7fb3f5; box-shadow: 0 0 0 4px rgba(127,179,245,.15); }
[data-bs-theme="dark"] .lp-field-input::placeholder { color: #64748b; }
[data-bs-theme="dark"] .lp-field-icon { color: #64748b; }
[data-bs-theme="dark"] .lp-field-wrap:focus-within .lp-field-icon { color: #7fb3f5; }
[data-bs-theme="dark"] .lp-field-help { color: #94a3b8; }
[data-bs-theme="dark"] .lp-field-help-error { color: #fca5a5; }
[data-bs-theme="dark"] .lp-pass-toggle { color: #64748b; }
[data-bs-theme="dark"] .lp-pass-toggle:hover { color: #7fb3f5; }
[data-bs-theme="dark"] .lp-secure-seal { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.08); }
[data-bs-theme="dark"] .lp-secondary-btn:hover { color: #7fb3f5; }
[data-bs-theme="dark"] .pwd-panel { background: rgba(255,255,255,.03); border-color: rgba(255,255,255,.08); }
[data-bs-theme="dark"] .pwd-strength-track { background: rgba(255,255,255,.12); }
[data-bs-theme="dark"] .pwd-req { color: #64748b; }
[data-bs-theme="dark"] .pwd-req .pwd-req-ic { border-color: rgba(255,255,255,.2); }
[data-bs-theme="dark"] .pwd-req.ok { color: #4ade80; }
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
[data-bs-theme="dark"] .lp-stats { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.08); }
[data-bs-theme="dark"] .lp-stat-value { color: #f1f5f9; }

/* ════════════ RESPONSIVIDADE ════════════ */
@media (max-width: 575.98px) {
    .lp-login-card { padding: 1.5rem; border-radius: 1.25rem; }
    .lp-login-right { padding: 1rem !important; }
    .lp-form-title { font-size: 1.4rem; }
    .lp-field-input { padding: .8rem .9rem .8rem 2.6rem; }
}
</style>
