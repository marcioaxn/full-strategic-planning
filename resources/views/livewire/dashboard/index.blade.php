<div class="dashboard-wrapper" wire:poll.30s wire:key="dashboard-{{ $peiAtivo?->cod_pei }}-{{ $organizacaoId }}">
    {{-- Header de Boas-Vindas --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Olá, {{ explode(' ', auth()->user()->name)[0] }}! 👋</h3>
            <p class="text-muted mb-0">Gestão estratégica da unidade <strong>{{ $organizacaoNome }}</strong>.</p>
        </div>
        <div class="d-flex gap-2">
            <div class="badge bg-white shadow-sm text-primary p-2 px-3 border-0 rounded-3">
                <i class="bi bi-calendar3 me-2"></i> Ciclo: {{ $peiAtivo->dsc_pei ?? now()->format('Y') }}
            </div>
        </div>
    </div>

    {{-- LINHA SUPERIOR: Cards de KPIs com contexto claro --}}
    <div class="row g-4 mb-4 align-items-stretch">
        <div class="col-xl-4 col-md-4">
            <div class="card-kpi shadow-sm h-100 position-relative">
                <div class="kpi-icon bg-primary-subtle text-primary"><i class="bi bi-bullseye"></i></div>
                <div class="kpi-data">
                    <span class="kpi-label">Objetivos BSC</span>
                    <h3 class="kpi-value">{{ $stats['totalObjetivos'] }}</h3>
                    <div class="kpi-context text-muted small">
                        em {{ $stats['totalPerspectivas'] }} {{ $stats['totalPerspectivas'] == 1 ? 'perspectiva' : 'perspectivas' }} · {{ $stats['totalIndicadores'] }} {{ $stats['totalIndicadores'] == 1 ? 'indicador' : 'indicadores' }}
                    </div>
                </div>
                <a href="{{ route('objetivos.index') }}" class="stretched-link" wire:navigate></a>
            </div>
        </div>
        <div class="col-xl-4 col-md-4">
            <div class="card-kpi shadow-sm h-100 position-relative">
                <div class="kpi-icon bg-success-subtle text-success"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="kpi-data">
                    <span class="kpi-label">Execução dos Planos</span>
                    <h3 class="kpi-value">{{ number_format($stats['progressoPlanos'], 1) }}%</h3>
                    <div class="kpi-context text-muted small">
                        média de {{ $stats['totalPlanos'] }} {{ $stats['totalPlanos'] == 1 ? 'plano' : 'planos' }} · {{ $stats['planosConcluidos'] }} {{ $stats['planosConcluidos'] == 1 ? 'concluído' : 'concluídos' }}
                    </div>
                    <div class="progress mt-2" style="height: 6px; width: 100%;">
                        <div class="progress-bar bg-success" style="width: {{ min($stats['progressoPlanos'], 100) }}%"></div>
                    </div>
                </div>
                <a href="{{ route('planos.index') }}" class="stretched-link" wire:navigate></a>
            </div>
        </div>
        <div class="col-xl-4 col-md-4">
            <div class="card-kpi shadow-sm h-100 position-relative">
                <div class="kpi-icon bg-danger-subtle text-danger"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="kpi-data">
                    <span class="kpi-label">Riscos Críticos</span>
                    <h3 class="kpi-value text-danger">{{ $stats['riscosCriticos'] }}</h3>
                    <div class="kpi-context text-muted small">
                        de {{ $stats['totalRiscos'] }} {{ $stats['totalRiscos'] == 1 ? 'risco mapeado' : 'riscos mapeados' }}
                    </div>
                </div>
                <a href="{{ route('riscos.index') }}?filtroNivel=Critico" class="stretched-link" wire:navigate></a>
            </div>
        </div>
    </div>

    {{-- LINHA MINHAS ATIVIDADES: Só exibe se houver entregas pendentes --}}
    @if($minhasEntregas->isNotEmpty())
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 overflow-hidden card-atividades">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="kpi-icon bg-secondary-subtle text-secondary"><i class="bi bi-person-check"></i></div>
                                <div>
                                    <span class="small fw-bold text-muted">Entregas sob minha responsabilidade</span>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-2">{{ $minhasEntregas->count() }} pendentes em {{ $entregasAgrupadas->count() }} {{ $entregasAgrupadas->count() == 1 ? 'plano' : 'planos' }}</span>
                                </div>
                            </div>
                        </div>

                        @foreach($entregasAgrupadas as $grupo)
                            <div class="mb-3 {{ !$loop->last ? 'pb-3 border-bottom' : '' }}">
                                {{-- Header do Plano de Ação --}}
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-folder2-open text-primary"></i>
                                        <div>
                                            <a href="{{ route('planos.detalhes', $grupo['plano']->cod_plano_de_acao) }}"
                                               class="fw-semibold text-body text-decoration-none hover-underline" wire:navigate>
                                                {{ Str::limit($grupo['plano']->dsc_plano_de_acao, 50) }}
                                            </a>
                                            @if($grupo['objetivo'])
                                                <div class="small text-muted">
                                                    <i class="bi bi-bullseye me-1"></i>{{ Str::limit($grupo['objetivo']->nom_objetivo, 60) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $grupo['total'] }} {{ $grupo['total'] == 1 ? 'entrega' : 'entregas' }}</span>
                                </div>

                                {{-- Entregas do Plano --}}
                                <div class="row g-2 ps-4">
                                    @foreach($grupo['entregas']->take(3) as $entrega)
                                        <div class="col-md-6 col-lg-4">
                                            <a href="{{ route('planos.detalhes', $entrega->planoDeAcao->cod_plano_de_acao) }}"
                                               class="text-decoration-none" wire:navigate>
                                                <div class="p-2 px-3 rounded-3 border bg-body-tertiary atividade-item d-flex align-items-center justify-content-between">
                                                    <div class="text-truncate me-2">
                                                        <span class="small fw-medium text-body">{{ Str::limit($entrega->dsc_entrega, 35) }}</span>
                                                    </div>
                                                    <span class="small {{ $entrega->isAtrasada() ? 'text-danger fw-bold' : 'text-muted' }} flex-shrink-0">
                                                        <i class="bi bi-calendar3 me-1"></i>{{ $entrega->dte_prazo?->format('d/m') ?? '-' }}
                                                    </span>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                    @if($grupo['total'] > 3)
                                        <div class="col-12">
                                            <a href="{{ route('planos.detalhes', $grupo['plano']->cod_plano_de_acao) }}"
                                               class="small text-muted text-decoration-none" wire:navigate>
                                                <i class="bi bi-plus-circle me-1"></i>mais {{ $grupo['total'] - 3 }} {{ $grupo['total'] - 3 == 1 ? 'entrega' : 'entregas' }}
                                            </a>
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

    {{-- LINHA DE ANÁLISE: Gráficos Estratégicos --}}
    <div class="row g-4 mb-4">
        {{-- Desempenho BSC --}}
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="fw-bold mb-1">Atingimento por Perspectiva BSC</h6>
                            <p class="text-muted small mb-0">
                                Média de atingimento dos indicadores em cada uma das {{ $stats['totalPerspectivas'] }} perspectivas do ciclo {{ $peiAtivo->dsc_pei ?? now()->format('Y') }}
                            </p>
                        </div>
                        <a href="{{ route('pei.mapa') }}" class="btn btn-outline-primary btn-sm rounded-pill" wire:navigate>
                            <i class="bi bi-diagram-3 me-1"></i> Mapa
                        </a>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="height: 300px;"
                         id="bscChartContainer"
                         data-chart='@json($chartBSC)'>
                        <canvas id="bscChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Distribuição de Riscos --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="fw-bold mb-1">Distribuição de Riscos</h6>
                            <p class="text-muted small mb-0">
                                {{ $stats['totalRiscos'] }} {{ $stats['totalRiscos'] == 1 ? 'risco identificado' : 'riscos identificados' }} por severidade
                            </p>
                        </div>
                        <a href="{{ route('riscos.matriz') }}" class="btn btn-outline-secondary btn-sm rounded-pill" wire:navigate>
                            <i class="bi bi-grid-3x3 me-1"></i> Matriz
                        </a>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    @if($stats['totalRiscos'] > 0)
                        <div style="height: 220px;"
                             id="riscosChartContainer"
                             data-chart='@json($chartRiscosNivel)'>
                            <canvas id="riscosNivelChart"></canvas>
                        </div>
                        <div class="mt-3 pt-2 border-top">
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Atenção prioritária:</span>
                                <span class="fw-bold text-danger">{{ $stats['riscosCriticos'] }} {{ $stats['riscosCriticos'] == 1 ? 'crítico' : 'críticos' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-shield-check fs-1 d-block mb-2 text-success"></i>
                            <span>Nenhum risco mapeado</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- LINHA FINAL: Planos e Colaboração --}}
    <div class="row g-4">
        <div class="{{ $comentariosRecentes->isNotEmpty() ? 'col-xl-4' : 'col-xl-6 mx-auto' }}">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="fw-bold mb-1">Status dos Planos de Ação</h6>
                    <p class="text-muted small mb-0">
                        Situação dos {{ $stats['totalPlanos'] }} {{ $stats['totalPlanos'] == 1 ? 'plano cadastrado' : 'planos cadastrados' }}
                    </p>
                </div>
                <div class="card-body px-4 pb-4">
                    @if($stats['totalPlanos'] > 0)
                        <div style="height: 180px;"
                             id="planosChartContainer"
                             data-chart='@json($chartPlanos)'>
                            <canvas id="planosChart"></canvas>
                        </div>
                        <div class="mt-3 pt-2 border-top text-center">
                            <a href="{{ route('planos.index') }}" class="small text-decoration-none" wire:navigate>
                                Ver todos os planos <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-clipboard-x fs-1 d-block mb-2"></i>
                            <span>Nenhum plano cadastrado</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($comentariosRecentes->isNotEmpty())
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold mb-1">Colaboração Recente</h6>
                                <p class="text-muted small mb-0">
                                    Últimos {{ $comentariosRecentes->count() }} comentários em entregas dos planos de ação
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-4">
                        <div class="row">
                            @foreach($comentariosRecentes as $comentario)
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 rounded-3 bg-light bg-opacity-50 border-start border-4 border-primary">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="small fw-bold">{{ $comentario->usuario->name }}</span>
                                            <span class="text-muted" style="font-size: 0.6rem;">{{ $comentario->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="small text-dark mb-1 text-truncate">"{{ $comentario->dsc_comentario }}"</p>
                                        <a href="{{ route('entregas.index') }}" class="text-decoration-none" style="font-size: 0.65rem;" wire:navigate>
                                            <i class="bi bi-arrow-right-short"></i> {{ Str::limit($comentario->entrega->dsc_entrega, 40) }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Estilos Refinados --}}
    <style>
        .dashboard-wrapper { padding: 5px; }
        .card-kpi { background: var(--bs-body-bg); border-radius: 12px; padding: 15px; display: flex; align-items: center; gap: 15px; border: 1px solid var(--bs-border-color); transition: all 0.2s; }
        .card-kpi:hover { transform: translateY(-3px); border-color: var(--bs-primary); }
        .kpi-icon { width: 42px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
        .kpi-label { font-size: 0.7rem; font-weight: 700; color: var(--bs-secondary); text-transform: uppercase; }
        .kpi-value { font-weight: 800; margin: 0; font-size: 1.5rem; color: var(--bs-body-color); }
        .kpi-trend { font-size: 0.65rem; margin-top: 4px; }
        .kpi-context { font-size: 0.7rem; margin-top: 2px; line-height: 1.3; }

        /* Card Minhas Atividades - Neutro e harmonizado */
        .card-atividades {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color) !important;
        }
        .hover-underline:hover { text-decoration: underline !important; }
        .atividade-item {
            transition: all 0.2s ease;
            border-color: var(--bs-border-color) !important;
        }
        .atividade-item:hover {
            transform: translateY(-2px);
            border-color: var(--bs-secondary) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        /* Dark mode ajustes */
        [data-bs-theme="dark"] .card-kpi { background: var(--bs-body-bg); border-color: var(--bs-border-color); }
        [data-bs-theme="dark"] .card-atividades { background: var(--bs-body-bg); }
        [data-bs-theme="dark"] .atividade-item { background: rgba(255,255,255,0.03); }
        [data-bs-theme="dark"] .atividade-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
    </style>

    {{-- Scripts de Gráficos --}}
    <script>
        // Inicializa gráficos no carregamento
        document.addEventListener('livewire:navigated', () => { initChartsFromDOM(); });
        document.addEventListener('DOMContentLoaded', () => { initChartsFromDOM(); });

        // Escuta evento do Livewire com dados atualizados
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('charts-updated', (data) => {
                console.log('Charts updated:', data);
                updateCharts(data[0]);
            });
        });

        function initChartsFromDOM() {
            const bscContainer = document.getElementById('bscChartContainer');
            const riscosContainer = document.getElementById('riscosChartContainer');
            const planosContainer = document.getElementById('planosChartContainer');

            const data = {
                bsc: bscContainer ? JSON.parse(bscContainer.dataset.chart || '[]') : [],
                riscos: riscosContainer ? JSON.parse(riscosContainer.dataset.chart || '{"labels":[],"data":[],"colors":[]}') : {labels:[],data:[],colors:[]},
                planos: planosContainer ? JSON.parse(planosContainer.dataset.chart || '[]') : []
            };

            updateCharts(data);
        }

        function updateCharts(data) {
            // 1. BSC Chart (Barras Horizontais)
            const ctxBsc = document.getElementById('bscChart');
            if (ctxBsc && data.bsc) {
                if (ctxBsc.chart) ctxBsc.chart.destroy();
                ctxBsc.chart = new Chart(ctxBsc, {
                    type: 'bar',
                    data: {
                        labels: data.bsc.map(i => i.label),
                        datasets: [{
                            data: data.bsc.map(i => i.count),
                            backgroundColor: data.bsc.map(i => i.color),
                            borderRadius: 4,
                            barThickness: 18
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { max: 100, grid: { borderDash: [2, 2] }, ticks: { callback: v => v + '%' } },
                            y: { ticks: { font: { size: 10, weight: 'bold' } }, grid: { display: false } }
                        }
                    }
                });
            }

            // 2. Riscos Chart (Doughnut)
            const ctxRs = document.getElementById('riscosNivelChart');
            if (ctxRs && data.riscos) {
                if (ctxRs.chart) ctxRs.chart.destroy();
                ctxRs.chart = new Chart(ctxRs, {
                    type: 'doughnut',
                    data: {
                        labels: data.riscos.labels || [],
                        datasets: [{
                            data: data.riscos.data || [],
                            backgroundColor: data.riscos.colors || [],
                            borderWidth: 0,
                            cutout: '70%'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } } }
                    }
                });
            }

            // 3. Planos Chart (Doughnut)
            const ctxPl = document.getElementById('planosChart');
            if (ctxPl && data.planos) {
                if (ctxPl.chart) ctxPl.chart.destroy();
                ctxPl.chart = new Chart(ctxPl, {
                    type: 'doughnut',
                    data: {
                        labels: data.planos.map(i => i.label),
                        datasets: [{
                            data: data.planos.map(i => i.count),
                            backgroundColor: data.planos.map(i => i.color),
                            borderWidth: 0,
                            cutout: '70%'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } } }
                    }
                });
            }
        }
    </script>
</div>
