<div class="dashboard-wrapper" 
     wire:poll.30s 
     wire:key="dashboard-{{ $peiAtivo?->cod_pei }}-{{ $organizacaoId }}"
     x-data="{ 
        chartData: @entangle('chartData'),
        charts: { bsc: null, riscos: null, planos: null },
        init() {
            this.updateAllCharts();
            $watch('chartData', () => this.updateAllCharts());
        },
        updateAllCharts() {
            this.renderChart('bscChart', 'bar', {
                labels: this.chartData.bsc.map(i => i.label),
                datasets: [{ data: this.chartData.bsc.map(i => i.count), backgroundColor: this.chartData.bsc.map(i => i.color), borderRadius: 4, barThickness: 18 }]
            }, { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { max: 100, ticks: { callback: v => v + '%' } } } });

            this.renderChart('riscosNivelChart', 'doughnut', {
                labels: this.chartData.riscos.labels,
                datasets: [{ data: this.chartData.riscos.data, backgroundColor: this.chartData.riscos.colors, borderWidth: 0 }]
            }, { cutout: '70%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } } } });

            this.renderChart('planosChart', 'doughnut', {
                labels: this.chartData.planos.map(i => i.label),
                datasets: [{ data: this.chartData.planos.map(i => i.count), backgroundColor: this.chartData.planos.map(i => i.color), borderWidth: 0 }]
            }, { cutout: '70%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } } } });
        },
        renderChart(id, type, data, options) {
            const ctx = document.getElementById(id);
            if (!ctx) return;
            if (this.charts[id]) {
                this.charts[id].data = data;
                this.charts[id].update('none');
            } else {
                this.charts[id] = new Chart(ctx, { type, data, options: { ...options, responsive: true, maintainAspectRatio: false } });
            }
        }
     }">
    
    {{-- Header de Boas-Vindas --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Olá, {{ explode(' ', auth()->user()->name)[0] }}! 👋</h3>
            <p class="text-muted mb-0">Gestão estratégica da unidade <strong>{{ $organizacaoNome }}</strong>.</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="generateAiSummary" wire:loading.attr="disabled" class="btn btn-white shadow-sm rounded-pill px-3 border-0 fw-bold text-primary d-flex align-items-center gap-2">
                <span wire:loading.remove wire:target="generateAiSummary">
                    <i class="bi bi-lightning-charge-fill text-warning"></i> {{ __('AI Minute') }}
                </span>
                <span wire:loading wire:target="generateAiSummary">
                    <span class="spinner-border spinner-border-sm text-primary"></span>
                </span>
            </button>
            <div class="badge bg-white shadow-sm text-primary p-2 px-3 border-0 rounded-3 d-flex align-items-center">
                <i class="bi bi-calendar3 me-2"></i> Ciclo: {{ $peiAtivo->dsc_pei ?? now()->format('Y') }}
            </div>
        </div>
    </div>

    {{-- Resumo Executivo da IA --}}
    @if($aiSummary)
        <div class="ai-insight-card animate-fade-in mb-4" style="border-left-color: #ffc107;">
            <div class="card-header bg-white">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-journal-check text-warning"></i>
                    <h6 class="fw-bold mb-0">{{ __('Resumo Executivo da Unidade (Gerado por IA)') }}</h6>
                </div>
                <button type="button" class="btn-close small" style="font-size: 0.7rem;" wire:click="$set('aiSummary', '')"></button>
            </div>
            <div class="card-body">
                <p class="mb-0 text-dark italic" style="font-size: 1rem; line-height: 1.6;">
                    {!! nl2br(e($aiSummary)) !!}
                </p>
            </div>
        </div>
    @endif

    {{-- Mentor Estratégico (Checklist/Guia) --}}
    @livewire('dashboard.pei-checklist')

    {{-- LINHA SUPERIOR: Cards de KPIs --}}
    <div class="row g-4 mb-4 align-items-stretch">
        <div class="col-xl-4 col-md-4">
            <div class="card-kpi shadow-sm h-100 position-relative">
                <div class="kpi-icon bg-primary-subtle text-primary"><i class="bi bi-bullseye"></i></div>
                <div class="kpi-data">
                    <span class="kpi-label">Objetivos BSC</span>
                    <h3 class="kpi-value">{{ $stats['totalObjetivos'] }}</h3>
                    <div class="kpi-context text-muted small">
                        em {{ $stats['totalPerspectivas'] }} perspectivas · {{ $stats['totalIndicadores'] }} indicadores
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
                        {{ $stats['totalPlanos'] }} planos · {{ $stats['planosConcluidos'] }} concluídos
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
                        de {{ $stats['totalRiscos'] }} riscos mapeados
                    </div>
                </div>
                <a href="{{ route('riscos.index') }}?filtroNivel=Critico" class="stretched-link" wire:navigate></a>
            </div>
        </div>
    </div>

    {{-- LINHA MINHAS ATIVIDADES --}}
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
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-2">{{ $minhasEntregas->count() }} pendentes</span>
                                </div>
                            </div>
                        </div>

                        @foreach($entregasAgrupadas as $grupo)
                            <div class="mb-3 {{ !$loop->last ? 'pb-3 border-bottom' : '' }}">
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
                                    <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $grupo['total'] }} entregas</span>
                                </div>

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
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- GRÁFICOS --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100" wire:ignore>
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="fw-bold mb-1">Atingimento por Perspectiva BSC</h6>
                            <p class="text-muted small mb-0">Média consolidada dos indicadores</p>
                        </div>
                        <a href="{{ route('pei.mapa') }}" class="btn btn-outline-primary btn-sm rounded-pill" wire:navigate>Mapa</a>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="height: 300px;"><canvas id="bscChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100" wire:ignore>
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="fw-bold mb-1">Distribuição de Riscos</h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="height: 220px;"><canvas id="riscosNivelChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100" wire:ignore>
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="fw-bold mb-1">Status dos Planos</h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="height: 180px;"><canvas id="planosChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="fw-bold mb-1">Colaboração Recente</h6>
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
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .dashboard-wrapper { padding: 5px; }
        .card-kpi { background: var(--bs-body-bg); border-radius: 12px; padding: 15px; display: flex; align-items: center; gap: 15px; border: 1px solid var(--bs-border-color); transition: all 0.2s; }
        .card-kpi:hover { transform: translateY(-3px); border-color: var(--bs-primary); }
        .kpi-icon { width: 42px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
        .kpi-label { font-size: 0.7rem; font-weight: 700; color: var(--bs-secondary); text-transform: uppercase; }
        .kpi-value { font-weight: 800; margin: 0; font-size: 1.5rem; color: var(--bs-body-color); }
        .kpi-context { font-size: 0.7rem; margin-top: 2px; line-height: 1.3; }
        .card-atividades { background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important; }
        .atividade-item { transition: all 0.2s ease; border-color: var(--bs-border-color) !important; }
        .atividade-item:hover { transform: translateY(-2px); border-color: var(--bs-secondary) !important; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .hover-underline:hover { text-decoration: underline !important; }
    </style>
</div>