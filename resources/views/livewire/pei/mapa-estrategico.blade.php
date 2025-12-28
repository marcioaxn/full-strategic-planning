<div>
    @guest
        <!-- Navbar Publica para Visitantes - Suporte a Dark Mode -->
        <nav class="navbar navbar-expand-lg fixed-top public-navbar">
            <div class="container-fluid px-4">
                <a class="navbar-brand fw-bold d-flex align-items-center" href="/">
                    <div class="icon-shape gradient-theme-icon rounded-circle p-2 me-2">
                        <i class="bi bi-diagram-3 fs-5"></i>
                    </div>
                    <span class="brand-text-primary">SEAE</span>
                    <span class="brand-text-secondary ms-2 small d-none d-md-inline">| Planejamento Estrategico</span>
                </a>

                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublic">
                    <i class="bi bi-list fs-4"></i>
                </button>

                <div class="collapse navbar-collapse" id="navbarPublic">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                        <!-- Theme Switcher Dropdown -->
                        <li class="nav-item dropdown me-lg-2">
                            <button class="btn btn-theme-toggle dropdown-toggle" type="button" id="themeDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Alternar Tema">
                                <i class="bi bi-circle-half fs-5" id="themeIcon"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="themeDropdown">
                                <li>
                                    <button class="dropdown-item d-flex align-items-center" onclick="setTheme('light')">
                                        <i class="bi bi-sun me-2"></i> Claro
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item d-flex align-items-center" onclick="setTheme('dark')">
                                        <i class="bi bi-moon-stars me-2"></i> Escuro
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item d-flex align-items-center" onclick="setTheme('system')">
                                        <i class="bi bi-circle-half me-2"></i> Sistema
                                    </button>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link-public active" href="/"><i class="bi bi-house-door me-1"></i>Inicio</a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a href="{{ route('login') }}" class="btn gradient-theme-btn px-4">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        @include('livewire.pei.partials.mapa-navbar-styles')
        <div style="margin-top: 80px;"></div>
    @endguest

    @auth
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none" wire:navigate>Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Mapa Estrategico</li>
                        </ol>
                    </nav>
                    <h2 class="h4 fw-bold mb-0">Mapa Estrategico BSC</h2>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-calendar3 me-2"></i>PEI: {{ $peiAtivo?->dsc_pei ?? 'Nenhum ativo' }}
                    </span>
                </div>
            </div>
        </x-slot>
    @endauth

    @if(!$peiAtivo)
        <div class="container py-5">
            <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center p-4" role="alert">
                <i class="bi bi-exclamation-octagon-fill fs-2 me-4"></i>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Nenhum PEI Ativo</h5>
                    <p class="mb-0">Nao foi possivel carregar o mapa estrategico pois nao ha um PEI ativo configurado no momento.</p>
                </div>
            </div>
        </div>
    @else
        <div class="container-fluid px-lg-4 py-3">
            <!-- Organizacao -->
            <div class="text-center mb-4">
                <h5 class="fw-bold text-uppercase letter-spacing-1 text-muted">
                    <i class="bi bi-building me-2"></i>{{ $organizacaoNome }}
                </h5>
            </div>

            <!-- ========== IDENTIDADE ESTRATEGICA ========== -->
            <div class="row g-3 mb-4">
                <!-- Missao -->
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden identity-card">
                        <div class="card-header bg-success text-white py-2">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-bullseye me-2"></i>Missao
                            </h6>
                        </div>
                        <div class="card-body bg-success-subtle">
                            @if($missaoVisao && $missaoVisao->dsc_missao)
                                <p class="mb-0 text-dark">{{ $missaoVisao->dsc_missao }}</p>
                            @else
                                <p class="mb-0 text-muted fst-italic">Missao nao definida</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Visao -->
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden identity-card">
                        <div class="card-header bg-warning text-dark py-2">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-eye me-2"></i>Visao
                            </h6>
                        </div>
                        <div class="card-body bg-warning-subtle">
                            @if($missaoVisao && $missaoVisao->dsc_visao)
                                <p class="mb-0 text-dark">{{ $missaoVisao->dsc_visao }}</p>
                            @else
                                <p class="mb-0 text-muted fst-italic">Visao nao definida</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Valores -->
                @if($valores && count($valores) > 0)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm overflow-hidden identity-card">
                            <div class="card-header bg-danger text-white py-2">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-gem me-2"></i>Valores
                                </h6>
                            </div>
                            <div class="card-body bg-danger-subtle p-3">
                                <div class="row g-2">
                                    @foreach($valores as $valor)
                                        <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
                                            <div class="card h-100 border-0 shadow-sm valor-card">
                                                <div class="card-header bg-danger text-white py-2 px-3">
                                                    <small class="fw-bold">{{ $valor->nom_valor }}</small>
                                                </div>
                                                @if($valor->dsc_valor)
                                                    <div class="card-body py-2 px-3">
                                                        <small class="text-muted">{{ Str::limit($valor->dsc_valor, 80) }}</small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- ========== MAPA DE PERSPECTIVAS E OBJETIVOS ========== -->
            <div class="mapa-perspectivas">
                @php $totalPerspectivas = count($perspectivas); @endphp
                @foreach($perspectivas as $index => $perspectiva)
                    @php
                        $isLast = ($index === $totalPerspectivas - 1);
                        $cores = $this->getCoresPerspectiva($perspectiva['num_nivel_hierarquico_apresentacao']);
                        $corSatisfacao = $perspectiva['cor_satisfacao'];
                    @endphp

                    <div class="card border-2 {{ $cores['border'] }} shadow-sm mb-3 overflow-hidden perspectiva-card">
                        <!-- Header da Perspectiva (Cores de Referência Restauradas) -->
                        <div class="card-header {{ $cores['bg'] }} {{ $cores['text'] }} py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-layers me-2"></i>{{ $perspectiva['dsc_perspectiva'] }}
                            </h5>
                            
                            <div class="d-flex align-items-center gap-2">
                                {{-- Memória de Cálculo (Auditabilidade) --}}
                                <button class="btn btn-link p-0 text-white opacity-75 hover-opacity-100" 
                                        wire:click="abrirMemoriaCalculo({{ $index }})" 
                                        title="{{ __('Ver memória de cálculo') }}">
                                    <i class="bi bi-info-circle fs-5"></i>
                                </button>

                                {{-- Badge de Atingimento com Cor de Satisfação --}}
                                <span class="badge shadow-sm d-flex align-items-center gap-1" 
                                      style="background-color: {{ $corSatisfacao }} !important; color: white !important; border: 1px solid rgba(255,255,255,0.4); font-size: 0.85rem; padding: 0.5rem 0.8rem;">
                                    <i class="bi bi-graph-up"></i> {{ $perspectiva['atingimento_medio'] }}%
                                </span>
                            </div>
                        </div>

                        <!-- Objetivos Estrategicos -->
                        <div class="card-body {{ $cores['bg_light'] }} p-3">
                            @if(count($perspectiva['objetivos']) > 0)
                                <div class="row g-3">
                                    @foreach($perspectiva['objetivos'] as $objetivo)
                                        @php
                                            $indicadoresResumo = $objetivo['resumo_indicadores'];
                                            $planosResumo = $objetivo['resumo_planos'];
                                        @endphp
                                        <div class="col-sm-6 col-md-4 col-lg-3">
                                            <div class="card h-100 border-0 shadow objetivo-card"
                                                 @auth
                                                 style="cursor: pointer;"
                                                 onclick="window.location.href='{{ route('objetivos.index') }}?search={{ urlencode($objetivo['nom_objetivo_estrategico']) }}'"
                                                 @endauth>
                                                <!-- Nome do Objetivo -->
                                                <div class="card-body p-3">
                                                    <p class="fw-semibold text-dark mb-3 objetivo-nome" title="{{ $objetivo['nom_objetivo_estrategico'] }}">
                                                        {{ Str::limit($objetivo['nom_objetivo_estrategico'], 80) }}
                                                    </p>

                                                    <!-- Indicadores -->
                                                    <div class="mb-2">
                                                        <a href="{{ route('indicadores.index', ['filtroObjetivo' => $objetivo['cod_objetivo_estrategico']]) }}"
                                                           class="text-decoration-none indicador-link"
                                                           @guest onclick="event.stopPropagation(); alert('Faça login para acessar os indicadores'); return false;" @endguest
                                                           @auth onclick="event.stopPropagation();" @endauth>
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <small class="text-muted">
                                                                    <i class="bi bi-graph-up me-1"></i>
                                                                    <span class="fw-bold text-dark">{{ $indicadoresResumo['quantidade'] }}</span>
                                                                    {{ $indicadoresResumo['quantidade'] == 1 ? 'indicador' : 'indicadores' }}
                                                                </small>
                                                                <small class="fw-bold" style="color: {{ $indicadoresResumo['cor'] }};">
                                                                    {{ $indicadoresResumo['percentual'] }}%
                                                                </small>
                                                            </div>
                                                            <div class="progress" style="height: 6px;">
                                                                <div class="progress-bar"
                                                                     role="progressbar"
                                                                     style="width: {{ min($indicadoresResumo['percentual'], 100) }}%; background-color: {{ $indicadoresResumo['cor'] }};"
                                                                     aria-valuenow="{{ $indicadoresResumo['percentual'] }}"
                                                                     aria-valuemin="0"
                                                                     aria-valuemax="100"></div>
                                                            </div>
                                                        </a>
                                                    </div>

                                                    <!-- Planos de Acao -->
                                                    <div>
                                                        <a href="{{ route('planos.index', ['filtroObjetivo' => $objetivo['cod_objetivo_estrategico']]) }}"
                                                           class="text-decoration-none plano-link"
                                                           @guest onclick="event.stopPropagation(); alert('Faça login para acessar os planos de ação'); return false;" @endguest
                                                           @auth onclick="event.stopPropagation();" @endauth>
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <small class="text-muted">
                                                                    <i class="bi bi-list-check me-1"></i>
                                                                    <span class="fw-bold text-dark">{{ $planosResumo['quantidade'] }}</span>
                                                                    {{ $planosResumo['quantidade'] == 1 ? 'plano' : 'planos' }}
                                                                </small>
                                                                <small class="fw-bold" style="color: {{ $planosResumo['cor'] }};">
                                                                    {{ $planosResumo['concluidos'] }}/{{ $planosResumo['quantidade'] }}
                                                                </small>
                                                            </div>
                                                            <div class="progress" style="height: 6px;">
                                                                <div class="progress-bar"
                                                                     role="progressbar"
                                                                     style="width: {{ min($planosResumo['percentual'], 100) }}%; background-color: {{ $planosResumo['cor'] }};"
                                                                     aria-valuenow="{{ $planosResumo['percentual'] }}"
                                                                     aria-valuemin="0"
                                                                     aria-valuemax="100"></div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox text-muted fs-1"></i>
                                    <p class="text-muted mb-0 mt-2">Nenhum objetivo estrategico definido para esta perspectiva</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Seta de Conexao entre Perspectivas -->
                    @if(!$isLast)
                        <div class="text-center mb-3">
                            <div class="arrow-connector">
                                <i class="bi bi-arrow-up-circle-fill fs-3 {{ str_replace('bg-', 'text-', $cores['bg']) }}"></i>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- ========== LEGENDA DINAMICA ========== -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <small class="fw-bold text-muted text-uppercase">Grau de Satisfacao:</small>
                        </div>
                        <div class="col">
                            <div class="d-flex flex-wrap gap-3 justify-content-center">
                                @forelse($grausSatisfacao as $grau)
                                    <div class="d-flex align-items-center">
                                        <span class="status-indicator me-2" style="background-color: {{ $grau->cor }};"></span>
                                        <small class="text-muted">{{ $grau->dsc_grau_satisfcao }} ({{ number_format($grau->vlr_minimo, 2, ',', '.') }}-{{ number_format($grau->vlr_maximo, 2, ',', '.') }}%)</small>
                                    </div>
                                @empty
                                    <!-- Fallback caso nao haja graus cadastrados -->
                                    <div class="d-flex align-items-center">
                                        <span class="status-indicator bg-success me-2"></span>
                                        <small class="text-muted">Excelente (100%+)</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="status-indicator bg-primary me-2"></span>
                                        <small class="text-muted">Bom (80-99%)</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="status-indicator bg-info me-2"></span>
                                        <small class="text-muted">Regular (60-79%)</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="status-indicator bg-warning me-2"></span>
                                        <small class="text-muted">Atencao (40-59%)</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="status-indicator bg-danger me-2"></span>
                                        <small class="text-muted">Critico (&lt;40%)</small>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @guest
                <!-- Call to Action Publico -->
                <div class="row mt-5 justify-content-center">
                    <div class="col-lg-8">
                        <div class="card gradient-theme text-white shadow-lg border-0 rounded-4 overflow-hidden">
                            <div class="card-body p-4 p-lg-5 text-center">
                                <h2 class="fw-bold mb-3"><i class="bi bi-unlock me-2"></i>Area Restrita</h2>
                                <p class="opacity-75 mb-4">Acesse o sistema completo para gerenciar indicadores, planos de acao, riscos e auditoria.</p>
                                <a href="{{ route('login') }}" class="btn btn-light btn-lg rounded-pill px-5 fw-bold shadow">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Acessar Painel de Gestao
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endguest
        </div>
    @endif

    {{-- Modal de Memória de Cálculo --}}
    @if($showCalcModal && $detalhesCalculo)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-primary">{{ __('Memória de Cálculo') }}</h5>
                        <button type="button" class="btn-close" wire:click="fecharMemoriaCalculo"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <h6 class="text-muted small text-uppercase fw-bold mb-1">{{ __('Perspectiva') }}</h6>
                            <h4 class="fw-bold">{{ $detalhesCalculo['titulo'] }}</h4>
                        </div>

                        <div class="alert d-flex align-items-center justify-content-between p-3 border-0 rounded-3" style="background-color: {{ $detalhesCalculo['cor'] }}15;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-shape rounded-circle text-white p-3" style="background-color: {{ $detalhesCalculo['cor'] }};">
                                    <i class="bi bi-calculator fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold" style="color: {{ $detalhesCalculo['cor'] }};">{{ __('Desempenho Médio') }}</h6>
                                    <p class="mb-0 small text-muted">{{ __('Média simples do atingimento de todos os indicadores vinculados.') }}</p>
                                </div>
                            </div>
                            <h2 class="fw-bold mb-0" style="color: {{ $detalhesCalculo['cor'] }};">{{ $detalhesCalculo['media'] }}%</h2>
                        </div>

                        <div class="mt-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-list-check me-2"></i>{{ __('Dados Considerados:') }}</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="bg-light small text-muted text-uppercase">
                                        <tr>
                                            <th>{{ __('Objetivo') }}</th>
                                            <th>{{ __('Indicador') }}</th>
                                            <th class="text-end">{{ __('Atingimento') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($detalhesCalculo['indicadores'] as $item)
                                            <tr>
                                                <td class="small fw-semibold">{{ $item['objetivo'] }}</td>
                                                <td class="small">{{ $item['indicador'] }}</td>
                                                <td class="text-end fw-bold" style="color: {{ $item['cor'] }};">
                                                    {{ $item['atingimento'] }}%
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3 p-3 bg-light rounded-3 small text-muted border-start border-4 border-info">
                            <i class="bi bi-info-circle-fill me-2 text-info"></i>
                            {{ __('O cálculo atual reflete o desempenho dos indicadores de resultado. O progresso físico das entregas dos planos de ação é monitorado separadamente para fins de controle operacional.') }}
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary px-4" wire:click="fecharMemoriaCalculo">{{ __('Fechar') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:navigated', () => {
            initPopovers();
        });

        document.addEventListener('DOMContentLoaded', () => {
            initPopovers();
        });

        function initPopovers() {
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
            [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
        }
    </script>

    <style>
        .cursor-help { cursor: help; }
        /* ========== Identidade Estrategica ========== */
        .identity-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .identity-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
        }
        .valor-card {
            transition: transform 0.2s ease;
        }
        .valor-card:hover {
            transform: scale(1.02);
        }

        /* ========== Perspectivas ========== */
        .perspectiva-card {
            transition: box-shadow 0.2s ease;
        }
        .perspectiva-card:hover {
            box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.15) !important;
        }

        /* ========== Objetivos ========== */
        .objetivo-card {
            transition: all 0.3s ease;
            border-left: 4px solid var(--bs-primary) !important;
        }
        .objetivo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.75rem 1.5rem rgba(0,0,0,0.15) !important;
            border-left-color: var(--bs-success) !important;
        }
        .objetivo-nome {
            line-height: 1.4;
            min-height: 3.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* ========== Seta de Conexao ========== */
        .arrow-connector {
            position: relative;
        }
        .arrow-connector::before,
        .arrow-connector::after {
            content: '';
            position: absolute;
            left: 50%;
            width: 2px;
            height: 10px;
            background: var(--bs-secondary);
            transform: translateX(-50%);
        }
        .arrow-connector::before {
            top: -12px;
        }
        .arrow-connector::after {
            bottom: -12px;
        }

        /* ========== Legenda ========== */
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }

        /* ========== Utilitarios ========== */
        .letter-spacing-1 {
            letter-spacing: 1px;
        }

        /* ========== Cores Customizadas ========== */
        .bg-slate {
            background-color: #475569 !important;
        }
        .border-slate {
            border-color: #475569 !important;
        }

        /* ========== Responsividade ========== */
        @media (max-width: 767px) {
            .objetivo-nome {
                min-height: auto;
                -webkit-line-clamp: 4;
            }
        }

        /* ========== Links de Indicadores e Planos ========== */
        .indicador-link,
        .plano-link {
            display: block;
            transition: all 0.2s ease;
            border-radius: 4px;
            padding: 4px;
            margin: -4px;
        }
        .indicador-link:hover,
        .plano-link:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
            transform: translateX(2px);
        }
        .indicador-link:hover small,
        .plano-link:hover small {
            color: var(--bs-primary) !important;
        }

        /* ========== Dark Mode ========== */
        [data-bs-theme="dark"] .objetivo-card {
            background: var(--bs-dark) !important;
        }
        [data-bs-theme="dark"] .objetivo-nome {
            color: var(--bs-light) !important;
        }
        [data-bs-theme="dark"] .identity-card .card-body {
            background: rgba(var(--bs-body-bg-rgb), 0.5) !important;
        }
        [data-bs-theme="dark"] .identity-card .card-body p {
            color: var(--bs-light) !important;
        }
        [data-bs-theme="dark"] .indicador-link:hover,
        [data-bs-theme="dark"] .plano-link:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.15);
        }
    </style>
</div>
