<div class="mapa-canvas" wire:poll.10s>
    @guest
        <!-- Navbar Pública para Visitantes -->
        <nav class="navbar navbar-expand-lg fixed-top public-navbar border-bottom shadow-sm">
            <div class="container-fluid px-4">
                <a class="navbar-brand fw-bold d-flex align-items-center" href="/">
                    <div class="icon-shape gradient-theme-icon rounded-circle p-2 me-2 shadow-sm">
                        <i class="bi bi-diagram-3 fs-5 text-white"></i>
                    </div>
                    <span class="brand-text-primary text-body">SEAE</span>
                    <span class="brand-text-secondary ms-2 small d-none d-md-inline text-muted">| Planejamento Estratégico</span>
                </a>

                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublic">
                    <i class="bi bi-list fs-4"></i>
                </button>

                <div class="collapse navbar-collapse" id="navbarPublic">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                        <li class="nav-item dropdown me-lg-2">
                            <button class="btn btn-theme-toggle dropdown-toggle border-0" type="button" id="themeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-circle-half fs-5"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="themeDropdown">
                                <li><button class="dropdown-item d-flex align-items-center" onclick="setTheme('light')"><i class="bi bi-sun me-2"></i> Claro</button></li>
                                <li><button class="dropdown-item d-flex align-items-center" onclick="setTheme('dark')"><i class="bi bi-moon-stars me-2"></i> Escuro</button></li>
                                <li><button class="dropdown-item d-flex align-items-center" onclick="setTheme('system')"><i class="bi bi-circle-half me-2"></i> Sistema</button></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link-public active" href="/"><i class="bi bi-house-door me-1"></i>Início</a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a href="{{ route('login') }}" class="btn gradient-theme-btn px-4 shadow-sm text-white">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        @include('livewire.pei.partials.mapa-navbar-styles')
        <div style="margin-top: 90px;"></div>
    @endguest

    @auth
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none" wire:navigate>Dashboard</a></li>
                            <li class="breadcrumb-item active opacity-75" aria-current="page">Mapa Estratégico</li>
                        </ol>
                    </nav>
                    <h2 class="h4 fw-bold mb-0 text-body">Mapa Estratégico Institucional</h2>
                </div>
                <div class="text-end">
                    <span class="badge bg-surface text-primary border shadow-sm px-3 py-2 rounded-pill">
                        <i class="bi bi-calendar3 me-2"></i>Ciclo: {{ $peiAtivo?->dsc_pei ?? 'N/A' }}
                    </span>
                </div>
            </div>
        </x-slot>
    @endauth

    @if(!$peiAtivo)
        <div class="container py-5">
            <div class="alert alert-modern alert-danger shadow-sm border-0 d-flex align-items-center p-4">
                <i class="bi bi-exclamation-octagon fs-2 me-4 text-danger"></i>
                <div>
                    <h5 class="alert-heading fw-bold mb-1 text-danger">Nenhum PEI Ativo</h5>
                    <p class="mb-0 text-body">Não foi possível carregar o mapa estratégico pois não há um ciclo ativo configurado.</p>
                </div>
            </div>
        </div>
    @else
        <div class="container-fluid px-lg-5 py-3 mb-0 pb-0">
            <!-- Título Organizacional -->
            <div class="text-center mb-4 mt-2">
                <h5 class="fw-bold text-uppercase letter-spacing-2 text-muted-custom mb-3">
                    Mapa Estratégico
                </h5>
                <h5 class="fw-bold text-uppercase letter-spacing-2 text-muted-custom">
                    <i class="bi bi-building me-2"></i>{{ $organizacaoNome }}
                </h5>
                <div class="divider-center"></div>
            </div>

            <!-- ========== IDENTIDADE ESTRATÉGICA ========== -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="identity-box shadow-sm border h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle-mini bg-primary bg-opacity-10 text-primary me-2"><i class="bi bi-bullseye"></i></div>
                            <label class="identity-label mb-0">Missão</label>
                        </div>
                        <p class="identity-text">"{{ $missaoVisao->dsc_missao ?? 'O propósito fundamental da organização.' }}"</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="identity-box shadow-sm border h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle-mini bg-info bg-opacity-10 text-info me-2"><i class="bi bi-eye"></i></div>
                            <label class="identity-label mb-0">Visão</label>
                        </div>
                        <p class="identity-text">"{{ $missaoVisao->dsc_visao ?? 'O futuro desejado pela instituição.' }}"</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="identity-box shadow-sm border h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle-mini bg-danger bg-opacity-10 text-danger me-2"><i class="bi bi-gem"></i></div>
                            <label class="identity-label mb-0">Valores</label>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            @forelse($valores as $valor)
                                <span class="value-tag-modern shadow-sm" title="{{ $valor->dsc_valor }}">{{ $valor->nom_valor }}</span>
                            @empty
                                <span class="text-muted small italic opacity-50">Valores não definidos.</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Card de Objetivos Estratégicos --}}
                <div class="col-12">
                    <div class="identity-box shadow-sm border h-100 text-center">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="icon-circle-mini bg-warning bg-opacity-10 text-warning me-2"><i class="bi bi-shield-check"></i></div>
                            <label class="identity-label mb-0">Objetivos Estratégicos</label>
                        </div>
                        <div class="d-flex flex-wrap gap-3 mt-2 justify-content-center">
                            @forelse($objetivosEstrategicos as $obj)
                                <span class="value-tag-modern shadow-sm border-warning border-opacity-50 py-2 px-4" style="background: rgba(var(--bs-warning-rgb), 0.05); font-size: 1.1rem;">
                                    <i class="bi bi-check2-circle text-warning me-2"></i> {{ $obj->nom_objetivo_estrategico }}
                                </span>
                            @empty
                                <span class="text-muted small italic opacity-50">Objetivos estratégicos não definidos para esta unidade.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== MAPA DE PERSPECTIVAS ========== -->
            <div class="mapa-wrapper">
                @php $totalPersp = count($perspectivas); @endphp
                @foreach($perspectivas as $index => $p)
                    @php
                        $isLast = ($index === $totalPersp - 1);
                        $corSatisfacao = $p['cor_satisfacao'];
                        $coresRef = $this->getCoresPerspectiva($p['num_nivel_hierarquico_apresentacao']);
                        $corBordaRef = str_replace(['bg-', 'border-'], '', $coresRef['border']);
                    @endphp

                    <div class="perspectiva-row mb-2">
                        <div class="card shadow-sm perspectiva-full-card" style="border: 2px solid var(--bs-{{ $corBordaRef }}) !important;">
                            <!-- Header da Perspectiva -->
                            <div class="perspectiva-header-modern px-4 py-3 d-flex justify-content-between align-items-center"
                                 style="background-color: color-mix(in srgb, var(--bs-{{ $corBordaRef }}-bg-subtle), white 77%) !important; border-bottom: 2px solid var(--bs-{{ $corBordaRef }}) !important;">
                                <div class="persp-title-group">
                                    <h5 class="persp-name text-uppercase fw-800 mb-0">{{ $p['dsc_perspectiva'] }}</h5>
                                </div>
                                
                                <div class="d-flex align-items-center gap-3">
                                    <button class="btn-info-calc shadow-sm border" wire:click="abrirMemoriaCalculo({{ $index }})" title="Ver Memória de Cálculo">
                                        <i class="bi bi-info-circle text-muted"></i>
                                    </button>

                                    <div class="performance-badge-modern shadow-sm" style="background-color: {{ $corSatisfacao }};">
                                        <i class="bi bi-graph-up-arrow me-1"></i> @brazil_percent($p['atingimento_medio'], 1)
                                    </div>
                                </div>
                            </div>

                            <div class="perspectiva-body-modern p-3">
                                <div class="row g-3">
                                    @forelse($p['objetivos'] as $objetivo)
                                        @php
                                            $ind = $objetivo['resumo_indicadores'];
                                            $pln = $objetivo['resumo_planos'];
                                        @endphp
                                        <div class="col-md-4 col-lg-3">
                                            <div class="objetivo-card-modern shadow-sm border h-100" 
                                                 @auth onclick="window.location.href='{{ route('objetivos.index') }}?search={{ urlencode($objetivo['nom_objetivo']) }}'" @endauth>
                                                <div class="obj-content p-3">
                                                    <p class="obj-title mb-3" title="{{ $objetivo['nom_objetivo'] }}">
                                                        {{ Str::limit($objetivo['nom_objetivo'], 70) }}
                                                    </p>
                                                    
                                                    {{-- Indicadores --}}
                                                    <div class="obj-stat-box mb-2">
                                                        <a wire:navigate href="{{ route('indicadores.index', ['filtroObjetivo' => $objetivo['cod_objetivo']]) }}" 
                                                           class="text-decoration-none indicador-link" @auth onclick="event.stopPropagation();" @endauth>
                                                            <div class="d-flex justify-content-between mb-1 align-items-center">
                                                                <span class="stat-label-modern"><i class="bi bi-graph-up me-1"></i>Indicadores</span>
                                                                <span class="stat-value-modern" style="color: {{ $ind['cor'] }};">@brazil_percent($ind['percentual'], 1)</span>
                                                            </div>
                                                            <div class="stat-progress-container bg-light-custom">
                                                                <div class="stat-progress-fill" style="width: {{ min($ind['percentual'], 100) }}%; background-color: {{ $ind['cor'] }};"></div>
                                                            </div>
                                                        </a>
                                                    </div>

                                                    {{-- Planos --}}
                                                    <div class="obj-stat-box">
                                                        <a wire:navigate href="{{ route('planos.index', ['filtroObjetivo' => $objetivo['cod_objetivo']]) }}" 
                                                           class="text-decoration-none plano-link" @auth onclick="event.stopPropagation();" @endauth>
                                                            <div class="d-flex justify-content-between mb-1 align-items-center">
                                                                <span class="stat-label-modern"><i class="bi bi-list-check me-1"></i>Planos</span>
                                                                <span class="stat-value-modern" style="color: {{ $pln['cor'] }};">{{ $pln['concluidos'] }}/{{ $pln['quantidade'] }}</span>
                                                            </div>
                                                            <div class="stat-progress-container bg-light-custom">
                                                                <div class="stat-progress-fill" style="width: {{ min($pln['percentual'], 100) }}%; background-color: {{ $pln['cor'] }};"></div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center py-4 opacity-50 small italic">Nenhum objetivo definido.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!$isLast)
                        <div class="mapa-connector">
                            <div class="connector-arrow-modern"><i class="bi bi-caret-up-fill" style="font-size: 2.1rem!Important;"></i></div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Legenda Refinada --}}
            <div class="legenda-wrapper mt-5 mb-4">
                <div class="d-flex flex-column gap-3 px-4 py-3 bg-white bg-opacity-50 rounded-4 shadow-sm">
                    <div class="d-flex align-items-center justify-content-center flex-wrap gap-4">
                        <span class="small fw-bold text-muted text-uppercase letter-spacing-1">Desempenho (Indicadores):</span>
                        @foreach($grausSatisfacao as $grau)
                            <div class="d-flex align-items-center">
                                <span class="legenda-color-dot me-2 shadow-sm" style="background-color: {{ $grau->cor }};"></span>
                                <small class="text-body fw-medium">{{ $grau->dsc_grau_satisfcao }} <span class="text-muted fw-normal" style="font-size: 0.9rem;">( @brazil_number($grau->vlr_minimo, 2) - @brazil_percent($grau->vlr_maximo, 2) )</span></small>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex align-items-center justify-content-center flex-wrap gap-4 border-top pt-3">
                        <span class="small fw-bold text-muted text-uppercase letter-spacing-1">Status (Planos de Ação):</span>
                        @foreach(\App\Models\ActionPlan\PlanoDeAcao::getStatusLegend() as $item)
                            <div class="d-flex align-items-center">
                                <span class="legenda-color-dot me-2 shadow-sm" style="background-color: {{ $item['color'] }};"></span>
                                <small class="text-body fw-medium">{{ $item['label'] }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Memória de Cálculo --}}
    @if($showCalcModal && $detalhesCalculo)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); backdrop-filter: blur(8px);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-body">
                    <div class="modal-header border-0 pb-0 px-4 pt-4">
                        <h5 class="modal-title fw-bold text-body">Memória de Cálculo</h5>
                        <button type="button" class="btn-close" wire:click="fecharMemoriaCalculo"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-4 p-4 rounded-4 bg-body-tertiary border d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-bold text-uppercase letter-spacing-1">Perspectiva</span>
                                <h4 class="fw-bold mb-0 text-primary">{{ $detalhesCalculo['titulo'] }}</h4>
                            </div>
                            <div class="text-end">
                                <span class="text-muted small fw-bold text-uppercase letter-spacing-1">Média de Atingimento</span>
                                <h2 class="fw-800 mb-0" style="color: {{ $detalhesCalculo['cor'] }};">@brazil_percent($detalhesCalculo['media'], 1)</h2>
                            </div>
                        </div>

                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover mb-0">
                                <thead class="bg-body-secondary">
                                    <tr class="small text-muted text-uppercase fw-bold">
                                        <th class="border-0 px-3">Objetivo</th>
                                        <th class="border-0">Indicador</th>
                                        <th class="border-0 text-end px-3">Atingimento</th>
                                    </tr>
                                </thead>
                                <tbody class="text-body">
                                    @foreach($detalhesCalculo['indicadores'] as $item)
                                        <tr>
                                            <td class="small fw-bold px-3">{{ $item['objetivo'] }}</td>
                                            <td class="small opacity-75">{{ $item['indicador'] }}</td>
                                            <td class="text-end fw-800 px-3" style="color: {{ $item['cor'] }};">@brazil_percent($item['atingimento'], 1)</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-secondary px-4 rounded-pill fw-bold" wire:click="fecharMemoriaCalculo">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .mapa-canvas { background-color: var(--bs-body-bg); color: var(--bs-body-color); min-height: 100vh; }
        
        /* Utils */
        .fw-800 { font-weight: 800; }
        .letter-spacing-1 { letter-spacing: 1px; }
        .letter-spacing-2 { letter-spacing: 2px; }
        .divider-center { width: 60px; height: 4px; background: var(--bs-primary); margin: 15px auto; border-radius: 10px; opacity: 0.3; }
        .text-muted-custom { color: var(--bs-secondary); opacity: 0.8; }
        .bg-surface { background-color: var(--bs-body-bg); }
        .bg-light-custom { background-color: rgba(var(--bs-secondary-rgb), 0.1); }

        /* Identidade */
        .identity-box { background: var(--bs-body-bg); padding: 25px; border-radius: 16px; border-color: rgba(var(--bs-secondary-rgb), 0.1) !important; transition: all 0.3s; }
        .identity-box:hover { transform: translateY(-3px); border-color: var(--bs-primary) !important; box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important; }
        .identity-label { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--bs-secondary); display: block; letter-spacing: 0.05em; }
        .identity-text { font-size: 1rem; line-height: 1.6; color: var(--bs-body-color); margin-bottom: 0; font-style: italic; opacity: 0.9; }
        .icon-circle-mini { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; }
        
        .value-tag-modern { display: inline-block; padding: 4px 14px; background: var(--bs-body-bg); border-radius: 10px; font-size: 0.8rem; color: var(--bs-body-color); border: 1px solid rgba(var(--bs-secondary-rgb), 0.2); font-weight: 600; }

        /* Perspectivas */
        .perspectiva-full-card { background-color: var(--bs-body-bg); border-radius: 9px; overflow: hidden; }
        .perspectiva-header-modern { border-bottom-width: 2px !important; }
        .persp-name { font-size: 1.2rem; color: var(--bs-body-color); }
        
        .performance-badge-modern { padding: 6px 18px; border-radius: 100px; color: white; font-weight: 600; font-size: 0.85rem; }
        .btn-info-calc { border: none; background: var(--bs-body-bg); width: 34px; height: 34px; border-radius: 50%; transition: all 0.2s; display: flex; align-items: center; justify-content: center; border-color: rgba(var(--bs-secondary-rgb), 0.1) !important; }
        .btn-info-calc:hover { background: var(--bs-body-tertiary); transform: scale(1.1); }

        /* Objetivos */
        .objetivo-card-modern { background: var(--bs-body-bg); border-radius: 14px; border-color: rgba(var(--bs-secondary-rgb), 0.15) !important; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; }
        .objetivo-card-modern:hover { transform: translateY(-5px); border-color: var(--bs-primary) !important; box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; }
        .obj-title { font-weight: 700; font-size: 1rem; line-height: 1.4; color: var(--bs-body-color); min-height: 2.8rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        
        .stat-label-modern { font-size: 0.65rem; font-weight: 700; color: var(--bs-secondary); text-transform: uppercase; letter-spacing: 0.02em; }
        .stat-value-modern { font-size: 0.75rem; font-weight: 800; }
        .stat-progress-container { height: 5px; border-radius: 10px; overflow: hidden; }
        .stat-progress-fill { height: 100%; border-radius: 10px; transition: width 0.8s ease; }

        /* Links Interativos */
        .indicador-link, .plano-link { display: block; padding: 6px; margin: -6px; border-radius: 10px; transition: all 0.2s; }
        .indicador-link:hover, .plano-link:hover { background-color: rgba(var(--bs-primary-rgb), 0.08); transform: translateX(4px); }

        /* Conectores */
        .mapa-connector { display: flex; flex-direction: column; align-items: center; position: relative; z-index: 1; }
        .connector-line-modern { width: 3px; height: 18px; background: var(--bs-secondary); opacity: 0.15; border-radius: 10px; }
        .connector-arrow-modern { color: var(--bs-secondary); opacity: 0.3; font-size: 1.4rem; margin-top: -12px; }

        /* Legenda */
        .legenda-color-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }

        /* Dark Mode Ajustes Finos */
        [data-bs-theme="dark"] .identity-box { background: rgba(255,255,255,0.02); }
        [data-bs-theme="dark"] .objetivo-card-modern { background: rgba(255,255,255,0.03); }
        [data-bs-theme="dark"] .perspectiva-full-card { border-color: rgba(255,255,255,0.1) !important; }
        [data-bs-theme="dark"] .connector-line-modern { background: white; opacity: 0.1; }
        [data-bs-theme="dark"] .connector-arrow-modern { color: white; opacity: 0.2; }
    </style>
</div>
