<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('indicadores.index') }}" class="text-decoration-none">Indicadores</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Ficha Técnica</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">{{ $indicador->nom_indicador }}</h2>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('indicadores.evolucao', $indicador->cod_indicador) }}" class="btn btn-success rounded-pill px-3 shadow-sm">
                    <i class="bi bi-graph-up-arrow me-1"></i> Lançar Resultados
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row g-4">
        <!-- Coluna Esquerda: Ficha Técnica -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-card-list me-2 text-primary"></i>Ficha Técnica</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold d-block">Descrição</label>
                        <p class="small">{{ $indicador->dsc_indicador ?: 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold d-block">Unidade / Periodicidade</label>
                        <p class="small fw-bold">{{ $indicador->dsc_unidade_medida }} ({{ $indicador->dsc_periodo_medicao }})</p>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold d-block">Fórmula de Cálculo</label>
                        <div class="p-2 bg-light rounded border border-dashed small">
                            <code>{{ $indicador->dsc_formula ?: 'Não informada' }}</code>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold d-block">Fonte de Dados</label>
                        <p class="small">{{ $indicador->dsc_fonte ?: 'N/A' }}</p>
                    </div>
                    <div class="mb-0">
                        <label class="small text-muted text-uppercase fw-bold d-block">Vínculo</label>
                        @if($indicador->cod_objetivo)
                            <small class="text-primary fw-bold"><i class="bi bi-bullseye"></i> Objetivo: {{ $indicador->objetivo->nom_objetivo }}</small>
                        @else
                            <small class="text-info fw-bold"><i class="bi bi-list-task"></i> Plano: {{ $indicador->planoDeAcao->dsc_plano_de_acao }}</small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Metas e Linha de Base -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-bullseye me-2 text-primary"></i>Metas e Linha de Base</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <table class="table table-sm small">
                        <thead class="table-light">
                            <tr><th>Ano</th><th>Linha Base</th><th>Meta</th></tr>
                        </thead>
                        <tbody>
                            @php
                                $anos = collect($indicador->metasPorAno->pluck('num_ano'))
                                    ->merge($indicador->linhaBase->pluck('num_ano'))
                                    ->unique()->sort();
                            @endphp
                            @foreach($anos as $ano)
                                <tr>
                                    <td>{{ $ano }}</td>
                                    <td>@brazil_number($indicador->linhaBase->where('num_ano', $ano)->first()?->num_linha_base ?? 0, 2)</td>
                                    <td class="fw-bold">@brazil_number($indicador->metasPorAno->where('num_ano', $ano)->first()?->meta ?? 0, 2)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Gráficos e Evolução -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4" 
                 wire:key="chart-card-{{ $anoFiltro }}"
                 x-data="{ 
                    chartData: @entangle('chartData'),
                    init() {
                        this.initChart();
                        
                        // Escuta o evento explícito do Livewire para atualização forçada
                        window.addEventListener('updateChart', event => {
                            this.updateChartData(event.detail.data);
                        });

                        // Watch para mudanças via entangle (ex: mudanças no navbar)
                        $watch('chartData', (value) => {
                            this.updateChartData(value);
                        });
                    },
                    initChart() {
                        const canvas = document.getElementById('evolucaoChart');
                        if (!canvas) return;
                        
                        // Garante que não existam gráficos fantasmas no mesmo canvas
                        const existingChart = Chart.getChart(canvas);
                        if (existingChart) existingChart.destroy();
                        
                        new Chart(canvas, {
                            type: 'line',
                            data: {
                                labels: this.chartData.labels,
                                datasets: [
                                    {
                                        label: 'Previsto',
                                        data: this.chartData.previsto,
                                        borderColor: '#6c757d',
                                        borderDash: [5, 5],
                                        tension: 0.1,
                                        fill: false,
                                        spanGaps: true
                                    },
                                    {
                                        label: 'Realizado',
                                        data: this.chartData.realizado,
                                        borderColor: '#0d6efd',
                                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                                        fill: true,
                                        tension: 0.3,
                                        pointRadius: 5,
                                        pointBackgroundColor: '#0d6efd',
                                        spanGaps: false
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: { 
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return value.toLocaleString('pt-BR');
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: { position: 'bottom' },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                let label = context.dataset.label || '';
                                                if (label) label += ': ';
                                                if (context.parsed.y !== null) {
                                                    label += context.parsed.y.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    },
                    updateChartData(data) {
                        const canvas = document.getElementById('evolucaoChart');
                        const chart = Chart.getChart(canvas);
                        if (chart && data) {
                            chart.data.datasets[0].data = data.previsto;
                            chart.data.datasets[1].data = data.realizado;
                            chart.update();
                        }
                    }
                 }">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-graph-up me-2 text-primary"></i>Evolução Temporal</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted">Ano:</span>
                        <select wire:model.live="anoFiltro" class="form-select form-select-sm w-auto fw-bold">
                            @foreach($anosDisponiveis as $ano)
                                <option value="{{ $ano }}">{{ $ano }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div style="height: 300px;">
                        <canvas id="evolucaoChart" wire:ignore></canvas>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-primary"></i>Detalhamento Mensal ({{ $anoFiltro }})</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Mês</th>
                                <th>Previsto</th>
                                <th>Realizado</th>
                                <th>Atingimento</th>
                                <th class="pe-4">Análise</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $mesesNomes = [1=>'Jan', 2=>'Fev', 3=>'Mar', 4=>'Abr', 5=>'Mai', 6=>'Jun', 7=>'Jul', 8=>'Ago', 9=>'Set', 10=>'Out', 11=>'Nov', 12=>'Dez'];
                            @endphp
                            @foreach($mesesNomes as $num => $nome)
                                @php
                                    $ev = $indicador->evolucoes->where('num_ano', (int)$anoFiltro)->where('num_mes', $num)->first();
                                    $ating = $ev ? $ev->calcularAtingimento() : 0;
                                @endphp
                                <tr>
                                    <td class="ps-4">{{ $nome }}</td>
                                    <td>@if($ev) @brazil_number($ev->vlr_previsto, 2) @else - @endif</td>
                                    <td class="fw-bold">@if($ev) @brazil_number($ev->vlr_realizado, 2) @else - @endif</td>
                                    <td>
                                        @if($ev)
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                    <div class="progress-bar bg-{{ $ating >= 100 ? 'success' : ($ating >= 80 ? 'warning' : 'danger') }}" style="width: {{ min($ating, 100) }}%"></div>
                                                </div>
                                                <span class="fw-bold">@brazil_percent($ating, 1)</span>
                                            </div>
                                        @else
                                            <span class="text-muted opacity-50">-</span>
                                        @endif
                                    </td>
                                    <td class="pe-4">
                                        <small class="text-muted">{{ $ev ? Str::limit($ev->txt_avaliacao, 50) : '-' }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</div>