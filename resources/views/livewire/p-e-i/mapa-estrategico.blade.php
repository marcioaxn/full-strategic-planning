<div class="mapa-canvas" wire:poll.10s>
    
    <div style="margin-top: 100px;"></div>

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
                                <span class="text-muted small italic opacity-50">Temas norteadores não definidos para esta unidade.</span>
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
                                                        <a wire:navigate href="{{ route('indicadores.index') }}?filtroObjetivo={{ $objetivo['cod_objetivo'] }}" 
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
                                                        <a wire:navigate href="{{ route('planos.index') }}?filtroObjetivo={{ $objetivo['cod_objetivo'] }}" 
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
                                <small class="text-body fw-medium">{{ $grau->dsc_grau_satisfacao }} <span class="text-muted fw-normal" style="font-size: 0.9rem;">( @brazil_number($grau->vlr_minimo, 2) - @brazil_percent($grau->vlr_maximo, 2) )</span></small>
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
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-secondary px-4 rounded-pill fw-bold" wire:click="fecharMemoriaCalculo">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>