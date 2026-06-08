{{-- Painel institucional esquerdo, idêntico ao da tela de login.
     Parâmetros: $tituloHtml (aceita <span> para gradiente) e $lead. --}}
@php
    $tituloHtml = $tituloHtml ?? 'Bem-vindo à sua<br>central de <span>governança</span><br>estratégica.';
    $lead = $lead ?? 'Gerencie ciclos PEI, monitore indicadores SMART e execute planos de ação com o rigor metodológico que a Administração Pública Federal exige.';
@endphp
<div class="lp-login-left d-none d-xl-flex flex-column justify-content-between p-5">

    {{-- Brand --}}
    <a href="{{ url('/') }}" class="d-flex align-items-center gap-3 text-decoration-none">
        <div style="width:46px;height:46px;background:linear-gradient(135deg,#1B408E,#2e5aa8);border-radius:.875rem;
                    display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(27,64,142,.25);">
            <i class="bi bi-diagram-3 text-white" style="font-size:1.3rem;"></i>
        </div>
        <div>
            <div class="lp-login-bname">SEAE</div>
            <div class="lp-login-bsub">Sistema de Gestão Estratégica</div>
        </div>
    </a>

    {{-- Centro --}}
    <div>
        <div class="lp-hero-badge mb-4">
            <i class="bi bi-patch-check-fill" style="font-size:.9rem;color:#e07b39;"></i>
            GPPEI/MGI 2025 · Gestão Pública Federal
        </div>
        <h1 class="lp-login-title mb-3">{!! $tituloHtml !!}</h1>
        <p class="lp-login-lead mb-5">{{ $lead }}</p>

        <div class="position-relative">
            @foreach([
                ['num'=>'01','title'=>'Inaugurar e Integrar','sub'=>'Planejar o processo · PPA/LOA/ODS','icon'=>'flag-fill','c'=>'#1a3a5c','tx'=>0],
                ['num'=>'02','title'=>'Planejar','sub'=>'Cadeia de Valor · SWOT · BSC · Planos','icon'=>'diagram-3-fill','c'=>'#1B408E','tx'=>28],
                ['num'=>'03','title'=>'Monitorar e Avaliar','sub'=>'Dashboard · Indicadores · RAE','icon'=>'graph-up-arrow','c'=>'#2e8b57','tx'=>56],
            ] as $m)
            <div class="lp-login-modcard d-flex align-items-center gap-3 mb-2 p-3 rounded-3"
                 style="border-left:4px solid {{ $m['c'] }};transform:translateX({{ $m['tx'] }}px);max-width:390px;">
                <div style="min-width:42px;height:42px;background:{{ $m['c'] }};border-radius:.75rem;
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
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

    {{-- Stats --}}
    <div>
        <div class="lp-stats mb-3">
            <div class="row g-3 text-center">
                <div class="col-4"><div class="lp-stat-value">3</div><div class="lp-stat-label">Módulos GPPEI</div></div>
                <div class="col-4"><div class="lp-stat-value">20+</div><div class="lp-stat-label">Ferramentas</div></div>
                <div class="col-4"><div class="lp-stat-value">100%</div><div class="lp-stat-label">Metodologia APF</div></div>
            </div>
        </div>
        <p style="font-size:.7rem;color:#94a3b8;margin:0;">&copy; {{ date('Y') }} Strategic Planning System. Todos os direitos reservados.</p>
    </div>
</div>
