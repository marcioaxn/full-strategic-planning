<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-bold mb-0">Painel Estratégico - {{ $organizacaoNome }}</h2>
            <div class="text-muted small">
                <i class="bi bi-calendar3 me-1"></i> Referência: {{ now()->format('M/Y') }}
            </div>
        </div>
    </x-slot>

    <!-- Cards de Estatísticas -->
    <div class="row g-4 mb-4">
        <!-- Objetivos -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                        <i class="bi bi-bullseye fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small text-uppercase fw-bold mb-1">Objetivos</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['totalObjetivos'] }}</h3>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-2">
                    <a href="{{ route('objetivos.index') }}" class="small text-decoration-none text-primary fw-bold">Ver todos <i class="bi bi-chevron-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Planos -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="icon-shape bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                        <i class="bi bi-list-task fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small text-uppercase fw-bold mb-1">Planos de Ação</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['totalPlanos'] }}</h3>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-2">
                    <a href="{{ route('planos.index') }}" class="small text-decoration-none text-info fw-bold">Ver todos <i class="bi bi-chevron-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Indicadores -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="icon-shape bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                        <i class="bi bi-graph-up fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small text-uppercase fw-bold mb-1">Indicadores</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['totalIndicadores'] }}</h3>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-2">
                    <a href="{{ route('indicadores.index') }}" class="small text-decoration-none text-success fw-bold">Ver todos <i class="bi bi-chevron-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Riscos -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="icon-shape bg-danger bg-opacity-10 text-danger rounded-3 p-3 me-3">
                        <i class="bi bi-exclamation-triangle fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small text-uppercase fw-bold mb-1">Riscos Críticos</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['riscosCriticos'] }}</h3>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-2">
                    <a href="{{ route('riscos.index') }}" class="small text-decoration-none text-danger fw-bold">Analisar <i class="bi bi-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Gráfico BSC -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-layers-half me-2 text-primary"></i>Distribuição por Perspectiva (BSC)</h5>
                </div>
                <div class="card-body p-4">
                    <div style="height: 350px;">
                        <canvas id="bscChart" wire:ignore></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas e Ações -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-lightning-charge me-2 text-warning"></i>Atenção Imediata</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="list-group list-group-flush">
                        <!-- Alerta Planos -->
                        <div class="list-group-item px-0 py-3 d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <span class="badge rounded-circle bg-danger bg-opacity-10 text-danger p-2">
                                    <i class="bi bi-clock-history fs-5"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 fw-bold">Planos Atrasados</h6>
                                <small class="text-muted">{{ $stats['planosAtrasados'] }} planos precisam de atenção.</small>
                            </div>
                            <a href="{{ route('planos.index') }}?filtroStatus=Atrasado" class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-arrow-right"></i></a>
                        </div>

                        <!-- Alerta Indicadores -->
                        <div class="list-group-item px-0 py-3 d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <span class="badge rounded-circle bg-warning bg-opacity-10 text-warning p-2">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 fw-bold">Lançamentos Pendentes</h6>
                                <small class="text-muted">{{ $stats['indicadoresSemLancamento'] }} indicadores sem dados no mês.</small>
                            </div>
                            <a href="{{ route('indicadores.index') }}" class="btn btn-sm btn-outline-warning border-0"><i class="bi bi-arrow-right"></i></a>
                        </div>

                        <!-- Atalho Relatório -->
                        <div class="list-group-item px-0 py-3 d-flex align-items-center border-bottom-0">
                            <div class="flex-shrink-0">
                                <span class="badge rounded-circle bg-primary bg-opacity-10 text-primary p-2">
                                    <i class="bi bi-file-earmark-pdf fs-5"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 fw-bold">Relatório Gerencial</h6>
                                <small class="text-muted">Gerar PDF consolidado da unidade.</small>
                            </div>
                            <a href="{{ route('relatorios.executivo') }}" class="btn btn-sm btn-primary rounded-pill px-3">Gerar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts de Gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            initBscChart();
        });

        function initBscChart() {
            const ctx = document.getElementById('bscChart');
            if (!ctx) return;

            const chartData = @json($chartBSC);
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.map(i => i.label),
                    datasets: [{
                        label: 'Quantidade de Objetivos',
                        data: chartData.map(i => i.count),
                        backgroundColor: [
                            'rgba(27, 64, 142, 0.8)',
                            'rgba(13, 202, 240, 0.8)',
                            'rgba(25, 135, 84, 0.8)',
                            'rgba(255, 193, 7, 0.8)'
                        ],
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { stepSize: 1 } },
                        y: { grid: { display: false } }
                    }
                }
            });
        }
    </script>
</div>