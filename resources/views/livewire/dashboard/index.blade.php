<div class="dashboard-wrapper">
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

    {{-- LINHA SUPERIOR: Cards de KPIs e Atividades (Harmonizados) --}}
    <div class="row g-4 mb-4 align-items-stretch">
        <div class="col-xl-3 col-md-6">
            <div class="card-kpi shadow-sm h-100 position-relative">
                <div class="kpi-icon bg-primary-subtle text-primary"><i class="bi bi-bullseye"></i></div>
                <div class="kpi-data">
                    <span class="kpi-label">Objetivos</span>
                    <h3 class="kpi-value">{{ $stats['totalObjetivos'] }}</h3>
                    <div class="kpi-trend text-primary small fw-bold">Ver Planejamento <i class="bi bi-arrow-right"></i></div>
                </div>
                <a href="{{ route('objetivos.index') }}" class="stretched-link" wire:navigate></a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card-kpi shadow-sm h-100 position-relative">
                <div class="kpi-icon bg-success-subtle text-success"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="kpi-data">
                    <span class="kpi-label">Progresso Planos</span>
                    <h3 class="kpi-value">{{ number_format($stats['progressoPlanos'], 1) }}%</h3>
                    <div class="progress mt-2" style="height: 6px; width: 100px;">
                        <div class="progress-bar bg-success" style="width: {{ $stats['progressoPlanos'] }}%"></div>
                    </div>
                </div>
                <a href="{{ route('planos.index') }}" class="stretched-link" wire:navigate></a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card-kpi shadow-sm h-100 position-relative">
                <div class="kpi-icon bg-danger-subtle text-danger"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="kpi-data">
                    <span class="kpi-label">Riscos Críticos</span>
                    <h3 class="kpi-value text-danger">{{ $stats['riscosCriticos'] }}</h3>
                    <div class="kpi-trend text-danger small fw-bold">Analisar Ameaças <i class="bi bi-arrow-right"></i></div>
                </div>
                <a href="{{ route('riscos.index') }}?filtroNivel=Critico" class="stretched-link" wire:navigate></a>
            </div>
        </div>
        {{-- Card Minhas Atividades integrado na mesma linha --}}
        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm h-100 border-0 bg-primary text-white overflow-hidden position-relative">
                <div class="card-body p-3 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-uppercase opacity-75">Minhas Atividades</span>
                        <span class="badge bg-white text-primary rounded-pill">{{ $minhasEntregas->count() }}</span>
                    </div>
                    <div class="notion-mini-list flex-grow-1">
                        @forelse($minhasEntregas->take(2) as $entrega)
                            <div class="mb-2 p-2 rounded bg-white bg-opacity-10">
                                <div class="small fw-bold text-truncate">{{ $entrega->dsc_entrega }}</div>
                                <div class="d-flex justify-content-between small opacity-75" style="font-size: 0.65rem;">
                                    <span>{{ $entrega->planoDeAcao->dsc_plano_de_acao }}</span>
                                    <span class="{{ $entrega->isAtrasada() ? 'text-warning fw-bold' : '' }}">{{ $entrega->dte_prazo?->format('d/m') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-2 opacity-75 small">Tudo em dia!</div>
                        @endforelse
                    </div>
                    <a href="{{ route('entregas.index') }}" class="mt-auto text-white small text-decoration-none fw-bold" wire:navigate>
                        Ver todas <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- LINHA DE ANÁLISE: Gráficos Estratégicos --}}
    <div class="row g-4 mb-4">
        {{-- Desempenho BSC --}}
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between">
                    <h6 class="fw-bold mb-0">Desempenho por Perspectiva (Atingimento %)</h6>
                    <a href="{{ route('pei.mapa') }}" class="small text-decoration-none" wire:navigate>Mapa Estratégico <i class="bi bi-box-arrow-up-right"></i></a>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="height: 320px;">
                        <canvas id="bscChart" wire:ignore></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Distribuição de Riscos (Útil) --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between">
                    <h6 class="fw-bold mb-0">Severidade dos Riscos</h6>
                    <a href="{{ route('riscos.matriz') }}" class="small text-decoration-none" wire:navigate>Ver Matriz</a>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="height: 250px;">
                        <canvas id="riscosNivelChart" wire:ignore></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Impacto na Estratégia</span>
                            <span class="fw-bold text-danger">{{ $stats['riscosCriticos'] }} Críticos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- LINHA FINAL: Planos e Colaboração --}}
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="fw-bold mb-0">Status dos Planos</h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="height: 200px;">
                        <canvas id="planosChart" wire:ignore></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Colaboração e Feedback Recente</h6>
                    <span class="badge bg-light text-muted">Últimas 5 interações</span>
                </div>
                <div class="card-body px-4">
                    <div class="row">
                        @forelse($comentariosRecentes as $comentario)
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
                        @empty
                            <div class="col-12 text-center py-5 opacity-25">
                                <i class="bi bi-chat-left-dots fs-1 d-block mb-2"></i>
                                <span class="small">Sem novas interações.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Estilos Refinados --}}
    <style>
        .dashboard-wrapper { padding: 5px; }
        .card-kpi { background: white; border-radius: 12px; padding: 15px; display: flex; align-items: center; gap: 15px; border: 1px solid #f0f0f0; transition: all 0.2s; }
        .card-kpi:hover { transform: translateY(-3px); border-color: var(--bs-primary); }
        .kpi-icon { width: 42px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
        .kpi-label { font-size: 0.7rem; font-weight: 700; color: #9b9a97; text-transform: uppercase; }
        .kpi-value { font-weight: 800; margin: 0; font-size: 1.5rem; }
        .kpi-trend { font-size: 0.65rem; margin-top: 4px; }
        
        [data-bs-theme="dark"] .card-kpi, [data-bs-theme="dark"] .card { background: #1e1e1e !important; border-color: #333 !important; }
        [data-bs-theme="dark"] .text-dark { color: #eee !important; }
        [data-bs-theme="dark"] .bg-light { background: #2a2a2a !important; }
    </style>

    {{-- Scripts de Gráficos --}}
    <script>
        document.addEventListener('livewire:navigated', () => { initAllCharts(); });
        document.addEventListener('DOMContentLoaded', () => { initAllCharts(); });

        function initAllCharts() {
            const bscData = @json($chartBSC);
            const riscosData = @json($chartRiscosNivel);
            const planosData = @json($chartPlanos);

            // 1. BSC Chart (Barras Horizontais com Cores Dinâmicas)
            const ctxBsc = document.getElementById('bscChart');
            if (ctxBsc) {
                if (ctxBsc.chart) ctxBsc.chart.destroy();
                ctxBsc.chart = new Chart(ctxBsc, {
                    type: 'bar',
                    data: {
                        labels: bscData.map(i => i.label),
                        datasets: [{
                            data: bscData.map(i => i.count),
                            backgroundColor: bscData.map(i => i.color),
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

            // 2. Riscos Nível Chart (Doughnut)
            const ctxRs = document.getElementById('riscosNivelChart');
            if (ctxRs) {
                if (ctxRs.chart) ctxRs.chart.destroy();
                ctxRs.chart = new Chart(ctxRs, {
                    type: 'doughnut',
                    data: {
                        labels: riscosData.labels,
                        datasets: [{
                            data: riscosData.data,
                            backgroundColor: riscosData.colors,
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
            if (ctxPl) {
                if (ctxPl.chart) ctxPl.chart.destroy();
                ctxPl.chart = new Chart(ctxPl, {
                    type: 'doughnut',
                    data: {
                        labels: planosData.map(i => i.label),
                        datasets: [{
                            data: planosData.map(i => i.count),
                            backgroundColor: planosData.map(i => i.color),
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
