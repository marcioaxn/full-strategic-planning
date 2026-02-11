<div class="mapa-canvas position-relative" wire:key="mapa-v11-{{ $viewMode }}-{{ $organizacaoId }}">
    {{-- Polling discreto --}}
    <div wire:poll.60s="carregarMapa"></div>

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
                <p class="text-muted small mb-0"><i class="bi bi-building me-1"></i>{{ $organizacaoNome }}</p>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="text-end border-start ps-3">
                    <span class="badge bg-body text-primary border shadow-sm px-3 py-2 rounded-pill">
                        <i class="bi bi-calendar3 me-2"></i>Ciclo: {{ $peiAtivo?->dsc_pei ?? 'N/A' }}
                    </span>
                </div>
            </div>
        </div>
    </x-slot>

    @guest
    <div class="container mt-4 mb-4 pt-4 pb-4">

    </div>
    @endguest

    @if(!$peiAtivo)
        <div class="container py-5 text-center">
            <div class="alert alert-modern alert-warning d-inline-block p-4 shadow-sm">
                <i class="bi bi-exclamation-triangle fs-2 d-block mb-2"></i>
                <h5 class="fw-bold">Nenhum PEI Ativo</h5>
                <p class="mb-0">Selecione ou configure um ciclo estratégico para carregar o mapa.</p>
            </div>
        </div>
    @else
        <div class="container-fluid px-lg-5 py-4">
            
            {{-- Título Centralizado --}}
            <div class="text-center mb-5 mt-2 animate-fade-in">
                <h5 class="fw-bold text-uppercase letter-spacing-2 text-muted-custom mb-2">Mapa Estratégico</h5>
                <h3 class="fw-bold text-body-emphasis letter-spacing-1">{{ $organizacaoNome }}</h3>
                <div class="divider-center"></div>
                
                @if($viewMode === 'grouped')
                    <div class="mt-2">
                        <button class="btn btn-sm btn-info bg-opacity-10 text-white border border-info border-opacity-25 rounded-pill px-3 py-2 shadow-sm" 
                                type="button" data-bs-toggle="collapse" data-bs-target="#collapseOrgs" aria-expanded="false" aria-controls="collapseOrgs">
                            <i class="bi bi-diagram-3-fill me-1"></i> Visualização Consolidada ({{ $qtdUnidadesConsolidadas }} unidades)
                            <i class="bi bi-chevron-down small ms-2"></i>
                        </button>
                        
                        <div class="collapse mt-3" id="collapseOrgs">
                            <div class="card card-body border-info border-opacity-25 bg-info bg-opacity-10 shadow-sm rounded-4 mx-auto" style="max-width: 800px;">
                                <h6 class="fw-bold text-info mb-3 text-uppercase small letter-spacing-1">Organizações Incluídas no Cálculo</h6>
                                <div class="d-flex flex-wrap justify-content-center gap-2">
                                    @foreach($organizacoesConsolidadas as $orgConsolidada)
                                        <span class="badge bg-white text-dark border shadow-sm px-3 py-2 rounded-pill fw-medium">
                                            <i class="bi bi-building me-1 text-info"></i>
                                            {{ $orgConsolidada['sgl_organizacao'] }} - {{ $orgConsolidada['nom_organizacao'] }}
                                        </span>
                                    @endforeach
                                </div>
                                <p class="text-muted small mt-3 mb-0">
                                    <i class="bi bi-info-circle me-1"></i> Os valores de atingimento acima representam a média aritmética dos indicadores de todas estas unidades.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-opacity-25 rounded-pill px-3 py-2 mt-2">
                        <i class="bi bi-geo-alt me-1"></i> Visualização Estrita (Apenas Unidade Selecionada)
                    </span>
                @endif
            </div>

            {{-- TOOLBAR PREMIUM (FORA DO SLOT PARA FUNCIONAR O WIRE:CLICK) --}}
            <div class="d-flex justify-content-end mb-4">
                <div class="view-mode-selector bg-surface border rounded-pill p-1 d-flex shadow-sm">
                    <button wire:click="setViewMode('grouped')" 
                            class="btn btn-sm rounded-pill px-4 d-flex align-items-center gap-2 transition-all {{ $viewMode === 'grouped' ? 'btn-primary shadow' : 'btn-ghost-secondary text-muted' }}"
                            wire:loading.attr="disabled">
                        <i class="bi bi-diagram-3-fill" wire:loading.remove wire:target="setViewMode('grouped')"></i>
                        <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="setViewMode('grouped')"></div>
                        <span class="fw-bold small">Agrupado</span>
                    </button>
                    <button wire:click="setViewMode('individual')" 
                            class="btn btn-sm rounded-pill px-4 d-flex align-items-center gap-2 transition-all {{ $viewMode === 'individual' ? 'btn-primary shadow' : 'btn-ghost-secondary text-muted' }}"
                            wire:loading.attr="disabled">
                        <i class="bi bi-geo-alt-fill" wire:loading.remove wire:target="setViewMode('individual')"></i>
                        <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="setViewMode('individual')"></div>
                        <span class="fw-bold small">Individual</span>
                    </button>
                </div>
            </div>

            <!-- ========== IDENTIDADE ESTRATÉGICA ========== -->
            <div class="row g-4 mb-5">
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
                                <span class="value-tag-modern shadow-sm">{{ $valor->nom_valor }}</span>
                            @empty
                                <span class="text-muted small italic opacity-50">Valores não definidos.</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Card de Temas Norteadores --}}
                <div class="col-12">
                    <div class="identity-box shadow-sm border h-100 text-center">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="icon-circle-mini bg-warning bg-opacity-10 text-warning me-2"><i class="bi bi-shield-check"></i></div>
                            <label class="identity-label mb-0">Temas Norteadores Institucionais</label>
                        </div>
                        <div class="d-flex flex-wrap gap-3 mt-2 justify-content-center">
                            @forelse($temasNorteadores as $obj)
                                <span class="value-tag-modern shadow-sm border-warning border-opacity-50 py-2 px-4" style="background: rgba(var(--bs-warning-rgb), 0.05); font-size: 1.1rem;">
                                    <i class="bi bi-check2-circle text-warning me-2"></i> {{ $obj->nom_tema_norteador }}
                                </span>
                            @empty
                                <span class="text-muted small italic opacity-50">Temas norteadores não definidos.</span>
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
                            <div class="perspectiva-header-modern px-4 py-3 d-flex justify-content-between align-items-center"
                                 style="--persp-base-bg: var(--bs-{{ $corBordaRef }}-bg-subtle); --persp-border-color: var(--bs-{{ $corBordaRef }});">
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
                                                 @auth onclick="Livewire.navigate('{{ route('objetivos.index') }}?search={{ urlencode($objetivo['nom_objetivo']) }}')" @endauth style="cursor: pointer;">
                                                <div class="obj-content p-3">
                                                    <p class="obj-title mb-3" title="{{ $objetivo['nom_objetivo'] }}">
                                                        {{ Str::limit($objetivo['nom_objetivo'], 70) }}
                                                    </p>
                                                    <div class="obj-stat-box mb-2">
                                                        <a wire:navigate href="{{ route('indicadores.index') }}?filtroObjetivo={{ $objetivo['cod_objetivo'] }}" 
                                                           class="text-decoration-none indicador-link" @auth onclick="event.stopPropagation();" @endauth>
                                                            <div class="d-flex justify-content-between mb-1 align-items-center">
                                                                <span class="stat-label-modern">KPIs</span>
                                                                <span class="stat-value-modern" style="color: {{ $ind['cor'] }};">@brazil_percent($ind['percentual'], 1)</span>
                                                            </div>
                                                            <div class="stat-progress-container bg-light-custom">
                                                                <div class="stat-progress-fill" style="width: {{ min($ind['percentual'], 100) }}%; background-color: {{ $ind['cor'] }};"></div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    {{-- Planos (Foco em Atividade e Progresso) --}}
                                                    <div class="obj-stat-box">
                                                        <a wire:navigate href="{{ route('planos.index') }}?filtroObjetivo={{ $objetivo['cod_objetivo'] }}" 
                                                           class="text-decoration-none plano-link" @auth onclick="event.stopPropagation();" @endauth>
                                                            <div class="d-flex justify-content-between mb-1 align-items-center">
                                                                {{-- Rótulo com Quantidade --}}
                                                                <span class="stat-label-modern" style="text-transform: uppercase;">
                                                                    {{ $pln['quantidade'] }} Planos Ativos
                                                                </span>
                                                                {{-- Percentual em Destaque --}}
                                                                <span class="stat-value-modern" style="color: {{ $pln['cor'] }}; font-size: 0.8rem;">
                                                                    @brazil_percent($pln['media_progresso'] ?? 0, 1)
                                                                </span>
                                                            </div>
                                                            {{-- Barra de Progresso --}}
                                                            <div class="stat-progress-container bg-light-custom">
                                                                <div class="stat-progress-fill" style="width: {{ min($pln['media_progresso'] ?? 0, 100) }}%; background-color: {{ $pln['cor'] }};"></div>
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
            <div class="legenda-wrapper mt-5 mb-4 text-center">
                <div class="d-inline-flex flex-column gap-3 px-5 py-3 rounded-4 shadow-sm bg-body border">
                    <div class="d-flex align-items-center justify-content-center flex-wrap gap-4">
                        <span class="small fw-bold text-muted text-uppercase letter-spacing-1">Graus de Satisfação:</span>
                        @foreach($grausSatisfacao as $grau)
                            <div class="d-flex align-items-center">
                                <span class="legenda-color-dot me-2 shadow-sm" style="background-color: {{ $grau->cor }};"></span>
                                <small class="text-body fw-medium">{{ $grau->dsc_grau_satisfacao }} <span class="text-muted fw-normal" style="font-size: 0.8rem;">({{ number_format($grau->vlr_minimo, 0) }}-{{ number_format($grau->vlr_maximo, 0) }}%)</span></small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Memória de Cálculo --}}
    @if($showCalcModal && $detalhesCalculo)
        <div class="modal fade show d-block premium-modal-backdrop" tabindex="-1">
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

                        @if(isset($detalhesCalculo['detalhes_calculo']) && ($detalhesCalculo['detalhes_calculo']['peso_planos'] > 0))
                            <div class="card border-0 bg-light-subtle shadow-sm mb-4">
                                <div class="card-body py-3 px-4">
                                    <h6 class="small fw-bold text-uppercase text-muted mb-3"><i class="bi bi-calculator me-1"></i>Composição da Nota (Cálculo Ponderado)</h6>
                                    <div class="d-flex justify-content-center align-items-center gap-4 flex-wrap">
                                        
                                        <!-- Parte Indicadores -->
                                        <div class="text-center px-3 border-end">
                                            <div class="h3 mb-0 text-primary fw-bold">@brazil_percent($detalhesCalculo['detalhes_calculo']['nota_indicadores'], 1)</div>
                                            <div class="x-small text-muted fw-bold text-uppercase mt-1">
                                                Indicadores <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 ms-1">{{ $detalhesCalculo['detalhes_calculo']['peso_indicadores'] }}%</span>
                                            </div>
                                        </div>

                                        <div class="text-muted h3 mb-0 opacity-50">+</div>

                                        <!-- Parte Planos -->
                                        <div class="text-center px-3 border-end">
                                            <div class="h3 mb-0 text-success fw-bold">@brazil_percent($detalhesCalculo['detalhes_calculo']['nota_planos'], 1)</div>
                                            <div class="x-small text-muted fw-bold text-uppercase mt-1">
                                                Planos (Ano) <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-1">{{ $detalhesCalculo['detalhes_calculo']['peso_planos'] }}%</span>
                                            </div>
                                        </div>

                                        <div class="text-muted h3 mb-0 opacity-50">=</div>

                                        <!-- Resultado -->
                                        <div class="text-center px-3">
                                            <div class="h2 mb-0 fw-800" style="color: {{ $detalhesCalculo['cor'] }}">
                                                @brazil_percent($detalhesCalculo['media'], 1)
                                            </div>
                                            <div class="x-small text-muted fw-bold text-uppercase mt-1">Nota Final</div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover mb-0">
                                <thead class="bg-body-secondary">
                                    <tr class="small text-muted text-uppercase fw-bold">
                                        <th class="border-0 px-3">Objetivo</th>
                                        <th class="border-0">Indicador</th>
                                        <th class="border-0 text-center">Polaridade</th>
                                        <th class="border-0 text-end px-3">Atingimento</th>
                                    </tr>
                                </thead>
                                <tbody class="text-body">
                                    @foreach($detalhesCalculo['indicadores'] as $item)
                                        <tr>
                                            <td class="small fw-bold px-3">{{ $item['objetivo'] }}</td>
                                            <td class="small opacity-75">{{ $item['indicador'] }}</td>
                                            <td class="text-center">
                                                @php
                                                    $polIcon = [
                                                        'Positiva' => 'bi-arrow-up-circle-fill text-success',
                                                        'Negativa' => 'bi-arrow-down-circle-fill text-danger',
                                                        'Estabilidade' => 'bi-dash-circle-fill text-warning',
                                                        'Não Aplicável' => 'bi-info-circle-fill text-muted'
                                                    ][$item['polaridade']] ?? 'bi-question-circle';
                                                @endphp
                                                <i class="bi {{ $polIcon }}" title="{{ $item['polaridade'] }}"></i>
                                            </td>
                                            <td class="text-end fw-800 px-3" style="color: {{ $item['cor'] }};">@brazil_percent($item['atingimento'], 1)</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Tabela de Planos e Entregas (NOVO) --}}
                        @if(isset($detalhesCalculo['planos']) && count($detalhesCalculo['planos']) > 0)
                            <div class="divider-center my-4 opacity-25"></div>
                            
                            <div class="d-flex align-items-center mb-3 ps-2">
                                <h6 class="small fw-bold text-uppercase text-muted mb-0"><i class="bi bi-list-check me-2"></i>Detalhamento de Planos e Entregas</h6>
                            </div>

                            <div class="table-responsive rounded-3 border">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="bg-body-secondary">
                                        <tr class="small text-muted text-uppercase fw-bold">
                                            <th class="border-0 px-3 py-3" style="width: 25%;">Objetivo</th>
                                            <th class="border-0 py-3">Plano de Ação / Entregas do Ano</th>
                                            <th class="border-0 text-end px-3 py-3" style="width: 120px;">Nota</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-body">
                                        @foreach($detalhesCalculo['planos'] as $plano)
                                            <tr>
                                                <td class="small fw-bold px-3 text-wrap">{{ $plano['objetivo'] }}</td>
                                                <td class="py-3">
                                                    <div class="fw-bold text-primary mb-2"><i class="bi bi-folder2-open me-1"></i>{{ $plano['plano'] }}</div>
                                                    
                                                    <div class="bg-light rounded-3 p-2 small">
                                                        @foreach($plano['entregas'] as $entrega)
                                                            <div class="d-flex align-items-center justify-content-between mb-1 pb-1 border-bottom border-light-subtle last-no-border">
                                                                <div class="d-flex align-items-center text-truncate pe-2">
                                                                    <i class="bi bi-dot text-muted me-1"></i>
                                                                    <span class="text-muted" title="{{ $entrega['entrega'] }}">{{ Str::limit($entrega['entrega'], 60) }}</span>
                                                                </div>
                                                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                                                    <span class="badge bg-white text-muted border fw-normal" style="font-size: 0.75rem;">{{ $entrega['prazo'] }}</span>
                                                                    
                                                                    @php
                                                                        $statusColor = match($entrega['status']) {
                                                                            'Concluído' => 'success', 'Em Andamento' => 'warning', 'Suspenso' => 'secondary', 'Atrasado' => 'danger', default => 'light'
                                                                        };
                                                                        $statusIcon = match($entrega['status']) {
                                                                            'Concluído' => 'check-circle-fill', 'Em Andamento' => 'hourglass-split', 'Suspenso' => 'pause-circle', 'Atrasado' => 'exclamation-circle-fill', default => 'circle'
                                                                        };
                                                                    @endphp
                                                                    
                                                                    <span class="text-{{ $statusColor }}" title="{{ $entrega['status'] }} (Peso {{ $entrega['peso'] }})">
                                                                        <i class="bi bi-{{ $statusIcon }}"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td class="text-end fw-800 px-3" style="font-size: 1.1rem; color: {{ $plano['cor'] }};">
                                                    @brazil_percent($plano['atingimento'], 1)
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                    </div>
                    <div class="modal-footer border-0 p-4 pt-0 text-center">
                        <button type="button" class="btn btn-secondary px-5 rounded-pill fw-bold" wire:click="fecharMemoriaCalculo">Fechar Memória</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .divider-left { width: 40px; height: 4px; background: var(--bs-primary); margin-top: 8px; border-radius: 10px; opacity: 0.4; }
        .last-no-border:last-child { border-bottom: none !important; padding-bottom: 0 !important; margin-bottom: 0 !important; }
    </style>
</div>