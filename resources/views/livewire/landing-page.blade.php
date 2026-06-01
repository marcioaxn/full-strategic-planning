<div class="lp-body">
<style>
/* ──────────────────────────────────────────────────────────
   SISTEMA DE DESIGN — Landing Page / Dashboard Público
   Tokens de cor que suportam light mode E dark mode
   ────────────────────────────────────────────────────────── */

/* ── Paleta base ── */
:root {
    --lp-navy:    #0d1b2e;
    --lp-blue:    #1a3a5c;
    --lp-primary: #1B408E;
    --lp-accent:  #e07b39;
    --lp-accent2: #f5a623;
    --lp-green:   #2e8b57;
    --lp-purple:  #6a4c9c;
}

/* ── Seções que mudam no dark mode ── */
.lp-section-light   { background: #f8fafc; }
.lp-section-white   { background: #ffffff; }
.lp-heading         { color: #0d1b2e; }
.lp-subtext         { color: #475569; }
.lp-card-surface    { background: #ffffff; border: 1px solid #e2e8f0; }
.lp-feat-card-bg    { background: #f8fafc; border: 1px solid #e2e8f0; }
.lp-eyebrow         { color: #2e6da4; }

[data-bs-theme="dark"] .lp-section-light  { background: #0f172a; }
[data-bs-theme="dark"] .lp-section-white  { background: #0f172a; }
[data-bs-theme="dark"] .lp-heading        { color: #f1f5f9; }
[data-bs-theme="dark"] .lp-subtext        { color: #94a3b8; }
[data-bs-theme="dark"] .lp-card-surface   { background: #1e293b; border-color: rgba(255,255,255,.08); }
[data-bs-theme="dark"] .lp-feat-card-bg   { background: #1e293b; border-color: rgba(255,255,255,.08); }
[data-bs-theme="dark"] .lp-eyebrow        { color: #7fb3f5; }
[data-bs-theme="dark"] .lp-card-surface:hover { background: #263347; }

/* ── Hero (tom claro/pastel — textos escuros) ── */
.lp-hero {
    background: linear-gradient(135deg, #cfe0f6 0%, #dde9fa 40%, #e9f1fc 70%, #f4f8fd 100%);
    min-height: 100vh;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
}
.lp-hero::before {
    content: '';
    position: absolute; inset: 0; pointer-events: none;
    background:
        radial-gradient(ellipse 80% 60% at 70% 40%, rgba(67,97,238,.10) 0%, transparent 60%),
        radial-gradient(ellipse 50% 50% at 20% 80%, rgba(224,123,57,.08) 0%, transparent 55%);
}
.lp-shape {
    position: absolute; border-radius: 50%;
    filter: blur(60px); opacity: .3; pointer-events: none;
}
.lp-shape-1 { width: 400px; height: 400px; background: #4361EE; top: -100px; right: -100px; }
.lp-shape-2 { width: 300px; height: 300px; background: #e07b39; bottom: 50px; left: 30%; }
.lp-shape-3 { width: 200px; height: 200px; background: #22a06b; top: 40%; left: -50px; }

/* ── Badge pill ── */
.lp-hero-badge {
    display: inline-flex; align-items: center; gap: .5rem;
    background: rgba(27,64,142,.08); border: 1px solid rgba(27,64,142,.18);
    backdrop-filter: blur(8px); border-radius: 999px;
    padding: .35rem 1rem; font-size: .78rem; font-weight: 700;
    color: #1B408E; letter-spacing: .04em; text-transform: uppercase;
}

/* ── Títulos hero ── */
.lp-hero-title {
    font-size: clamp(2rem, 5vw, 3.6rem);
    font-weight: 800; line-height: 1.1; color: #16306b; letter-spacing: -.03em;
}
.lp-hero-title span {
    background: linear-gradient(90deg, #1B408E, #e07b39);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.lp-hero-sub { font-size: 1.05rem; color: #3f5170; line-height: 1.75; max-width: 540px; }

/* ── Botões hero ── */
.lp-hero-cta {
    background: linear-gradient(135deg, #e07b39, #f5a623);
    border: none; color: #fff; font-weight: 700;
    padding: .85rem 2.2rem; border-radius: 999px; font-size: 1rem;
    box-shadow: 0 8px 24px rgba(224,123,57,.45); transition: all .25s ease;
    text-decoration: none; display: inline-flex; align-items: center; gap: .6rem;
}
.lp-hero-cta:hover { transform: translateY(-3px); box-shadow: 0 14px 32px rgba(224,123,57,.5); color: #fff; }
.lp-hero-cta-ghost {
    border: 2px solid rgba(27,64,142,.4); color: #1B408E;
    padding: .83rem 2rem; border-radius: 999px; font-weight: 700; font-size: 1rem;
    transition: all .25s ease; text-decoration: none;
    display: inline-flex; align-items: center; gap: .6rem;
}
.lp-hero-cta-ghost:hover { background: rgba(27,64,142,.08); border-color: rgba(27,64,142,.65); color: #16306b; }

/* ── Stats bar ── */
.lp-stats {
    background: rgba(255,255,255,.7); border: 1px solid rgba(27,64,142,.12);
    backdrop-filter: blur(12px); border-radius: 1.25rem; padding: 1.25rem 1.5rem;
    box-shadow: 0 8px 24px rgba(27,64,142,.08);
}
.lp-stat-value { font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 800; color: #16306b; line-height: 1; }
.lp-stat-label { font-size: .72rem; color: #64748b; text-transform: uppercase; letter-spacing: .05em; margin-top: .2rem; }
.lp-stat-divider { width: 1px; background: rgba(27,64,142,.14); }

/* ── Achievement circle ── */
.lp-achieve-ring {
    width: 130px; height: 130px; border-radius: 50%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    background: rgba(255,255,255,.7); border: 3px solid;
    box-shadow: 0 8px 24px rgba(27,64,142,.1);
}
.lp-achieve-num  { font-size: 2.2rem; font-weight: 800; color: #16306b; line-height: 1; }
.lp-achieve-sub  { font-size: .65rem; color: #64748b; text-transform: uppercase; letter-spacing: .06em; }

/* ── Módulo stacked cards (hero right) ── */
.lp-mod-card {
    display: flex; align-items: center; gap: .875rem;
    background: #fff; border: 1px solid rgba(27,64,142,.1);
    box-shadow: 0 6px 18px rgba(27,64,142,.08);
    border-radius: .875rem; padding: .875rem 1rem;
    margin-bottom: .625rem;
}
.lp-mod-icon {
    min-width: 42px; height: 42px;
    border-radius: .75rem;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.lp-stack-eyebrow { font-size: .6rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: #94a3b8; }
.lp-stack-title   { font-weight: 700; color: #16306b; font-size: .9rem; line-height: 1.2; }
.lp-stack-sub     { font-size: .72rem; color: #64748b; }
.lp-hero-eyebrow  { color: #1B408E; font-weight: 700; }
.lp-hero-muted    { color: #64748b; }

/* ════════════ DARK MODE — Hero e cards ════════════ */
[data-bs-theme="dark"] .lp-hero {
    background: linear-gradient(135deg, #0a1422 0%, #14233b 40%, #1a3a5c 70%, #244270 100%);
}
[data-bs-theme="dark"] .lp-hero::before {
    background:
        radial-gradient(ellipse 80% 60% at 70% 40%, rgba(67,97,238,.22) 0%, transparent 60%),
        radial-gradient(ellipse 50% 50% at 20% 80%, rgba(224,123,57,.14) 0%, transparent 55%);
}
[data-bs-theme="dark"] .lp-hero-badge { background: rgba(255,255,255,.1); border-color: rgba(255,255,255,.2); color: rgba(255,255,255,.92); }
[data-bs-theme="dark"] .lp-hero-title { color: #f1f5f9; }
[data-bs-theme="dark"] .lp-hero-title span { background: linear-gradient(90deg,#7fb3f5,#f5a623); -webkit-background-clip: text; background-clip: text; }
[data-bs-theme="dark"] .lp-hero-sub { color: rgba(226,232,240,.78); }
[data-bs-theme="dark"] .lp-hero-eyebrow { color: #7fb3f5; }
[data-bs-theme="dark"] .lp-hero-muted { color: rgba(148,163,184,.85); }
[data-bs-theme="dark"] .lp-hero-cta-ghost { border-color: rgba(255,255,255,.35); color: rgba(255,255,255,.85); }
[data-bs-theme="dark"] .lp-hero-cta-ghost:hover { background: rgba(255,255,255,.1); border-color: rgba(255,255,255,.6); color: #fff; }
[data-bs-theme="dark"] .lp-stats { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.12); box-shadow: none; }
[data-bs-theme="dark"] .lp-stat-value { color: #f1f5f9; }
[data-bs-theme="dark"] .lp-stat-label { color: rgba(148,163,184,.9); }
[data-bs-theme="dark"] .lp-stat-divider { background: rgba(255,255,255,.15); }
[data-bs-theme="dark"] .lp-achieve-ring { background: rgba(255,255,255,.07); box-shadow: none; }
[data-bs-theme="dark"] .lp-achieve-num { color: #f1f5f9; }
[data-bs-theme="dark"] .lp-achieve-sub { color: rgba(148,163,184,.9); }
[data-bs-theme="dark"] .lp-mod-card { background: #1e293b; border-color: rgba(255,255,255,.08); box-shadow: 0 6px 18px rgba(0,0,0,.25); }
[data-bs-theme="dark"] .lp-stack-eyebrow { color: #64748b; }
[data-bs-theme="dark"] .lp-stack-title { color: #e2e8f0; }
[data-bs-theme="dark"] .lp-stack-sub { color: #94a3b8; }
[data-bs-theme="dark"] .lp-persp-header div:last-child { /* % branco já legível */ }

/* ── Seção BSC (perspectivas) ── */
.lp-persp-card {
    border-radius: 1rem; overflow: hidden;
    transition: transform .25s ease, box-shadow .25s ease;
    margin-bottom: .875rem;
}
.lp-persp-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(27,64,142,.12); }
.lp-persp-header {
    padding: .75rem 1.25rem; color: #fff;
    display: flex; align-items: center; justify-content: space-between;
}
.lp-persp-body { padding: .875rem 1.25rem; }
.lp-progress-track { background: rgba(0,0,0,.08); border-radius: 999px; height: 8px; overflow: hidden; }
.lp-progress-fill  { height: 8px; border-radius: 999px; transition: width .6s ease; }
[data-bs-theme="dark"] .lp-persp-body { background: #1e293b !important; }

/* ── Módulos GPPEI (modo estático) ── */
.lp-section-eyebrow { font-size: .8rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
.lp-module-card {
    border-radius: 1.5rem; overflow: hidden;
    transition: all .3s cubic-bezier(.4,0,.2,1); height: 100%;
}
.lp-card-surface .lp-module-card { border: 1px solid #e2e8f0; }
.lp-module-card:hover { transform: translateY(-6px); box-shadow: 0 20px 48px rgba(27,64,142,.12); }
.lp-module-num {
    font-size: 5rem; font-weight: 900; line-height: 1; opacity: .08;
    position: absolute; top: -1rem; right: 1.5rem;
}
.lp-module-icon {
    width: 56px; height: 56px; border-radius: 1rem;
    display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
}
.lp-module-badge {
    font-size: .72rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
    padding: .25rem .75rem; border-radius: 999px;
}
.lp-module-bar { height: 3px; border-radius: 999px; margin-bottom: 1.5rem; }
.lp-feature-tag {
    display: inline-flex; align-items: center; gap: .4rem;
    border-radius: .5rem; padding: .3rem .7rem; font-size: .78rem; font-weight: 500;
}
.lp-feature-tag { background: #f1f5f9; color: #475569; }
[data-bs-theme="dark"] .lp-feature-tag { background: rgba(255,255,255,.08); color: #94a3b8; }

.mod-inaugurar .lp-module-icon { background: linear-gradient(135deg, #0d1b2e, #1a3a5c); color: #fff; }
.mod-inaugurar .lp-module-num  { color: #1a3a5c; }
.mod-inaugurar .lp-module-badge { background: #e8f0fa; color: #1a3a5c; }
.mod-inaugurar .lp-module-bar   { background: linear-gradient(90deg, #1a3a5c, #2e6da4); }
[data-bs-theme="dark"] .mod-inaugurar .lp-module-badge { background: rgba(26,58,92,.4); color: #7fb3f5; }

.mod-planejar .lp-module-icon { background: linear-gradient(135deg, #2e6da4, #4361EE); color: #fff; }
.mod-planejar .lp-module-num  { color: #2e6da4; }
.mod-planejar .lp-module-badge { background: #e8f2ff; color: #2e6da4; }
.mod-planejar .lp-module-bar   { background: linear-gradient(90deg, #2e6da4, #4361EE); }
[data-bs-theme="dark"] .mod-planejar .lp-module-badge { background: rgba(46,109,164,.4); color: #7fb3f5; }

.mod-monitorar .lp-module-icon { background: linear-gradient(135deg, #2e8b57, #22a06b); color: #fff; }
.mod-monitorar .lp-module-num  { color: #2e8b57; }
.mod-monitorar .lp-module-badge { background: #e8f5ef; color: #2e8b57; }
.mod-monitorar .lp-module-bar   { background: linear-gradient(90deg, #2e8b57, #22a06b); }
[data-bs-theme="dark"] .mod-monitorar .lp-module-badge { background: rgba(46,139,87,.4); color: #6ee7b7; }

/* ── Feature cards ── */
.lp-feat-card {
    border-radius: 1.25rem; padding: 1.75rem; height: 100%;
    transition: all .25s ease;
}
.lp-feat-card:hover { box-shadow: 0 8px 24px rgba(27,64,142,.08); }
.lp-feat-icon {
    width: 44px; height: 44px; border-radius: .875rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem; margin-bottom: 1rem;
}

/* ── GPPEI band (laranja — funciona em ambos os modos) ── */
.lp-gppei-band { background: linear-gradient(135deg, #e07b39 0%, #f5a623 100%); }

/* ── Footer ── */
.lp-footer { background: #0d1b2e; color: rgba(255,255,255,.6); }

/* ── Mapa Estratégico (read-only) ── */
.lp-map-row {
    display: flex; border-radius: 12px; overflow: hidden;
    margin-bottom: .875rem; transition: box-shadow .25s ease;
}
.lp-map-row:hover { box-shadow: 0 10px 28px rgba(27,64,142,.12); }
.lp-map-band {
    flex-shrink: 0; width: 210px; color: #fff; padding: 1rem 1.25rem;
    display: flex; flex-direction: column; justify-content: center; gap: .25rem;
}
.lp-map-band-name { font-weight: 700; font-size: .9rem; line-height: 1.2; text-transform: uppercase; letter-spacing: .02em; }
.lp-map-band-pct  { font-size: 1.5rem; font-weight: 800; line-height: 1; }
.lp-map-objs { flex: 1; display: flex; flex-wrap: wrap; gap: .5rem; padding: 1rem; align-items: flex-start; }
.lp-map-obj {
    display: inline-flex; align-items: center; gap: .5rem;
    background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 8px; padding: .45rem .7rem; max-width: 100%;
}
.lp-map-obj-dot  { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.lp-map-obj-name { font-size: .8rem; font-weight: 600; color: #16306b; }
.lp-map-obj-pct  { font-size: .78rem; font-weight: 800; }
[data-bs-theme="dark"] .lp-map-obj      { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.08); }
[data-bs-theme="dark"] .lp-map-obj-name { color: #e2e8f0; }

/* ── Responsividade adicional ── */
@media (max-width: 991.98px) {
    /* Hero deixa de forçar 100vh para não cortar conteúdo em telas baixas */
    .lp-hero { min-height: auto; align-items: flex-start; }
    .lp-hero .container { padding-top: 3.5rem; padding-bottom: 3.5rem; }
}
@media (max-width: 767.98px) {
    .lp-stats { padding: 1rem .75rem; }
    .lp-stat-divider { display: none; }
    .lp-stat-value { font-size: 1.4rem; }
    .lp-stat-label { font-size: .62rem; }
    .lp-hero-title { font-size: clamp(1.7rem, 8vw, 2.2rem); }
    .lp-hero-sub { font-size: .95rem; }
    .lp-achieve-ring { width: 100px; height: 100px; }
    .lp-achieve-num { font-size: 1.8rem; }
    /* CTAs ocupam largura total e empilham confortavelmente */
    .lp-hero-cta, .lp-hero-cta-ghost { width: 100%; justify-content: center; }
    /* Mapa Estratégico: faixa da perspectiva vai para o topo, em linha */
    .lp-map-row { flex-direction: column; }
    .lp-map-band { width: 100%; flex-direction: row; align-items: center; justify-content: space-between; padding: .65rem 1rem; }
    .lp-map-band-pct { font-size: 1.2rem; }
}
@media (max-width: 575.98px) {
    .lp-hero .container { padding-left: 1.1rem; padding-right: 1.1rem; }
    .lp-feat-card { padding: 1.25rem; }
    .lp-module-card { padding: 1.25rem !important; }
    .lp-persp-header { padding: .65rem 1rem; }
    .lp-persp-body { padding: .75rem 1rem; }
}
</style>

{{-- ════════════════════════════════════════════════════════════
     HERO — idêntico em ambos os modos, mas data-driven quando PEI ativo
════════════════════════════════════════════════════════════ --}}
<section class="lp-hero lp-body">
    <div class="lp-shape lp-shape-1"></div>
    <div class="lp-shape lp-shape-2"></div>
    <div class="lp-shape lp-shape-3"></div>

    <div class="container py-5 position-relative">
        <div class="row align-items-center g-5">

            {{-- Coluna esquerda --}}
            <div class="col-lg-6">
                @if($temDados)
                {{-- ── MODO DASHBOARD: PEI configurado ── --}}
                <div class="lp-hero-badge mb-4">
                    <i class="bi bi-patch-check-fill" style="color:#e07b39;"></i>
                    {{ $pei->dsc_pei ?? 'Ciclo Estratégico Ativo' }}
                    &nbsp;·&nbsp;
                    {{ $pei->num_ano_inicio_pei ?? '' }}–{{ $pei->num_ano_fim_pei ?? '' }}
                </div>
                <h1 class="lp-hero-title mb-3">
                    {{ $organizacao?->sgl_organizacao ?? 'SEAE' }}<br>
                    <span>Planejamento Estratégico</span><br>
                    Institucional
                </h1>
                @if($identidade?->dsc_missao)
                <p class="lp-hero-sub mb-4">
                    <em class="lp-hero-eyebrow" style="font-size:.78rem;letter-spacing:.06em;text-transform:uppercase;font-style:normal;">Missão</em><br>
                    {{ Str::limit($identidade->dsc_missao, 160) }}
                </p>
                @else
                <p class="lp-hero-sub mb-4">
                    Painel público de transparência estratégica —
                    acompanhe o progresso do planejamento institucional em tempo real.
                </p>
                @endif

                @else
                {{-- ── MODO ESTÁTICO: sem PEI ── --}}
                <div class="lp-hero-badge mb-4">
                    <i class="bi bi-patch-check-fill" style="color:#e07b39;"></i>
                    GPPEI/MGI 2025 · Gestão Pública Federal
                </div>
                <h1 class="lp-hero-title mb-4">
                    Planejamento Estratégico<br>
                    <span>Institucional</span>
                </h1>
                <p class="lp-hero-sub mb-5">
                    Sistema integrado alinhado ao
                    <strong class="lp-hero-eyebrow">Guia Prático de PEI do MGI</strong>.
                    Do diagnóstico ao monitoramento em um único ambiente.
                </p>
                @endif

                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="{{ route('login') }}" class="lp-hero-cta">
                        <i class="bi bi-box-arrow-in-right"></i>
                        {{ $temDados ? 'Acessar o Sistema' : 'Entrar no Sistema' }}
                    </a>
                    @if($temDados)
                    <a href="#panorama" class="lp-hero-cta-ghost">
                        <i class="bi bi-chevron-down"></i>
                        Ver Panorama Estratégico
                    </a>
                    @else
                    <a href="{{ route('documentos.viewer-gppei') }}" class="lp-hero-cta-ghost">
                        <i class="bi bi-book-half"></i>
                        Ver o Guia GPPEI
                    </a>
                    @endif
                </div>

                {{-- Stats bar --}}
                <div class="lp-stats">
                    <div class="row g-0 text-center align-items-center">
                        @if($temDados)
                        <div class="col">
                            <div class="lp-stat-value">{{ $stats['perspectivas'] }}</div>
                            <div class="lp-stat-label">Perspectivas</div>
                        </div>
                        <div class="lp-stat-divider d-none d-sm-block" style="height:36px;"></div>
                        <div class="col">
                            <div class="lp-stat-value">{{ $stats['objetivos'] }}</div>
                            <div class="lp-stat-label">Objetivos</div>
                        </div>
                        <div class="lp-stat-divider d-none d-sm-block" style="height:36px;"></div>
                        <div class="col">
                            <div class="lp-stat-value">{{ $stats['planos'] }}</div>
                            <div class="lp-stat-label">Planos</div>
                        </div>
                        <div class="lp-stat-divider d-none d-sm-block" style="height:36px;"></div>
                        <div class="col">
                            <div class="lp-stat-value">{{ $stats['indicadores'] }}</div>
                            <div class="lp-stat-label">Indicadores</div>
                        </div>
                        @else
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
                        @endif
                    </div>
                </div>
            </div>

            {{-- Coluna direita --}}
            <div class="col-lg-6 d-none d-lg-block">
                @if($temDados)
                {{-- Atingimento global + cards de perspectiva --}}
                <div class="text-center mb-4">
                    <div class="lp-achieve-ring mx-auto mb-2" style="border-color:{{ $stats['corGlobal'] }};">
                        <div class="lp-achieve-num" style="color:{{ $stats['corGlobal'] }};">{{ $stats['atingimentoGlobal'] }}%</div>
                        <div class="lp-achieve-sub">Atingimento<br>Global</div>
                    </div>
                    <p class="lp-hero-muted" style="font-size:.75rem;margin:0;">
                        Média consolidada de {{ $stats['perspectivas'] }} perspectivas BSC
                    </p>
                </div>

                <div class="position-relative ps-2">
                    @foreach($perspectivas->take(3) as $i => $persp)
                    <div class="lp-mod-card" style="border-left:4px solid {{ $persp->cor_atingimento }};transform:translateX({{ $i * 22 }}px);">
                        <div class="lp-mod-icon" style="background:rgba({{ hexdec(substr($persp->cor_atingimento,1,2)) }},{{ hexdec(substr($persp->cor_atingimento,3,2)) }},{{ hexdec(substr($persp->cor_atingimento,5,2)) }},.16);">
                            <i class="bi bi-layers" style="font-size:1.1rem;color:{{ $persp->cor_atingimento }};"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="lp-stack-eyebrow">Perspectiva {{ $loop->iteration }}</div>
                            <div class="lp-stack-title text-truncate">{{ $persp->dsc_perspectiva }}</div>
                            <div class="lp-stack-sub">
                                {{ $persp->objetivos->count() }} {{ Str::plural('objetivo', $persp->objetivos->count()) }}
                                &nbsp;·&nbsp; {{ $persp->atingimento_medio }}%
                            </div>
                        </div>
                        <div style="flex-shrink:0;font-size:1.1rem;font-weight:800;color:{{ $persp->cor_atingimento }};">
                            {{ $persp->atingimento_medio }}%
                        </div>
                    </div>
                    @endforeach
                    @if($perspectivas->count() > 3)
                    <div class="lp-hero-muted" style="font-size:.75rem;text-align:center;margin-top:.5rem;">
                        + {{ $perspectivas->count() - 3 }} perspectivas adicionais
                    </div>
                    @endif
                </div>
                @else
                {{-- Cards estáticos dos módulos GPPEI --}}
                <div class="position-relative ps-4">
                    @foreach([
                        ['num'=>'01','title'=>'Inaugurar e Integrar','sub'=>'Planejar o processo · Integração PPA/LOA/ODS','icon'=>'flag-fill','c'=>'#1a3a5c'],
                        ['num'=>'02','title'=>'Planejar','sub'=>'Cadeia de Valor · SWOT · Indicadores · Planos','icon'=>'diagram-3-fill','c'=>'#1B408E'],
                        ['num'=>'03','title'=>'Monitorar e Avaliar','sub'=>'Dashboard · RAE · Relatórios · Alertas','icon'=>'graph-up-arrow','c'=>'#2e8b57'],
                    ] as $i => $m)
                    <div class="lp-mod-card" style="border-left:4px solid {{ $m['c'] }};transform:translateX({{ $i * 20 }}px);">
                        <div class="lp-mod-icon" style="background:{{ $m['c'] }};">
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
                @endif
            </div>

        </div>
    </div>
</section>


{{-- ════════════════════════════════════════════════════════════
     CONTEÚDO PRINCIPAL — split por modo
════════════════════════════════════════════════════════════ --}}

@if($temDados)
{{-- ══════════════════════════════════════════════════════════
     MODO DASHBOARD — Panorama Estratégico Real
══════════════════════════════════════════════════════════ --}}

{{-- ── MAPA BSC (perspectivas com atingimento) ── --}}
<section id="panorama" class="lp-section-light lp-body" style="padding: 5rem 0; scroll-margin-top: 80px;">
    <div class="container">
        <div class="text-center mb-5">
            <div class="lp-section-eyebrow lp-eyebrow mb-2">Mapa Estratégico</div>
            <h2 class="fw-bold lp-heading" style="font-size:clamp(1.6rem,2.5vw,2.4rem);letter-spacing:-.02em;">
                Panorama Estratégico — BSC
            </h2>
            <p class="lp-subtext mx-auto mb-0" style="max-width:520px;font-size:1rem;line-height:1.7;">
                Atingimento consolidado por perspectiva do Balanced Scorecard.
                Atualizado em tempo real conforme os indicadores são lançados.
            </p>
        </div>

        @php
            $coresNivel = [1 => '#475569', 2 => '#2e8b57', 3 => '#0891b2', 4 => '#d97706', 5 => '#1B408E'];
        @endphp

        <div class="row g-4">
            @forelse($perspectivas as $persp)
            @php
                $corBand = $coresNivel[$persp->num_nivel_hierarquico_apresentacao] ?? '#1B408E';
                $pct = min(100, max(0, $persp->atingimento_medio));
            @endphp
            <div class="col-12 col-md-6">
                <div class="lp-persp-card lp-card-surface">
                    {{-- Header colorido --}}
                    <div class="lp-persp-header" style="background:{{ $corBand }};">
                        <div>
                            <div style="font-size:.62rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;opacity:.65;">
                                Perspectiva {{ $loop->iteration }}
                            </div>
                            <div style="font-weight:700;font-size:.95rem;">{{ $persp->dsc_perspectiva }}</div>
                        </div>
                        <div style="font-size:1.75rem;font-weight:800;color:#fff;text-shadow:0 1px 2px rgba(0,0,0,.18);">
                            {{ $persp->atingimento_medio }}%
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="lp-persp-body lp-section-white">
                        <div class="lp-progress-track mb-2">
                            <div class="lp-progress-fill" style="width:{{ $pct }}%;background:{{ $persp->cor_atingimento }};"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div style="font-size:.8rem;" class="lp-subtext">
                                <i class="bi bi-bullseye me-1"></i>
                                {{ $persp->objetivos->count() }} {{ Str::plural('objetivo', $persp->objetivos->count()) }}
                                @if($persp->objetivos_abaixo > 0)
                                &nbsp;·&nbsp;
                                <span style="color:#dc3545;">{{ $persp->objetivos_abaixo }} abaixo de 50%</span>
                                @endif
                            </div>
                            <div style="font-size:.72rem;font-weight:700;padding:.2rem .7rem;border-radius:999px;background:{{ $persp->cor_atingimento }}22;color:{{ $persp->cor_atingimento }};">
                                {{ $pct >= 80 ? 'No alvo' : ($pct >= 50 ? 'Atenção' : 'Crítico') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5 lp-subtext">
                <i class="bi bi-layers fs-1 d-block mb-3 opacity-25"></i>
                Nenhuma perspectiva cadastrada ainda.
            </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ── INDICADORES E RISCOS ── --}}
<section class="lp-section-white lp-body" style="padding: 4rem 0;">
    <div class="container">
        <div class="row g-4 align-items-stretch">

            {{-- Card: Visão Geral --}}
            <div class="col-md-4">
                <div class="lp-card-surface rounded-4 p-4 h-100">
                    <div style="width:48px;height:48px;background:linear-gradient(135deg,#1a3a5c,#1B408E);border-radius:1rem;display:flex;align-items:center;justify-content:center;margin-bottom:1.25rem;">
                        <i class="bi bi-speedometer2 text-white fs-5"></i>
                    </div>
                    <h5 class="fw-bold lp-heading mb-3">Visão Geral</h5>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="lp-subtext small">Perspectivas</span>
                            <span class="fw-bold lp-heading">{{ $stats['perspectivas'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="lp-subtext small">Objetivos Estratégicos</span>
                            <span class="fw-bold lp-heading">{{ $stats['objetivos'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="lp-subtext small">Indicadores (KPIs)</span>
                            <span class="fw-bold lp-heading">{{ $stats['indicadores'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="lp-subtext small">Planos de Ação</span>
                            <span class="fw-bold lp-heading">{{ $stats['planos'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Atingimento Global --}}
            <div class="col-md-4">
                <div class="lp-card-surface rounded-4 p-4 h-100 text-center d-flex flex-column justify-content-center">
                    <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;margin-bottom:1rem;" class="lp-subtext">
                        Atingimento Global
                    </div>
                    <div style="font-size:4.5rem;font-weight:900;line-height:1;color:{{ $stats['corGlobal'] }};letter-spacing:-.04em;">
                        {{ $stats['atingimentoGlobal'] }}%
                    </div>
                    <div class="mt-3 px-4">
                        <div class="lp-progress-track">
                            <div class="lp-progress-fill" style="width:{{ min(100, $stats['atingimentoGlobal']) }}%;background:{{ $stats['corGlobal'] }};"></div>
                        </div>
                    </div>
                    <div class="mt-3 lp-subtext" style="font-size:.82rem;line-height:1.5;">
                        Média ponderada de<br>{{ $stats['perspectivas'] }} perspectivas BSC
                    </div>
                </div>
            </div>

            {{-- Card: Riscos e Alertas --}}
            <div class="col-md-4">
                <div class="lp-card-surface rounded-4 p-4 h-100">
                    <div style="width:48px;height:48px;background:linear-gradient(135deg,#dc3545,#ea580c);border-radius:1rem;display:flex;align-items:center;justify-content:center;margin-bottom:1.25rem;">
                        <i class="bi bi-shield-exclamation text-white fs-5"></i>
                    </div>
                    <h5 class="fw-bold lp-heading mb-1">Gestão de Riscos</h5>
                    <p class="lp-subtext mb-3" style="font-size:.85rem;">Riscos identificados e monitorados no ciclo.</p>

                    @if($stats['riscosCriticos'] > 0)
                    <div class="rounded-3 p-3 d-flex align-items-center gap-3 mb-2"
                         style="background:rgba(220,53,69,.08);border:1px solid rgba(220,53,69,.2);">
                        <div style="font-size:2rem;font-weight:800;color:#dc3545;line-height:1;">{{ $stats['riscosCriticos'] }}</div>
                        <div>
                            <div style="font-weight:700;color:#dc3545;font-size:.9rem;">Risco(s) Crítico(s)</div>
                            <div class="lp-subtext" style="font-size:.78rem;">Nível P×I ≥ 16 — requerem atenção imediata</div>
                        </div>
                    </div>
                    @else
                    <div class="rounded-3 p-3 d-flex align-items-center gap-3"
                         style="background:rgba(46,139,87,.08);border:1px solid rgba(46,139,87,.2);">
                        <i class="bi bi-shield-check fs-3" style="color:#2e8b57;"></i>
                        <div>
                            <div style="font-weight:700;color:#2e8b57;font-size:.9rem;">Nenhum risco crítico</div>
                            <div class="lp-subtext" style="font-size:.78rem;">Riscos monitorados dentro do limite aceitável</div>
                        </div>
                    </div>
                    @endif

                    <div class="mt-3 pt-3 border-top">
                        <p class="lp-subtext mb-0" style="font-size:.8rem;">
                            <i class="bi bi-lock-fill me-1"></i>
                            Detalhes completos disponíveis para usuários autenticados.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── MAPA ESTRATÉGICO (read-only) ── --}}
<section class="lp-section-white lp-body" style="padding: 4rem 0;">
    <div class="container">
        <div class="text-center mb-5">
            <div class="lp-section-eyebrow lp-eyebrow mb-2">Visão Integrada</div>
            <h2 class="fw-bold lp-heading" style="font-size:clamp(1.6rem,2.5vw,2.4rem);letter-spacing:-.02em;">
                Mapa Estratégico
            </h2>
            <p class="lp-subtext mx-auto mb-0" style="max-width:560px;font-size:1rem;line-height:1.7;">
                A estratégia completa em uma só visão — as perspectivas do Balanced Scorecard e seus
                objetivos, cada um com seu farol de desempenho no exercício {{ date('Y') }}.
            </p>
        </div>

        @php $coresNivelMapa = [1 => '#475569', 2 => '#2e8b57', 3 => '#0891b2', 4 => '#d97706', 5 => '#1B408E']; @endphp

        @foreach($perspectivas as $persp)
            @php $corBandMapa = $coresNivelMapa[$persp->num_nivel_hierarquico_apresentacao] ?? '#1B408E'; @endphp
            <div class="lp-map-row lp-card-surface">
                <div class="lp-map-band" style="background:{{ $corBandMapa }};">
                    <span class="lp-map-band-name">{{ $persp->dsc_perspectiva }}</span>
                    <span class="lp-map-band-pct">{{ $persp->atingimento_medio }}%</span>
                </div>
                <div class="lp-map-objs">
                    @forelse($persp->objetivos as $obj)
                        <div class="lp-map-obj" style="border-left:3px solid {{ $obj->lp_cor }};">
                            <span class="lp-map-obj-dot" style="background:{{ $obj->lp_cor }};"></span>
                            <span class="lp-map-obj-name">{{ $obj->nom_objetivo }}</span>
                            <span class="lp-map-obj-pct" style="color:{{ $obj->lp_cor }};">{{ $obj->lp_atingimento }}%</span>
                        </div>
                    @empty
                        <span class="lp-subtext" style="font-size:.8rem;font-style:italic;">Sem objetivos vinculados a esta perspectiva.</span>
                    @endforelse
                </div>
            </div>
        @endforeach

        {{-- Legenda dos graus de satisfação --}}
        @if($grausSatisfacao && $grausSatisfacao->isNotEmpty())
        <div class="d-flex flex-wrap gap-3 justify-content-center mt-4">
            @foreach($grausSatisfacao as $g)
                <span class="lp-subtext" style="font-size:.75rem;">
                    <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $g->cor }};margin-right:5px;vertical-align:middle;"></span>
                    {{ $g->dsc_grau_satisfcao ?? $g->dsc_grau_satisfacao ?? '' }}
                </span>
            @endforeach
        </div>
        @endif
    </div>
</section>

{{-- ── CTA LOGIN (modo dashboard) ── --}}
<section class="lp-section-light lp-body" style="padding: 4rem 0;">
    <div class="container">
        <div class="lp-card-surface rounded-4 p-5 text-center"
             style="background:linear-gradient(135deg,#0d1b2e,#1B408E);border:none;">
            <div style="width:60px;height:60px;background:rgba(255,255,255,.15);border-radius:1.25rem;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                <i class="bi bi-graph-up-arrow text-white fs-3"></i>
            </div>
            <h3 class="fw-bold text-white mb-2" style="font-size:1.6rem;letter-spacing:-.02em;">
                Acesse o painel completo
            </h3>
            <p style="color:rgba(255,255,255,.72);max-width:480px;margin:0 auto 2rem;font-size:1rem;line-height:1.7;">
                Indicadores detalhados, planos de ação, relatórios executivos, gestão de riscos
                e muito mais — disponível para usuários autorizados.
            </p>
            <a href="{{ route('login') }}" class="lp-hero-cta" style="font-size:1.05rem;padding:1rem 2.8rem;">
                <i class="bi bi-box-arrow-in-right"></i>
                Entrar no Sistema
            </a>
        </div>
    </div>
</section>

@endif

{{-- ════════════════════════════════════════════════════════════
     SEMPRE PRESENTE — conteúdo original da landing page
════════════════════════════════════════════════════════════ --}}

{{-- ── MÓDULOS GPPEI ── --}}
<section id="modulos" class="lp-section-light lp-body" style="padding: 5rem 0; scroll-margin-top: 80px;">
    <div class="container">
        <div class="text-center mb-5">
            <div class="lp-section-eyebrow lp-eyebrow mb-2">Estrutura Metodológica</div>
            <h2 class="fw-bold lp-heading" style="font-size:clamp(1.8rem,3vw,2.8rem);letter-spacing:-.02em;">
                3 Módulos, 1 Ciclo Completo
            </h2>
            <p class="lp-subtext mx-auto" style="max-width:560px;font-size:1.05rem;line-height:1.7;">
                Metodologia oficial do GPPEI do Ministério da Gestão,
                guiando cada etapa do planejamento estratégico institucional.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="lp-module-card lp-card-surface mod-inaugurar p-4 position-relative">
                    <div class="lp-module-num">01</div>
                    <div class="lp-module-bar"></div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="lp-module-icon"><i class="bi bi-flag-fill"></i></div>
                        <div>
                            <span class="lp-module-badge">Módulo 01</span>
                            <h3 class="fw-bold mb-0 mt-1 lp-heading" style="font-size:1.2rem;">Inaugurar e Integrar</h3>
                        </div>
                    </div>
                    <p class="lp-subtext small mb-3" style="line-height:1.65;">
                        Formalize a demanda e planeje o processo. Integre o PEI aos instrumentos de governo — PPA, LOA, Agenda 2030 e Planos Setoriais.
                    </p>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach(['Planejar o Processo','Integração PPA/LOA','Agenda 2030 / ODS','Calendário de Eventos'] as $t)
                        <span class="lp-feature-tag"><i class="bi bi-check2" style="color:#1a3a5c;"></i>{{ $t }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <div class="lp-module-card lp-card-surface mod-planejar p-4 position-relative">
                    <div class="lp-module-num">02</div>
                    <div class="lp-module-bar"></div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="lp-module-icon"><i class="bi bi-diagram-3-fill"></i></div>
                        <div>
                            <span class="lp-module-badge">Módulo 02</span>
                            <h3 class="fw-bold mb-0 mt-1 lp-heading" style="font-size:1.2rem;">Planejar</h3>
                        </div>
                    </div>
                    <p class="lp-subtext small mb-3" style="line-height:1.65;">
                        Construa a Cadeia de Valor, execute análises SWOT e PESTEL, defina objetivos, indicadores SMART, metas e a carteira de projetos.
                    </p>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach(['Cadeia de Valor','SWOT + GUT','PESTEL','Objetivos BSC','Indicadores SMART','5W2H / RACI'] as $t)
                        <span class="lp-feature-tag"><i class="bi bi-check2" style="color:#2e6da4;"></i>{{ $t }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <div class="lp-module-card lp-card-surface mod-monitorar p-4 position-relative">
                    <div class="lp-module-num">03</div>
                    <div class="lp-module-bar"></div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="lp-module-icon"><i class="bi bi-graph-up-arrow"></i></div>
                        <div>
                            <span class="lp-module-badge">Módulo 03</span>
                            <h3 class="fw-bold mb-0 mt-1 lp-heading" style="font-size:1.2rem;">Monitorar e Avaliar</h3>
                        </div>
                    </div>
                    <p class="lp-subtext small mb-3" style="line-height:1.65;">
                        Acompanhe indicadores em tempo real, registre revisões estratégicas (RAE), gere relatórios executivos e tome decisões baseadas em dados.
                    </p>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach(['Dashboard Executivo','Evolução de KPIs','RAE com PDF','Mapa Estratégico','Alertas de Risco','Relatórios'] as $t)
                        <span class="lp-feature-tag"><i class="bi bi-check2" style="color:#2e8b57;"></i>{{ $t }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── FUNCIONALIDADES ── --}}
<section id="funcionalidades" class="lp-section-white lp-body" style="padding: 4rem 0; scroll-margin-top: 80px;">
    <div class="container">
        <div class="text-center mb-5">
            <div class="lp-section-eyebrow lp-eyebrow mb-2">Funcionalidades</div>
            <h2 class="fw-bold lp-heading" style="font-size:clamp(1.6rem,2.5vw,2.4rem);letter-spacing:-.02em;">
                Tudo que sua equipe precisa em um só lugar
            </h2>
        </div>
        <div class="row g-3">
            @php
            $features = [
                ['icon'=>'bullseye',           'color'=>'#1B408E','bg'=>'#e8f0ff','title'=>'Objetivos Estratégicos',  'desc'=>'Estruture objetivos por perspectiva BSC vinculados ao mapa estratégico.'],
                ['icon'=>'graph-up',           'color'=>'#2e8b57','bg'=>'#e8f5ef','title'=>'Indicadores de Desempenho','desc'=>'Defina KPIs com metas SMART, linha de base e evolução mensal.'],
                ['icon'=>'list-task',          'color'=>'#e07b39','bg'=>'#fdf0e5','title'=>'Planos e Entregas',        'desc'=>'Gerencie projetos com Modelo Lógico, 5W2H, RACI e quadro Kanban.'],
                ['icon'=>'shield-exclamation', 'color'=>'#dc3545','bg'=>'#fde8ea','title'=>'Gestão de Riscos',         'desc'=>'Mapeie riscos por probabilidade × impacto e defina mitigações.'],
                ['icon'=>'arrow-repeat',       'color'=>'#6a4c9c','bg'=>'#f0ebfa','title'=>'RAE — Revisão Estratégica','desc'=>'Registre revisões periódicas com destaques e encaminhamentos.'],
                ['icon'=>'map-fill',           'color'=>'#1a3a5c','bg'=>'#e5ecf5','title'=>'Mapa Estratégico',        'desc'=>'Visualize a estratégia em diagrama BSC interativo com indicadores de farol.'],
                ['icon'=>'file-earmark-pdf',   'color'=>'#dc3545','bg'=>'#fde8ea','title'=>'Relatórios Executivos',   'desc'=>'Exporte relatórios completos em PDF e Excel para a Alta Direção.'],
                ['icon'=>'people-fill',        'color'=>'#0891B2','bg'=>'#e0f4f8','title'=>'Partes Interessadas',      'desc'=>'Mapeie stakeholders por influência × interesse e planeje engajamento.'],
                ['icon'=>'lightbulb-fill',     'color'=>'#f5a623','bg'=>'#fef5e5','title'=>'Lições Aprendidas',       'desc'=>'Registre aprendizados, problemas e boas práticas entre projetos.'],
            ];
            @endphp
            @foreach($features as $f)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="lp-feat-card lp-feat-card-bg">
                    <div class="lp-feat-icon" style="background:{{ $f['bg'] }};">
                        <i class="bi bi-{{ $f['icon'] }}" style="color:{{ $f['color'] }};"></i>
                    </div>
                    <h6 class="fw-bold mb-2 lp-heading">{{ $f['title'] }}</h6>
                    <p class="lp-subtext small mb-0" style="line-height:1.6;">{{ $f['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── CTA FINAL ── --}}
<section class="lp-section-light lp-body" style="padding: 5rem 0;">
    <div class="container text-center">
        <h2 class="fw-bold lp-heading mb-3" style="font-size:clamp(1.8rem,3vw,2.8rem);letter-spacing:-.02em;">
            Pronto para começar?
        </h2>
        <p class="lp-subtext mb-5 mx-auto" style="max-width:500px;font-size:1.05rem;line-height:1.7;">
            Acesse o sistema com suas credenciais e inicie o ciclo de Planejamento Estratégico da sua organização.
        </p>
        <a href="{{ route('login') }}" class="lp-hero-cta" style="font-size:1.05rem;padding:1rem 2.8rem;">
            <i class="bi bi-box-arrow-in-right"></i>
            Entrar no Sistema
        </a>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════════
     SEMPRE PRESENTE — GPPEI BAND + FOOTER
════════════════════════════════════════════════════════════ --}}

{{-- ── GPPEI Band ── --}}
<section class="lp-gppei-band py-5 lp-body">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-8">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="background:rgba(255,255,255,.2);border-radius:.75rem;padding:.75rem;">
                        <i class="bi bi-book-half text-white fs-3"></i>
                    </div>
                    <div>
                        <div style="font-size:.75rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.7);">Referência Metodológica</div>
                        <h3 class="fw-bold text-white mb-0">Guia Prático de PEI — GPPEI/MGI 2025</h3>
                    </div>
                </div>
                <p class="mb-0" style="color:rgba(255,255,255,.85);font-size:1.05rem;line-height:1.65;">
                    Todo o sistema é fundamentado no Guia Prático de Planejamento Estratégico Institucional,
                    documento oficial do Ministério da Gestão e da Inovação em Serviços Públicos (MGI).
                </p>
            </div>
            <div class="col-12 col-lg-4 text-lg-end">
                <a href="{{ route('documentos.viewer-gppei') }}"
                   style="display:inline-flex;align-items:center;gap:.6rem;background:rgba(255,255,255,.2);border:1.5px solid rgba(255,255,255,.4);color:#fff;font-weight:700;padding:.85rem 2rem;border-radius:999px;text-decoration:none;transition:all .25s ease;font-size:.95rem;"
                   onmouseover="this.style.background='rgba(255,255,255,.3)'"
                   onmouseout="this.style.background='rgba(255,255,255,.2)'">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                    Abrir Guia GPPEI
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ── Footer ── --}}
<footer class="lp-footer py-4 lp-body">
    <div class="container">
        <div class="row align-items-center g-2">
            <div class="col-12 col-md-6 mb-2 mb-md-0">
                <span style="font-size:.85rem;">Sistema de Planejamento Estratégico Institucional</span>
                <span class="mx-2" style="opacity:.3;">·</span>
                <span style="font-size:.85rem;">Alinhado ao GPPEI/MGI 2025</span>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <a href="{{ route('login') }}" style="color:rgba(255,255,255,.5);font-size:.85rem;text-decoration:none;" class="me-3">
                    Entrar no Sistema
                </a>
                <a href="{{ route('documentos.viewer-gppei') }}" style="color:rgba(255,255,255,.5);font-size:.85rem;text-decoration:none;">
                    Guia GPPEI
                </a>
            </div>
        </div>
    </div>
</footer>

</div>
