@props([
    'module'   => 'planejar',   // chave do módulo GPPEI
    'title'    => '',
    'subtitle' => null,
    'icon'     => 'diagram-3',
    'breadcrumb' => null,        // string da página atual no breadcrumb
    'gppei'    => null,          // página do Guia GPPEI
    'projetos' => null,          // página do Guia de Projetos
    'numero'   => null,          // número do módulo (01/02/03) opcional
])

@php
    // Paleta de cores por módulo — seção 9.1 do Documento Mestre
    $palette = [
        'inaugurar'   => ['c1' => '#1a3a5c', 'c2' => '#2e5a8c', 'label' => 'Módulo 01 · Inaugurar e Integrar'],
        'cadeia-valor'=> ['c1' => '#2e6da4', 'c2' => '#4a8cc8', 'label' => 'Módulo 02 · Planejar'],
        'ambiental'   => ['c1' => '#1a7a8a', 'c2' => '#2ba0b3', 'label' => 'Módulo 02 · Planejar'],
        'referencial' => ['c1' => '#3a5ca8', 'c2' => '#5478c8', 'label' => 'Módulo 02 · Planejar'],
        'indicadores' => ['c1' => '#2e8b57', 'c2' => '#3fa86c', 'label' => 'Módulo 02 · Planejar'],
        'carteira'    => ['c1' => '#e07b39', 'c2' => '#f0974f', 'label' => 'Módulo 02 · Planejar'],
        'monitorar'   => ['c1' => '#6a4c9c', 'c2' => '#8868bd', 'label' => 'Módulo 03 · Monitorar e Avaliar'],
        'ferramentas' => ['c1' => '#4a6080', 'c2' => '#647ea3', 'label' => 'Caixa de Ferramentas'],
    ];
    $p = $palette[$module] ?? $palette['cadeia-valor'];
@endphp

<div class="module-header-banner mb-4"
     style="background: linear-gradient(120deg, {{ $p['c1'] }} 0%, {{ $p['c2'] }} 100%);
            border-radius: 1rem; padding: 1.5rem 1.75rem; position: relative; overflow: hidden;
            box-shadow: 0 8px 24px {{ $p['c1'] }}33;">

    {{-- Número decorativo de fundo --}}
    @if($numero)
        <span style="position:absolute; top:-1.5rem; right:1rem; font-size:8rem; font-weight:900;
                     color:rgba(255,255,255,.08); line-height:1; font-variant-numeric:tabular-nums; pointer-events:none;">
            {{ $numero }}
        </span>
    @endif

    <div class="position-relative">
        {{-- Breadcrumb + label do módulo --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <span style="font-size:.7rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:rgba(255,255,255,.7);">
                {{ $p['label'] }}
            </span>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider-color:rgba(255,255,255,.4);">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" wire:navigate style="color:rgba(255,255,255,.7); text-decoration:none;">Dashboard</a></li>
                    @if($breadcrumb)
                        <li class="breadcrumb-item active" aria-current="page" style="color:#fff;">{{ $breadcrumb }}</li>
                    @endif
                </ol>
            </nav>
        </div>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div style="width:54px; height:54px; border-radius:.875rem; background:rgba(255,255,255,.18);
                            display:flex; align-items:center; justify-content:center; flex-shrink:0; backdrop-filter:blur(4px);">
                    <i class="bi bi-{{ $icon }} text-white" style="font-size:1.5rem;"></i>
                </div>
                <div>
                    <h1 class="fw-bold mb-0 text-white" style="font-size:1.5rem; letter-spacing:-.02em;">{{ $title }}</h1>
                    @if($subtitle)
                        <p class="mb-0" style="color:rgba(255,255,255,.78); font-size:.9rem;">{{ $subtitle }}</p>
                    @endif
                    @if($gppei || $projetos)
                        <div class="d-flex gap-2 mt-1">
                            @if($gppei)
                                <a href="{{ route('documentos.gppei') }}#page={{ $gppei }}" target="_blank" rel="noopener"
                                   class="d-inline-flex align-items-center gap-1 text-decoration-none"
                                   style="background:rgba(255,255,255,.15); border-radius:999px; padding:.15rem .6rem;">
                                    <i class="bi bi-book-half text-white" style="font-size:.7rem;"></i>
                                    <span style="color:#fff; font-size:.7rem; font-weight:600;">GPPEI p.{{ $gppei }}</span>
                                </a>
                            @endif
                            @if($projetos)
                                <a href="{{ route('documentos.projetos') }}#page={{ $projetos }}" target="_blank" rel="noopener"
                                   class="d-inline-flex align-items-center gap-1 text-decoration-none"
                                   style="background:rgba(255,255,255,.15); border-radius:999px; padding:.15rem .6rem;">
                                    <i class="bi bi-journal-bookmark-fill text-white" style="font-size:.7rem;"></i>
                                    <span style="color:#fff; font-size:.7rem; font-weight:600;">Projetos p.{{ $projetos }}</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Slot para ações (botões) --}}
            @if(isset($actions))
                <div class="d-flex align-items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    </div>
</div>
