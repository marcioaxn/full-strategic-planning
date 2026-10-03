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
    // Paleta de cores por módulo — seção 9.1 do Documento Mestre, com os tons
    // escurecidos o bastante para o texto branco passar a WCAG (4,5:1) em
    // toda a faixa. Os tons claros originais (ex.: #4a8cc8, #f0974f) davam
    // 3,6:1 ou menos mesmo com branco puro.
    $palette = [
        'inaugurar'   => ['c1' => '#1a3a5c', 'c2' => '#2e5a8c', 'label' => 'Módulo 01 · Inaugurar e Integrar'],
        'cadeia-valor'=> ['c1' => '#2e6da4', 'c2' => '#2f6fa8', 'label' => 'Módulo 02 · Planejar'],
        'ambiental'   => ['c1' => '#1a7a8a', 'c2' => '#1b7f90', 'label' => 'Módulo 02 · Planejar'],
        'referencial' => ['c1' => '#3a5ca8', 'c2' => '#4a6cbd', 'label' => 'Módulo 02 · Planejar'],
        'indicadores' => ['c1' => '#24764a', 'c2' => '#2b7f4c', 'label' => 'Módulo 02 · Planejar'],
        'carteira'    => ['c1' => '#b55a1f', 'c2' => '#a85418', 'label' => 'Módulo 02 · Planejar'],
        'monitorar'   => ['c1' => '#6a4c9c', 'c2' => '#7a5bb0', 'label' => 'Módulo 03 · Monitorar e Avaliar'],
        'ferramentas' => ['c1' => '#4a6080', 'c2' => '#566f93', 'label' => 'Caixa de Ferramentas'],
    ];
    $p = $palette[$module] ?? $palette['cadeia-valor'];
@endphp

<div class="module-header-banner mb-4"
     style="background: linear-gradient(120deg, {{ $p['c1'] }} 0%, {{ $p['c2'] }} 100%);
            border-radius: 1rem; padding: 1.5rem 1.75rem; position: relative; overflow: hidden;
            box-shadow: 0 8px 24px {{ $p['c1'] }}33;">

    {{-- Número decorativo de fundo --}}
    @if($numero)
        <span aria-hidden="true" style="position:absolute; top:-1.5rem; right:1rem; font-size:8rem; font-weight:900;
                     color:rgba(255,255,255,.08); line-height:1; font-variant-numeric:tabular-nums; pointer-events:none;">
            {{ $numero }}
        </span>
    @endif

    <div class="position-relative">
        {{-- Breadcrumb + label do módulo --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <span style="font-size:.7rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#fff;">
                {{ $p['label'] }}
            </span>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider-color:rgba(255,255,255,.4);">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" wire:navigate style="color:#fff; text-decoration:underline; text-underline-offset:2px;">Dashboard</a></li>
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
                        <p class="mb-0" style="color:#fff; font-size:.9rem;">{{ $subtitle }}</p>
                    @endif
                    @if($gppei || $projetos)
                        <div class="d-flex gap-2 mt-1">
                            @if($gppei)
                                <a href="{{ route('documentos.gppei') }}#page={{ $gppei }}" target="_blank" rel="noopener"
                                   class="d-inline-flex align-items-center gap-1 text-decoration-none"
                                   style="background:rgba(0,0,0,.22); border-radius:999px; padding:.15rem .6rem;">
                                    <i class="bi bi-book-half text-white" style="font-size:.7rem;"></i>
                                    <span style="color:#fff; font-size:.7rem; font-weight:600;">GPPEI p.{{ $gppei }}</span>
                                </a>
                            @endif
                            @if($projetos)
                                <a href="{{ route('documentos.projetos') }}#page={{ $projetos }}" target="_blank" rel="noopener"
                                   class="d-inline-flex align-items-center gap-1 text-decoration-none"
                                   style="background:rgba(0,0,0,.22); border-radius:999px; padding:.15rem .6rem;">
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
