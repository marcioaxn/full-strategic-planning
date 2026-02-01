<div class="container-fluid py-4">
    <!-- Header & Stats Section -->
    <div class="row mb-4 align-items-center animate-fade-in-down">
        <div class="col-md-8">
            <h2 class="fw-bold text-dark mb-1 tracking-tight">
                Central de Relatórios
            </h2>
            <p class="text-muted mb-0 font-medium">
                Gerencie, visualize e agende a inteligência estratégica da sua organização.
            </p>
        </div>
        <div class="col-md-4 text-end">
             @if($aiEnabled && $organizacaoId)
                <button wire:click="gerarInsightIA" wire:loading.attr="disabled" class="btn btn-magic btn-lg shadow-sm rounded-3 btn-pulse">
                    <span wire:loading.remove wire:target="gerarInsightIA" class="d-flex align-items-center gap-2">
                        <i class="bi bi-stars"></i> <span>AI Strategic Minute</span>
                    </span>
                    <span wire:loading wire:target="gerarInsightIA">
                        <div class="spinner-border spinner-border-sm text-white" role="status"></div> Analisando...
                    </span>
                </button>
            @endif
        </div>
    </div>

    <!-- AI Insights Area -->
    @if($aiInsight)
        <div class="row mb-4 animate-fade-in">
            <div class="col-12">
                <div class="card border-0 shadow-lg overflow-hidden rounded-4" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);">
                    <div class="card-body p-4 position-relative">
                        <div class="position-absolute top-0 end-0 p-3 opacity-10">
                            <i class="bi bi-robot fs-1 text-primary"></i>
                        </div>
                        <h5 class="fw-bold text-primary mb-3"><i class="bi bi-stars me-2"></i>Insight Estratégico</h5>
                        <div class="markdown-content text-dark opacity-75" style="line-height: 1.6;">
                            {!! Str::markdown($aiInsight) !!}
                        </div>
                        <div class="text-end mt-2">
                            <button class="btn btn-sm btn-link text-muted text-decoration-none" wire:click="$set('aiInsight', '')">Fechar Insight</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content Grid -->
    <div class="row g-4">
        
        <!-- Left Column: Filters & Reports Catalog -->
        <div class="col-lg-8">
            
            <!-- Context Bar -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-3 d-flex align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                            <i class="bi bi-building fs-5"></i>
                        </div>
                        <div class="border-end pe-3">
                            <small class="text-muted text-uppercase d-block fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Organização</small>
                            <span class="fw-bold text-dark">{{ $organizacaoNome ?? 'Selecione uma Organização' }}</span>
                        </div>
                    </div>

                    <!-- Compact Filters -->
                    <div class="d-flex gap-2 flex-wrap flex-grow-1 justify-content-end">
                         <div class="filter-group">
                            <select wire:model.live="anoSelecionado" class="form-select form-select-sm bg-light border-0 fw-bold text-secondary" style="cursor: pointer;">
                                @foreach($anos as $ano)
                                    <option value="{{ $ano }}">{{ $ano }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <select wire:model.live="periodoSelecionado" class="form-select form-select-sm bg-light border-0 fw-bold text-secondary" style="cursor: pointer;">
                                <option value="anual">Anual</option>
                                <option value="1_semestre">1º Sem.</option>
                                <option value="2_semestre">2º Sem.</option>
                                <option value="1_trimestre">1º Tri.</option>
                                <option value="2_trimestre">2º Tri.</option>
                                <option value="3_trimestre">3º Tri.</option>
                                <option value="4_trimestre">4º Tri.</option>
                            </select>
                        </div>
                        <div class="filter-group" style="min-width: 150px;">
                            <select wire:model.live="perspectivaSelecionada" class="form-select form-select-sm bg-light border-0 fw-bold text-secondary" style="cursor: pointer;">
                                <option value="">Todas Perspectivas</option>
                                @foreach($perspectivas as $persp)
                                    <option value="{{ $persp->cod_perspectiva }}">{{ Str::limit($persp->dsc_perspectiva, 20) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        @if($aiEnabled)
                        <div class="filter-group d-flex align-items-center bg-light rounded px-2" style="border: 1px solid #dee2e6;">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" id="includeAiSwitch" wire:model.live="includeAi">
                                <label class="form-check-label small fw-bold text-secondary" for="includeAiSwitch" style="cursor: pointer; margin-left: 5px;">Incluir IA</label>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Reports Grid -->
            <h6 class="text-uppercase text-muted fw-bold mb-3 small tracking-wide ps-1">Catálogo de Relatórios</h6>
            <div class="row g-3">
                
                <!-- Card Component Loop (Refactored for Visual Impact) -->
                @php
                    $reports = [
                        [
                            'title' => 'Dossiê Estratégico Integrado',
                            'desc' => 'Relatório completo unificando todas as visões estratégicas.',
                            'icon' => 'bi-collection-play',
                            'color' => 'success', // Alterado para success para garantir visual Premium com opacidade
                            'route_pdf' => route('relatorios.integrado', ['organizacaoId' => $organizacaoId, 'ano' => $anoSelecionado, 'periodo' => $periodoSelecionado]) . "&include_ai=" . ($includeAi ? '1' : '0'),
                            'type' => 'integrado',
                            'featured' => true // Marcador visual
                        ],
                        [
                            'title' => 'Relatório Executivo',
                            'desc' => 'Visão holística da estratégia.',
                            'icon' => 'bi-briefcase',
                            'color' => 'success',
                            'route_pdf' => route('relatorios.executivo', ['organizacaoId' => $organizacaoId, 'ano' => $anoSelecionado, 'periodo' => $periodoSelecionado]),
                            'type' => 'executivo'
                        ],
                        [
                            'title' => 'Mapa Estratégico',
                            'desc' => 'Visualização gráfica das perspectivas e objetivos.',
                            'icon' => 'bi-diagram-3', // Ícone de diagrama/mapa
                            'color' => 'primary',
                            'route_pdf' => route('relatorios.identidade', ['organizacaoId' => $organizacaoId]) . "?ano={$anoSelecionado}", // Precisa do ano agora
                            'type' => 'identidade' // Mantemos o ID interno por compatibilidade de rota, mas o conceito muda
                        ],
                        [
                            'title' => 'Objetivos Táticos (BSC)',
                            'desc' => 'Status por perspectiva.',
                            'icon' => 'bi-bullseye',
                            'color' => 'info',
                            'route_pdf' => route('relatorios.objetivos.pdf') . "?organizacao_id={$organizacaoId}&ano={$anoSelecionado}",
                            'route_excel' => route('relatorios.objetivos.excel'),
                            'type' => 'objetivos'
                        ],
                        [
                            'title' => 'Indicadores (KPIs)',
                            'desc' => 'Metas vs Realizado.',
                            'icon' => 'bi-graph-up-arrow',
                            'color' => 'warning',
                            'route_pdf' => route('relatorios.indicadores.pdf', ['organizacaoId' => $organizacaoId]) . "?ano={$anoSelecionado}&periodo={$periodoSelecionado}",
                            'route_excel' => route('relatorios.indicadores.excel', ['organizacaoId' => $organizacaoId]),
                            'type' => 'indicadores'
                        ],
                        [
                            'title' => 'Planos de Ação',
                            'desc' => 'Cronogramas e status.',
                            'icon' => 'bi-list-check',
                            'color' => 'secondary',
                            'route_pdf' => route('relatorios.planos.pdf') . "?organizacao_id={$organizacaoId}&ano={$anoSelecionado}",
                            'route_excel' => route('relatorios.planos.excel') . "?organizacao_id={$organizacaoId}&ano={$anoSelecionado}",
                            'type' => 'planos'
                        ],
                        [
                            'title' => 'Gestão de Riscos',
                            'desc' => 'Matriz e mitigações.',
                            'icon' => 'bi-shield-exclamation',
                            'color' => 'danger',
                            'route_pdf' => route('relatorios.riscos.pdf') . "?organizacao_id={$organizacaoId}",
                            'route_excel' => route('relatorios.riscos.excel') . "?organizacao_id={$organizacaoId}",
                            'type' => 'riscos'
                        ],
                    ];
                @endphp

                @foreach($reports as $report)
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 report-card hover-lift transition-all">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="icon-shape icon-lg bg-{{ $report['color'] }} bg-opacity-10 text-{{ $report['color'] }} rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="bi {{ $report['icon'] }} fs-4"></i>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3">
                                            @if($organizacaoId)
                                                <li>
                                                    <a class="dropdown-item py-2" href="{{ $report['route_pdf'] }}" target="_blank">
                                                        <i class="bi bi-file-pdf text-danger me-2"></i>Baixar PDF
                                                    </a>
                                                </li>
                                                @if(isset($report['route_excel']))
                                                <li>
                                                    <a class="dropdown-item py-2" href="{{ $report['route_excel'] }}">
                                                        <i class="bi bi-file-excel text-success me-2"></i>Baixar Excel
                                                    </a>
                                                </li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button class="dropdown-item py-2" wire:click="$dispatch('abrirAgendamento', { tipo: '{{ $report['type'] }}', filtros: @js($this->getQueryParamsProperty()) })">
                                                        <i class="bi bi-clock-history text-primary me-2"></i>Agendar Envio
                                                    </button>
                                                </li>
                                            @else
                                                <li><span class="dropdown-item disabled text-muted">Selecione uma organização</span></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">{{ $report['title'] }}</h5>
                                <p class="text-muted small mb-3">{{ $report['desc'] }}</p>
                                
                                <div class="d-grid">
                                    @if($organizacaoId)
                                        <a href="{{ $report['route_pdf'] }}" target="_blank" class="btn btn-outline-light text-dark border-0 bg-light bg-opacity-50 hover-bg-{{ $report['color'] }} text-start btn-sm rounded-3 py-2 px-3 d-flex justify-content-between align-items-center">
                                            <span class="fw-medium">Visualizar Agora</span>
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    @else
                                        <button disabled class="btn btn-light text-muted btn-sm rounded-3 border-0">Selecione Org.</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right Column: Recent Activity & Scheduled -->
        <div class="col-lg-4">
            
            <!-- Recent Reports (Feed Style) -->
            @if($recentReports->isNotEmpty())
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Gerados Recentemente</h6>
                    </div>
                    <div class="card-body pt-2">
                        <div class="list-group list-group-flush">
                            @foreach($recentReports as $rep)
                                <div class="list-group-item border-0 px-0 py-3 d-flex align-items-center">
                                    <div class="me-3 position-relative">
                                        <i class="bi bi-file-earmark-pdf fs-3 text-danger"></i>
                                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle">
                                            <span class="visually-hidden">New alerts</span>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 min-width-0">
                                        <h6 class="mb-0 text-truncate font-weight-bold text-dark" style="font-size: 0.95rem;">
                                            {{ ucfirst($rep->dsc_tipo_relatorio) }}
                                        </h6>
                                        <small class="text-muted d-block">
                                            {{ $rep->created_at->diffForHumans() }} &bull; {{ number_format($rep->num_tamanho_bytes / 1024, 1) }} KB
                                        </small>
                                    </div>
                                    <button wire:click="download('{{ $rep->cod_relatorio_gerado }}')" class="btn btn-light btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px;">
                                        <i class="bi bi-download text-dark"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Scheduled Reports (Compact Manager) -->
            @livewire('reports.gerenciar-agendamentos')

        </div>
    </div>

    @livewire('reports.agendar-relatorio')
    
    <style>
        .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .hover-lift:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important; }
        .btn-magic { background: linear-gradient(45deg, #6366f1, #8b5cf6); border: none; color: white; }
        .btn-magic:hover { background: linear-gradient(45deg, #565add, #7c4dff); color: white; }
        .tracking-tight { letter-spacing: -0.5px; }
        .filter-group select { box-shadow: none; }
        .filter-group select:hover { background-color: #e9ecef!important; }
        .icon-box { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; }
        
        /* Custom Scrollbar for dropdowns if needed */
        .dropdown-menu { max-height: 300px; overflow-y: auto; }
        
        .btn-pulse { animation: pulse-purple 2s infinite; }
        @keyframes pulse-purple {
            0% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(99, 102, 241, 0); }
            100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
        }
    </style>
</div>