<div class="dashboard-wrapper" 
     wire:poll.30s 
     wire:key="dashboard-{{ $peiAtivo?->cod_pei }}-{{ $organizacaoId }}-{{ $anoSelecionado }}"
     x-data="dashboardData()">
    
    <style>
        .dashboard-wrapper {
            background-color: transparent !important; 
            min-height: 100vh;
            padding: 1.5rem;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6); /* Borda padrão do glass */
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .glass-panel:hover {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 15px -3px rgba(27, 64, 142, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
            transform: translateY(-2px);
            border-color: rgba(27, 64, 142, 0.2);
            cursor: pointer;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.5);
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            padding: 1rem 1.5rem;
            border-radius: 16px 16px 0 0;
        }

        .metric-value {
            background: linear-gradient(135deg, #1B408E 0%, #0d6efd 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        
        .mentor-step {
            position: relative;
            z-index: 1;
        }
        .mentor-line {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 2px;
            background: #e9ecef;
            z-index: 0;
            transform: translateY(-50%);
        }
        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background: white;
            border: 2px solid #e9ecef;
            margin: 0 auto 8px;
            transition: all 0.3s ease;
        }
        /* Ajuste do ícone check */
        .check-icon-absolute {
            position: absolute;
            top: -5px; 
            right: -5px; 
            width: 16px; 
            height: 16px; 
            background: white;
            border: 1px solid #198754;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #198754;
        }

        .step-circle.active {
            border-color: #198754;
            color: #198754;
            box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.1);
        }
        .step-circle.pending {
            border-color: #ffc107;
            color: #ffc107;
            border-style: dashed;
        }
        
        .animate-entry { animation: slideUp 0.5s ease-out forwards; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-end mb-4 animate-entry">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge glass-panel text-primary px-3 py-2 fw-bold">
                    <i class="bi bi-calendar-event me-2"></i>Exercício {{ $anoSelecionado }}
                </span>
                @if($organizacaoId)
                    <span class="badge glass-panel text-secondary px-3 py-2 fw-medium">
                        <i class="bi bi-building me-2"></i>{{ Str::limit($organizacaoNome, 30) }}
                    </span>
                @endif
            </div>
            <h2 class="fw-bold text-dark mb-0" style="letter-spacing: -0.5px;">
                Visão Estratégica
            </h2>
            <p class="text-muted mb-0 mt-1">Monitoramento em tempo real dos indicadores de desempenho.</p>
        </div>

        {{-- Item 1: Botão AI Corrigido (d-none) --}}
        <button wire:click="generateAiSummary" wire:loading.attr="disabled" class="btn glass-panel text-primary fw-bold px-4 py-2 border d-flex align-items-center gap-2">
            <span wire:loading.remove wire:target="generateAiSummary" class="d-flex align-items-center gap-2">
                <i class="bi bi-stars text-warning fs-5"></i>
                <span>Gerar Análise AI</span>
            </span>
            <span wire:loading wire:target="generateAiSummary" class="d-flex align-items-center gap-2 d-none" wire:loading.class.remove="d-none">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <span>Analisando...</span>
            </span>
        </button>
    </div>

    {{-- Item 2, 4, 5, 6: Mentor Estratégico --}}
    @php
        $mentor = $this->getMentorStatus();
        $isComplete = $mentor['percent'] >= 100;
        $steps = [
            'identidade' => ['label' => 'Identidade', 'icon' => 'bi-gem'],
            'mapa' => ['label' => 'Mapa', 'icon' => 'bi-map'],
            'objetivos' => ['label' => 'Objetivos', 'icon' => 'bi-bullseye'],
            'indicadores' => ['label' => 'Indicadores', 'icon' => 'bi-graph-up'],
            'planos' => ['label' => 'Planos', 'icon' => 'bi-list-check']
        ];
    @endphp
    
    @if($isComplete)
        <div class="d-flex justify-content-end mb-3">
             <button class="btn btn-sm btn-outline-success border-0 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mentorCollapse" aria-expanded="false" aria-controls="mentorCollapse">
                <i class="bi bi-check-circle-fill me-1"></i> Planejamento Completo (Mostrar/Ocultar)
            </button>
        </div>
    @endif

    <div class="collapse {{ !$isComplete ? 'show' : '' }}" id="mentorCollapse">
        <div class="glass-panel p-4 mb-5 animate-entry delay-1 border"> <!-- Border restaurado -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-dark mb-0 fs-6">
                    <i class="bi bi-mortarboard-fill text-primary me-2"></i>Mentor Estratégico
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <div class="progress" style="width: 100px; height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $mentor['percent'] }}%"></div>
                    </div>
                    <span class="small fw-bold {{ $mentor['percent'] == 100 ? 'text-success' : 'text-primary' }}">{{ $mentor['percent'] }}% Completo</span>
                </div>
            </div>
            
            <div class="position-relative px-4">
                <div class="mentor-line"></div>
                <div class="d-flex justify-content-between position-relative">
                    @foreach($steps as $key => $meta)
                        <div class="text-center mentor-step position-relative" style="width: 80px;">
                            <div class="step-circle {{ $mentor['steps'][$key] ? 'active' : 'pending' }} glass-panel mx-auto position-relative">
                                <i class="bi {{ $meta['icon'] }}"></i>
                                @if($mentor['steps'][$key])
                                    <div class="check-icon-absolute">
                                        <i class="bi bi-check"></i>
                                    </div>
                                @endif
                            </div>
                            <span class="small fw-bold text-secondary d-block mt-2">{{ $meta['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- AI Insight --}}
    @if($aiSummary)
        <div class="glass-panel mb-5 animate-entry p-4 position-relative overflow-hidden border" style="border-left: 4px solid #F3C72B !important;">
            <div class="d-flex justify-content-between align-items-start position-relative z-1">
                <div>
                    <h6 class="text-warning fw-bold text-uppercase mb-2"><i class="bi bi-stars me-2"></i>Insight Estratégico</h6>
                    <div class="text-dark" style="font-size: 1rem; line-height: 1.6;">{!! nl2br(e($aiSummary)) !!}</div>
                </div>
                <button type="button" class="btn-close" wire:click="$set('aiSummary', '')"></button>
            </div>
        </div>
    @endif

    {{-- Metric Cards Grid --}}
    <div class="row g-4 mb-5">
        {{-- Card 1: Execução --}}
        <a href="{{ route('planos.index') }}" class="col-xl-3 col-md-6 animate-entry delay-1 text-decoration-none">
            <div class="glass-panel h-100 p-4 position-relative overflow-hidden border"> <!-- Border restaurado -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-secondary text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Execução no Exercício</p>
                        <h2 class="metric-value mb-0" style="font-size: 2.5rem;">{{ number_format($stats['progressoPlanos'], 1) }}%</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle text-primary"><i class="bi bi-activity fs-4"></i></div>
                </div>
                <div class="progress bg-secondary bg-opacity-10" style="height: 6px; border-radius: 10px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $stats['progressoPlanos'] }}%; border-radius: 10px;"></div>
                </div>
                <div class="mt-3 text-muted small">
                    <span class="fw-bold text-primary">{{ $stats['planosConcluidos'] }}</span> concluídos de {{ $stats['totalPlanos'] }} planos do ano
                </div>
            </div>
        </a>

        {{-- Card 2: Saúde --}}
        <a href="#" class="col-xl-3 col-md-6 animate-entry delay-2 text-decoration-none">
            <div class="glass-panel h-100 p-4 position-relative border"> <!-- Border restaurado -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <p class="text-secondary text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Saúde da Estratégia</p>
                        <h2 class="metric-value mb-0" style="font-size: 2.5rem;">{{ $stats['totalObjetivos'] }}</h2>
                    </div>
                    <div class="bg-success bg-opacity-10 p-2 rounded-circle text-success"><i class="bi bi-bullseye fs-4"></i></div>
                </div>
                <p class="text-muted small mb-0 mt-2">Objetivos Estratégicos Ativos</p>
                <div class="mt-3 row g-2">
                    <div class="col-6">
                        <div class="bg-light rounded p-2 text-center border">
                            <i class="bi bi-layers text-secondary d-block mb-1"></i>
                            <span class="fw-bold text-dark small">{{ $stats['totalPerspectivas'] }} Persp.</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-2 text-center border">
                            <i class="bi bi-graph-up text-secondary d-block mb-1"></i>
                            <span class="fw-bold text-dark small">{{ $stats['totalIndicadores'] }} Indic.</span>
                        </div>
                    </div>
                </div>
            </div>
        </a>

        {{-- Card 3: Riscos Críticos --}}
        <a href="{{ route('riscos.index') }}" class="col-xl-3 col-md-6 animate-entry delay-3 text-decoration-none">
            <div class="glass-panel h-100 p-4 position-relative border"> <!-- Border restaurado -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <p class="text-secondary text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Riscos Críticos</p>
                        <h2 class="metric-value mb-0 {{ $stats['riscosCriticos'] > 0 ? 'text-danger' : 'text-success' }}" style="font-size: 2.5rem;">
                            {{ $stats['riscosCriticos'] }}
                        </h2>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-2 rounded-circle text-danger"><i class="bi bi-shield-exclamation fs-4"></i></div>
                </div>
                <p class="text-muted small mb-0 mt-2">
                    De <span class="fw-bold text-dark">{{ $stats['totalRiscos'] }}</span> mapeados. <span class="text-primary fw-bold text-decoration-underline">Ver detalhes</span>
                </p>
            </div>
        </a>

        {{-- Card 4: Indicadores --}}
        <a href="#" class="col-xl-3 col-md-6 animate-entry delay-4 text-decoration-none">
            <div class="glass-panel h-100 p-4 position-relative border"> <!-- Border restaurado -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <p class="text-secondary text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Indicadores Monitorados</p>
                        <h2 class="metric-value mb-0" style="font-size: 2.5rem;">{{ $stats['totalIndicadores'] }}</h2>
                    </div>
                    <div class="bg-info bg-opacity-10 p-2 rounded-circle text-info"><i class="bi bi-graph-up fs-4"></i></div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Acompanhamento</span>
                        <span class="fw-bold text-dark">Em dia</span>
                    </div>
                    <div class="progress bg-secondary bg-opacity-10" style="height: 4px; border-radius: 10px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 100%; border-radius: 10px;"></div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Charts Section --}}
    <div class="row g-4 mb-5 animate-entry delay-4">
        {{-- Chart Evolução --}}
        <div class="col-xl-8">
            <div class="glass-panel h-100 d-flex flex-column border"> <!-- Border restaurado -->
                <div class="glass-header d-flex justify-content-between align-items-center bg-transparent border-bottom border-light">
                    <div>
                        <h5 class="fw-bold text-dark mb-0 fs-6"><i class="bi bi-activity text-primary me-2"></i>Curva de Evolução</h5>
                        <small class="text-muted" style="font-size: 0.75rem;">Média mensal do atingimento das metas dos indicadores.</small>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ $anoSelecionado }}</span>
                </div>
                <div class="p-4 flex-grow-1">
                    <div style="height: 300px; width: 100%;" wire:ignore>
                        <canvas id="evolucaoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Status e Riscos --}}
        <div class="col-xl-4">
            <div class="d-flex flex-column gap-4 h-100">
                <div class="glass-panel flex-grow-1 d-flex flex-column border"> <!-- Border restaurado -->
                    <div class="glass-header bg-transparent border-bottom border-light py-3">
                        <h5 class="fw-bold text-dark mb-0 fs-6">Status dos Planos</h5>
                        <small class="text-muted d-block" style="font-size: 0.75rem;">Distribuição dos planos ativos no ano.</small>
                    </div>
                    <div class="p-4 flex-grow-1 d-flex align-items-center justify-content-center">
                        <div style="height: 180px; width: 100%;" wire:ignore>
                            <canvas id="planosChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="glass-panel flex-grow-1 d-flex flex-column border"> <!-- Border restaurado -->
                    <div class="glass-header bg-transparent border-bottom border-light py-3">
                        <h5 class="fw-bold text-dark mb-0 fs-6">Riscos por Severidade</h5>
                        <small class="text-muted d-block" style="font-size: 0.75rem;">Matriz de risco (Impacto x Probabilidade).</small>
                    </div>
                    <div class="p-4 flex-grow-1 d-flex align-items-center justify-content-center">
                        <div style="height: 180px; width: 100%;" wire:ignore>
                            <canvas id="riscosNivelChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BSC Chart --}}
    <div class="glass-panel p-4 mb-5 animate-entry delay-3 border"> <!-- Border restaurado -->
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-1 fs-6"><i class="bi bi-layers text-primary me-2"></i>Performance por Perspectiva</h5>
            <small class="text-muted">Atingimento consolidado dos indicadores e planos de cada perspectiva do BSC.</small>
        </div>
        <div style="height: 250px; width: 100%;" wire:ignore>
            <canvas id="bscChart"></canvas>
        </div>
    </div>
    
    <script>
        function dashboardData() {
            return {
                chartData: @entangle('chartData'),
                charts: { bsc: null, riscos: null, planos: null, evolucao: null },
                
                init() {
                    if (typeof Chart === 'undefined') return;
                    Chart.defaults.font.family = "'Inter', sans-serif";
                    Chart.defaults.color = '#64748b';
                    Chart.defaults.scale.grid.color = 'rgba(0, 0, 0, 0.03)';
                    
                    this.updateAllCharts();
                    this.$watch('chartData', (newValue, oldValue) => {
                        if (JSON.stringify(newValue) !== JSON.stringify(oldValue)) {
                            this.updateAllCharts();
                        }
                    });
                },
                
                updateAllCharts() {
                    this.renderChart('evolucaoChart', 'line', {
                        labels: this.chartData.evolucao.labels,
                        datasets: [{
                            label: 'Desempenho Médio',
                            data: this.chartData.evolucao.data,
                            borderColor: '#1B408E',
                            backgroundColor: (ctx) => {
                                const g = ctx.chart.ctx.createLinearGradient(0,0,0,300);
                                g.addColorStop(0, 'rgba(27, 64, 142, 0.15)'); g.addColorStop(1, 'rgba(27, 64, 142, 0)');
                                return g;
                            },
                            borderWidth: 3, fill: true, tension: 0.4, pointRadius: 4, pointBackgroundColor: '#fff', pointBorderColor: '#1B408E', pointBorderWidth: 2
                        }]
                    }, { interaction: { intersect: false, mode: 'index' }, plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, cornerRadius: 8 } }, scales: { y: { beginAtZero: true, max: 100, border: { display: false } }, x: { grid: { display: false }, border: { display: false } } } });

                    this.renderChart('planosChart', 'doughnut', {
                        labels: this.chartData.planos.map(i => i.label),
                        datasets: [{ data: this.chartData.planos.map(i => i.count), backgroundColor: this.chartData.planos.map(i => i.color), borderWidth: 0, hoverOffset: 4 }]
                    }, { cutout: '75%', plugins: { legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 8, font: { size: 10 } } } } });

                    this.renderChart('riscosNivelChart', 'doughnut', {
                        labels: this.chartData.riscos.labels,
                        datasets: [{ data: this.chartData.riscos.data, backgroundColor: this.chartData.riscos.colors, borderWidth: 0 }]
                    }, { cutout: '75%', plugins: { legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 8, font: { size: 10 } } } } });

                    this.renderChart('bscChart', 'bar', {
                        labels: this.chartData.bsc.map(i => i.label),
                        datasets: [{ label: 'Atingimento', data: this.chartData.bsc.map(i => i.count), backgroundColor: this.chartData.bsc.map(i => i.color), borderRadius: 6, barThickness: 24 }]
                    }, { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { max: 100, border: { display: false } }, y: { grid: { display: false }, border: { display: false } } } });
                },
                
                renderChart(id, type, data, options) {
                    const canvas = document.getElementById(id);
                    if (!canvas) return;
                    if (this.charts[id]) {
                        this.charts[id].data = data;
                        this.charts[id].update();
                    } else {
                        this.charts[id] = new Chart(canvas, { type, data, options: { ...options, responsive: true, maintainAspectRatio: false } });
                    }
                }
            }
        }
    </script>
</div>