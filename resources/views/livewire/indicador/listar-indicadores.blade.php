<div>
    {{-- Header Interno para garantir escopo do Livewire --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Indicadores (KPIs)</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="icon-circle-header gradient-theme-icon">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <h2 class="h4 fw-bold mb-0">Indicadores de Desempenho</h2>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($organizacaoId)
                <div class="dropdown">
                    <button class="btn btn-outline-secondary rounded-pill px-3 shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-download me-1"></i> Exportar
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                        <li><a class="dropdown-item" href="{{ route('relatorios.indicadores.pdf') }}"><i class="bi bi-file-earmark-pdf text-danger me-2"></i> PDF</a></li>
                        <li><a class="dropdown-item" href="{{ route('relatorios.indicadores.excel') }}"><i class="bi bi-file-earmark-excel text-success me-2"></i> Excel</a></li>
                    </ul>
                </div>
                <button wire:click="create" class="btn btn-primary gradient-theme-btn px-4 shadow-sm">
                    <i class="bi bi-plus-lg me-2"></i>Novo Indicador
                </button>
            @endif
        </div>
    </div>

    {{-- Seção Educativa: O que são Indicadores (KPIs) --}}
    <div class="card border-0 shadow-sm mb-4 educational-card-gradient" x-data="{ expanded: false }">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle bg-white bg-opacity-25">
                        <i class="bi bi-book-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">
                            <i class="bi bi-mortarboard me-2"></i>{{ __('O que são Indicadores (KPIs)?') }}
                        </h5>
                        <p class="mb-0 text-white-50 small">
                            {{ __('Aprenda a medir e monitorar o desempenho estratégico') }}
                        </p>
                    </div>
                </div>
                <button @click="expanded = !expanded" class="btn btn-link text-white text-decoration-none p-0" type="button">
                    <i class="bi fs-4" :class="expanded ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
            </div>
        </div>

        <div x-show="expanded" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
            <div class="card-body p-4 bg-white border-top">
                {{-- Introdução --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-info-circle me-2"></i>{{ __('O que são Indicadores?') }}
                    </h6>
                    <p class="text-muted mb-3">
                        <strong>Indicadores</strong> (ou KPIs - Key Performance Indicators) são <strong>métricas quantificáveis</strong> que medem o progresso em direção aos objetivos estratégicos.
                        Eles transformam conceitos abstratos ("melhorar atendimento") em <strong>números concretos</strong> ("reduzir tempo de espera para 15 minutos").
                    </p>
                    <p class="text-muted mb-0">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        <strong>Por que usar?</strong> "O que não é medido, não é gerenciado" (Peter Drucker).
                        Indicadores permitem monitorar desempenho, identificar desvios cedo e tomar decisões baseadas em dados.
                    </p>
                </div>

                {{-- Tipos de Indicadores: Leading vs Lagging --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-diagram-3 me-2"></i>{{ __('Tipos de Indicadores') }}
                    </h6>
                    <p class="small text-muted mb-3">
                        Indicadores se dividem em dois grandes grupos:
                    </p>

                    <div class="row g-3">
                        {{-- Leading (Tendência) --}}
                        <div class="col-md-6">
                            <div class="card border-2 border-primary h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-speedometer"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-primary">Leading (Tendência)</h6>
                                    </div>
                                    <p class="small text-muted mb-3">
                                        Indicadores de <strong>processo</strong>. Medem atividades que <strong>influenciam resultados futuros</strong>.
                                        São preditivos e controláveis.
                                    </p>
                                    <div class="bg-light p-2 rounded mb-2">
                                        <p class="fw-bold x-small mb-1 text-dark">Exemplos:</p>
                                        <ul class="x-small text-muted mb-0">
                                            <li>Nº de servidores treinados/mês</li>
                                            <li>Horas de manutenção preventiva</li>
                                            <li>Nº de processos mapeados</li>
                                            <li>Taxa de participação em pesquisas</li>
                                        </ul>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        <i class="bi bi-arrow-right text-primary me-1"></i>
                                        <strong>Use quando:</strong> Quer influenciar resultados futuros com ações hoje
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Lagging (Resultado) --}}
                        <div class="col-md-6">
                            <div class="card border-2 border-success h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-trophy"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-success">Lagging (Resultado)</h6>
                                    </div>
                                    <p class="small text-muted mb-3">
                                        Indicadores de <strong>resultado</strong>. Medem <strong>conquistas já alcançadas</strong>.
                                        Mostram o impacto final, mas são difíceis de mudar no curto prazo.
                                    </p>
                                    <div class="bg-light p-2 rounded mb-2">
                                        <p class="fw-bold x-small mb-1 text-dark">Exemplos:</p>
                                        <ul class="x-small text-muted mb-0">
                                            <li>Satisfação do cidadão (%)</li>
                                            <li>Taxa de execução orçamentária</li>
                                            <li>Tempo médio de atendimento</li>
                                            <li>Índice de transparência</li>
                                        </ul>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        <i class="bi bi-arrow-right text-success me-1"></i>
                                        <strong>Use quando:</strong> Quer medir o impacto final do trabalho realizado
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Polaridade --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-arrows-expand me-2"></i>{{ __('Polaridade: Maior ou Menor é Melhor?') }}
                    </h6>
                    <p class="small text-muted mb-3">
                        A <strong>polaridade</strong> define se o objetivo é <strong>aumentar</strong> ou <strong>diminuir</strong> o valor do indicador.
                    </p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-0 bg-success bg-opacity-5 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-arrow-up-circle-fill text-success fs-4"></i>
                                        <h6 class="fw-bold mb-0">Maior é Melhor</h6>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        Queremos que o valor <strong>cresça</strong>. O sucesso é medido pelo aumento.
                                    </p>
                                    <div class="bg-white p-2 rounded">
                                        <p class="fw-bold x-small mb-1">Exemplos:</p>
                                        <ul class="x-small text-muted mb-0">
                                            <li>Satisfação dos cidadãos (%)</li>
                                            <li>Taxa de execução orçamentária (%)</li>
                                            <li>Nº de processos digitalizados</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-danger bg-opacity-5 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-arrow-down-circle-fill text-danger fs-4"></i>
                                        <h6 class="fw-bold mb-0">Menor é Melhor</h6>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        Queremos que o valor <strong>diminua</strong>. O sucesso é medido pela redução.
                                    </p>
                                    <div class="bg-white p-2 rounded">
                                        <p class="fw-bold x-small mb-1">Exemplos:</p>
                                        <ul class="x-small text-muted mb-0">
                                            <li>Tempo médio de atendimento (min)</li>
                                            <li>Taxa de processos atrasados (%)</li>
                                            <li>Nº de reclamações/mês</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Fórmula e Unidade de Medida --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-calculator me-2"></i>{{ __('Fórmula e Unidade de Medida') }}
                    </h6>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <p class="small text-muted mb-3">
                                Todo indicador deve ter uma <strong>fórmula clara</strong> e uma <strong>unidade de medida</strong> para garantir consistência.
                            </p>

                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-primary">
                                        <tr>
                                            <th class="fw-bold small">Indicador</th>
                                            <th class="fw-bold small">Fórmula</th>
                                            <th class="fw-bold small">Unidade</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <tr>
                                            <td>Taxa de Satisfação</td>
                                            <td>(Clientes satisfeitos / Total de respondentes) × 100</td>
                                            <td>%</td>
                                        </tr>
                                        <tr>
                                            <td>Tempo Médio de Atendimento</td>
                                            <td>Soma dos tempos / Nº de atendimentos</td>
                                            <td>minutos</td>
                                        </tr>
                                        <tr>
                                            <td>Taxa de Execução Orçamentária</td>
                                            <td>(Orçamento executado / Orçamento aprovado) × 100</td>
                                            <td>%</td>
                                        </tr>
                                        <tr>
                                            <td>Processos Digitalizados</td>
                                            <td>Contagem direta</td>
                                            <td>unidades</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Exemplo Completo --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-star me-2"></i>{{ __('Exemplo Completo de Indicador') }}
                    </h6>

                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-graph-up me-2"></i>Taxa de Satisfação dos Cidadãos
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="small mb-1 text-muted"><strong>Objetivo vinculado:</strong></p>
                                    <p class="x-small mb-3">"Aumentar satisfação dos cidadãos com serviços públicos"</p>

                                    <p class="small mb-1 text-muted"><strong>Descrição:</strong></p>
                                    <p class="x-small mb-3">Percentual de cidadãos que avaliaram o atendimento como "bom" ou "excelente"</p>

                                    <p class="small mb-1 text-muted"><strong>Fórmula:</strong></p>
                                    <p class="x-small mb-3">(Avaliações positivas / Total de avaliações) × 100</p>
                                </div>

                                <div class="col-md-6">
                                    <p class="small mb-1 text-muted"><strong>Unidade:</strong></p>
                                    <p class="x-small mb-3">Percentual (%)</p>

                                    <p class="small mb-1 text-muted"><strong>Polaridade:</strong></p>
                                    <p class="x-small mb-3"><i class="bi bi-arrow-up-circle text-success me-1"></i>Maior é melhor</p>

                                    <p class="small mb-1 text-muted"><strong>Tipo:</strong></p>
                                    <p class="x-small mb-3">Lagging (resultado)</p>

                                    <p class="small mb-1 text-muted"><strong>Periodicidade:</strong></p>
                                    <p class="x-small mb-0">Mensal</p>
                                </div>

                                <div class="col-12">
                                    <div class="alert alert-info mb-0 py-2">
                                        <p class="fw-bold small mb-1"><i class="bi bi-bullseye me-1"></i>Meta para 2025:</p>
                                        <p class="x-small mb-0">Alcançar 85% de satisfação até dezembro/2025 (baseline atual: 68%)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dicas Profissionais --}}
                <div>
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-trophy me-2"></i>{{ __('Boas Práticas para Indicadores Eficazes') }}
                    </h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Use critério SMART</p>
                                    <p class="x-small text-muted mb-0">Específico, Mensurável, Atingível, Relevante e Temporal. Ex: "85% até dez/2025"</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Evite indicadores "vaidade"</p>
                                    <p class="x-small text-muted mb-0">Foque no que importa. "Nº de curtidas" não mede impacto real na sociedade</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Combine Leading e Lagging</p>
                                    <p class="x-small text-muted mb-0">Monitore ações (leading) E resultados (lagging) para visão completa</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Documente fonte dos dados</p>
                                    <p class="x-small text-muted mb-0">Especifique de onde vem o dado: sistema X, pesquisa Y, relatório Z</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Revise e ajuste periodicamente</p>
                                    <p class="x-small text-muted mb-0">Se um indicador sempre fica verde ou sempre vermelho, revise a meta ou a relevância dele</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Mentor de IA --}}
    @if($organizacaoId && $aiEnabled)
        <div class="ai-mentor-wrapper animate-fade-in">
            <button wire:click="pedirAjudaIA" wire:loading.attr="disabled" class="ai-magic-button shadow-sm">
                <span wire:loading.remove wire:target="pedirAjudaIA">
                    <i class="bi bi-robot"></i> {{ __('Sugerir Indicadores (KPIs) com IA') }}
                </span>
                <span wire:loading wire:target="pedirAjudaIA">
                    <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Definindo métricas ideais...') }}
                </span>
            </button>

            @if($aiSuggestion)
                <div class="ai-insight-card animate-fade-in">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-robot text-primary"></i>
                            <h6 class="fw-bold mb-0">{{ __('KPIs Recomendados pelo Mentor IA') }}</h6>
                        </div>
                        <button type="button" class="btn-close small" style="font-size: 0.7rem;" wire:click="$set('aiSuggestion', '')"></button>
                    </div>
                    <div class="card-body">
                        @if(is_array($aiSuggestion))
                            <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                                @foreach($aiSuggestion as $kpi)
                                    <div class="list-group-item d-flex align-items-start justify-content-between p-3 bg-light bg-opacity-25 hover-bg-white transition-all gap-3">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark">{{ $kpi['nome'] }}</div>
                                            <p class="small text-muted mb-2 mt-1">{{ $kpi['descricao'] }}</p>
                                            <div class="d-flex gap-2">
                                                <span class="badge bg-info-subtle text-info small border-0">{{ __('Unidade: ') }}{{ $kpi['unidade'] }}</span>
                                                <span class="badge bg-secondary-subtle text-secondary small border-0">{{ __('Fórmula: ') }}{{ $kpi['formula'] }}</span>
                                            </div>
                                        </div>
                                        <button wire:click="aplicarSugestao('{{ $kpi['nome'] }}', '{{ $kpi['descricao'] }}', '{{ $kpi['unidade'] }}', '{{ $kpi['formula'] }}')" 
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold flex-shrink-0">
                                            <i class="bi bi-plus-lg me-1"></i> {{ __('Adicionar') }}
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="markdown-content">
                                {!! Str::markdown($aiSuggestion) !!}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if(!$organizacaoId && !$filtroObjetivo)
        <div class="alert alert-warning shadow-sm border-0 d-flex align-items-center p-4" role="alert">
            <i class="bi bi-building-exclamation fs-2 me-4"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Selecione uma Organizacao</h5>
                <p class="mb-0">Selecione uma organizacao no menu superior para listar e gerenciar indicadores.</p>
            </div>
        </div>
    @else
        @if($filtroObjetivo)
            @php
                $objetivoFiltrado = \App\Models\StrategicPlanning\Objetivo::with(['perspectiva.pei', 'indicadores.evolucoes', 'indicadores.metasPorAno', 'planosAcao'])->find($filtroObjetivo);
            @endphp

            {{-- Contexto Completo do Objetivo --}}
            @include('livewire.partials.objetivo-contexto', ['objetivo' => $objetivoFiltrado])

            {{-- Grafico de Evolucao dos Indicadores --}}
            @if($objetivoFiltrado && $objetivoFiltrado->indicadores->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-graph-up text-primary me-2"></i>Evolucao dos Indicadores
                            </h6>
                            <small class="text-muted">Ultimos 6 meses</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="chartEvolucaoIndicadores" height="120"></canvas>
                    </div>
                </div>

                @php
                    // Preparar dados para o grafico
                    $meses = [];
                    $dadosGrafico = [];
                    for ($i = 5; $i >= 0; $i--) {
                        $data = now()->subMonths($i);
                        $meses[] = $data->translatedFormat('M/y');
                    }

                    foreach ($objetivoFiltrado->indicadores->take(5) as $ind) {
                        $valores = [];
                        for ($i = 5; $i >= 0; $i--) {
                            $data = now()->subMonths($i);
                            $evolucao = $ind->evolucoes
                                ->where('num_ano', $data->year)
                                ->where('num_mes', $data->month)
                                ->first();
                            $valores[] = $evolucao ? round(($evolucao->vlr_realizado / max($evolucao->vlr_previsto, 1)) * 100, 1) : null;
                        }
                        $dadosGrafico[] = [
                            'label' => Str::limit($ind->nom_indicador, 25),
                            'data' => $valores
                        ];
                    }
                @endphp

                <script>
                    (function() {
                        function initChartEvolucao() {
                            const ctx = document.getElementById('chartEvolucaoIndicadores');
                            if (ctx && typeof Chart !== 'undefined') {
                                // Destruir grafico existente se houver
                                if (ctx.chartInstance) {
                                    ctx.chartInstance.destroy();
                                }
                                const cores = ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#dc3545'];
                                ctx.chartInstance = new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: @json($meses),
                                        datasets: @json($dadosGrafico).map((item, idx) => ({
                                            label: item.label,
                                            data: item.data,
                                            borderColor: cores[idx % cores.length],
                                            backgroundColor: cores[idx % cores.length] + '20',
                                            tension: 0.3,
                                            fill: false,
                                            pointRadius: 4,
                                            pointHoverRadius: 6
                                        }))
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } },
                                            tooltip: { mode: 'index', intersect: false }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                max: 150,
                                                ticks: { callback: v => v + '%' }
                                            }
                                        }
                                    }
                                });
                            }
                        }
                        // Inicializar na carga inicial e na navegacao Livewire
                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', initChartEvolucao);
                        } else {
                            initChartEvolucao();
                        }
                        document.addEventListener('livewire:navigated', initChartEvolucao);
                    })();
                </script>
            @endif
        @endif
        <!-- Filtros -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 bg-light rounded-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" wire:model.live.debounce="search" class="form-control border-start-0 ps-0" placeholder="Buscar por nome do indicador...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="filtroVinculo" class="form-select">
                            <option value="">Todos os Vínculos</option>
                            <option value="Objetivo">Vínculo com Objetivo</option>
                            <option value="Plano">Vínculo com Plano</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <div wire:loading class="spinner-border text-primary spinner-border-sm" role="status"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Legenda de Desempenho --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-2 px-4 bg-white rounded-3">
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <span class="small fw-bold text-muted text-uppercase me-2"><i class="bi bi-info-circle me-1"></i>Legenda Desempenho (Indicadores):</span>
                    @foreach($grausSatisfacao as $grau)
                        <div class="d-flex align-items-center">
                            <span class="farol-dot me-1" style="background-color: {{ $grau->cor }}; width: 10px; height: 10px;"></span>
                            <small class="text-muted" style="font-size: 0.75rem;">{{ $grau->dsc_grau_satisfacao }} ({{ number_format($grau->vlr_minimo, 0) }}-{{ number_format($grau->vlr_maximo, 0) }}%)</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Tabela -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive" style="min-height: 350px;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Indicador / Descrição</th>
                            <th>Vínculo</th>
                            <th>Período / Unidade</th>
                            <th class="text-center">Polaridade</th>
                            <th>Performance</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indicadores as $ind)
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="fw-bold text-dark d-block mb-1">{{ $ind->nom_indicador }}</span>
                                    <small class="text-muted text-truncate d-block" style="max-width: 350px;">{{ $ind->dsc_indicador }}</small>
                                </td>
                                <td>
                                    @if($ind->cod_objetivo)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3">
                                            <i class="bi bi-bullseye me-1"></i> Objetivo
                                        </span>
                                        <small class="d-block text-muted mt-1 small-vinculo">{{ Str::limit($ind->objetivo->nom_objetivo ?? 'N/A', 40) }}</small>
                                    @else
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3">
                                                <i class="bi bi-list-task me-1"></i> Plano
                                            </span>
                                            @if($ind->dsc_calculation_type === 'action_plan')
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2" 
                                                      data-bs-toggle="tooltip" 
                                                      title="Calculado automaticamente pelo progresso ponderado das entregas do plano">
                                                    <i class="bi bi-lightning-charge-fill"></i> Auto
                                                </span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2"
                                                      data-bs-toggle="tooltip"
                                                      title="Valores lançados manualmente">
                                                    <i class="bi bi-pencil-fill"></i> Manual
                                                </span>
                                            @endif
                                        </div>
                                        <small class="d-block text-muted mt-1 small-vinculo">{{ Str::limit($ind->planoDeAcao->dsc_plano_de_acao ?? 'N/A', 40) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <small class="text-dark fw-semibold">{{ $ind->dsc_unidade_medida }}</small>
                                        <small class="text-muted">{{ $ind->dsc_periodo_medicao }}</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @php
                                        $polaridadeIcon = [
                                            'Positiva' => 'bi-arrow-up-circle-fill text-success',
                                            'Negativa' => 'bi-arrow-down-circle-fill text-danger',
                                            'Estabilidade' => 'bi-dash-circle-fill text-warning',
                                            'Não Aplicável' => 'bi-info-circle-fill text-muted'
                                        ][$ind->dsc_polaridade ?? 'Positiva'] ?? 'bi-question-circle';
                                    @endphp
                                    <i class="bi {{ $polaridadeIcon }} fs-5" data-bs-toggle="tooltip" title="{{ $ind->dsc_polaridade ?? 'Positiva' }}"></i>
                                </td>
                                <td>
                                    @php
                                        $atingimento = $ind->calcularAtingimento();
                                        $corFarol = $ind->getCorFarol();
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        <div class="farol-dot me-2" style="background-color: {{ $corFarol ?: '#dee2e6' }}; shadow: 0 0 5px {{ $corFarol }}88;"></div>
                                        <span class="fw-bold fs-6">@brazil_percent($atingimento, 1)</span>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <li><h6 class="dropdown-header small text-uppercase">Lançamentos</h6></li>
                                            <li><a class="dropdown-item" href="{{ route('indicadores.detalhes', $ind->cod_indicador) }}" wire:navigate><i class="bi bi-eye me-2 text-primary"></i> Ficha Técnica</a></li>
                                            <li><a class="dropdown-item" href="{{ route('indicadores.evolucao', $ind->cod_indicador) }}" wire:navigate><i class="bi bi-graph-up-arrow me-2 text-success"></i> Lançar Evolução</a></li>
                                            <li><button class="dropdown-item" wire:click="abrirMetas('{{ $ind->cod_indicador }}')"><i class="bi bi-bullseye me-2 text-primary"></i> Gerenciar Metas</button></li>
                                            <li><button class="dropdown-item" wire:click="abrirLinhaBase('{{ $ind->cod_indicador }}')"><i class="bi bi-bar-chart-steps me-2 text-warning"></i> Linha de Base</button></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><h6 class="dropdown-header small text-uppercase">Configuração</h6></li>
                                            <li><button class="dropdown-item" wire:click="edit('{{ $ind->cod_indicador }}')"><i class="bi bi-pencil me-2"></i> Editar</button></li>
                                            <li><button class="dropdown-item text-danger" wire:click="confirmDelete('{{ $ind->cod_indicador }}')"><i class="bi bi-trash me-2"></i> Excluir</button></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-bar-chart fs-1 opacity-25 mb-3 d-block"></i>
                                    Nenhum indicador encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3">
                {{ $indicadores->links() }}
            </div>
        </div>
    @endif

    <!-- Modal Premium Criar/Editar XL -->
    @if($showModal)
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5); z-index: 1055;" wire:click.self="$set('showModal', false)">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    
                    {{-- Header Premium --}}
                    <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle-mini bg-white bg-opacity-25 text-white">
                                <i class="bi bi-{{ $indicadorId ? 'sliders' : 'plus-circle' }}"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">{{ $indicadorId ? 'Configurar Indicador' : 'Novo Indicador' }}</h5>
                                <p class="mb-0 small text-white-50">Definição de métricas e parâmetros de desempenho</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                    </div>

                    <form wire:submit.prevent="save">
                        <div class="modal-body p-4 bg-white">
                            <div class="row g-4">
                                
                                {{-- Coluna Principal: Identificação --}}
                                <div class="col-lg-7">
                                    <div class="card border-0 bg-light rounded-4 h-100">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Identificação e Conceito</h6>
                                            
                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between align-items-end mb-1">
                                                    <label class="form-label text-muted small text-uppercase fw-bold mb-0">Nome do Indicador <span class="text-danger">*</span></label>
                                                    <div wire:loading wire:target="form.nom_indicador" class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                </div>
                                                <input type="text" wire:model.live.debounce.2000ms="form.nom_indicador" class="form-control form-control-lg bg-white border-0 shadow-sm @error('form.nom_indicador') is-invalid @enderror" placeholder="Ex: Índice de Satisfação do Cidadão">
                                                @error('form.nom_indicador') <div class="invalid-feedback">{{ $message }}</div> @enderror

                                                @if($smartFeedback)
                                                    <div class="mt-3 p-3 rounded-4 bg-white border-start border-4 border-primary shadow-sm animate-fade-in position-relative">
                                                        <button type="button" class="btn-close small position-absolute top-0 end-0 m-2" style="font-size: 0.6rem;" wire:click="$set('smartFeedback', '')"></button>
                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                            <i class="bi bi-robot text-primary"></i>
                                                            <small class="text-primary fw-bold text-uppercase">Análise SMART:</small>
                                                        </div>
                                                        <small class="text-dark lh-sm d-block">{{ $smartFeedback }}</small>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Descrição / Conceito</label>
                                                <textarea wire:model="form.dsc_indicador" class="form-control bg-white border-0 shadow-sm @error('form.dsc_indicador') is-invalid @enderror" rows="3" placeholder="O que este indicador mede exatamente?"></textarea>
                                                @error('form.dsc_indicador') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Unidades Organizacionais Vinculadas <span class="text-danger">*</span></label>
                                                <div class="bg-white rounded-4 shadow-sm p-3 overflow-auto" style="max-height: 200px; border: 1px solid rgba(0,0,0,0.05);">
                                                    @foreach($organizacoesOptions as $org)
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" value="{{ $org['id'] }}" 
                                                                   wire:model="form.organizacoes_ids" id="org_{{ $org['id'] }}">
                                                            <label class="form-check-label small fw-medium text-dark" for="org_{{ $org['id'] }}">
                                                                {!! $org['label'] !!}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @error('form.organizacoes_ids') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                                                <small class="text-muted mt-2 d-block lh-sm"><i class="bi bi-info-circle me-1"></i>O indicador aparecerá no mapa estratégico de todas as unidades selecionadas.</small>
                                            </div>

                                            <div class="mb-0">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Observações Técnicas</label>
                                                <textarea wire:model="form.txt_observacao" class="form-control bg-white border-0 shadow-sm" rows="2" placeholder="Notas adicionais sobre a coleta ou restrições..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Coluna Lateral: Vínculo e Peso --}}
                                <div class="col-lg-5">
                                    <div class="card border-0 bg-light rounded-4 h-100">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Vínculo Estratégico</h6>
                                            
                                            <div class="mb-4">
                                                <label class="form-label text-muted small text-uppercase fw-bold d-block">Origem do Indicador</label>
                                                <div class="d-flex gap-2 p-1 bg-white rounded-pill shadow-sm">
                                                    <input type="radio" class="btn-check" wire:model.live="form.dsc_tipo" value="Objetivo" id="v_obj" autocomplete="off">
                                                    <label class="btn btn-outline-primary border-0 rounded-pill flex-grow-1 py-2 fw-bold" for="v_obj">
                                                        <i class="bi bi-bullseye me-1"></i> Objetivo
                                                    </label>

                                                    <input type="radio" class="btn-check" wire:model.live="form.dsc_tipo" value="Plano" id="v_plan" autocomplete="off">
                                                    <label class="btn btn-outline-info border-0 rounded-pill flex-grow-1 py-2 fw-bold" for="v_plan">
                                                        <i class="bi bi-list-task me-1"></i> Plano
                                                    </label>
                                                </div>
                                            </div>

                                            @if($form['dsc_tipo'] === 'Objetivo')
                                                <div class="mb-4 animate-fade-in">
                                                    <label class="form-label text-muted small text-uppercase fw-bold">Selecionar Objetivo <span class="text-danger">*</span></label>
                                                    <select wire:model="form.cod_objetivo" class="form-select bg-white border-0 shadow-sm fw-bold">
                                                        <option value="">Escolha o objetivo...</option>
                                                        @foreach($objetivosAgrupados as $perspectiva => $objs)
                                                            <optgroup label="Perspectiva: {{ $perspectiva }}">
                                                                @foreach($objs as $obj)
                                                                    <option value="{{ $obj['cod_objetivo'] }}">{{ $obj['nom_objetivo'] }}</option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>
                                                    @error('form.cod_objetivo') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                                                </div>
                                            @else
                                                <div class="mb-4 animate-fade-in">
                                                    <label class="form-label text-muted small text-uppercase fw-bold">Selecionar Plano <span class="text-danger">*</span></label>
                                                    <select wire:model="form.cod_plano_de_acao" class="form-select bg-white border-0 shadow-sm fw-bold">
                                                        <option value="">Escolha o plano...</option>
                                                        @foreach($planosAgrupados as $objetivo => $plns)
                                                            <optgroup label="Objetivo: {{ $objetivo }}">
                                                                @foreach($plns as $plano)
                                                                    <option value="{{ $plano['cod_plano_de_acao'] }}">{{ $plano['dsc_plano_de_acao'] }}</option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>
                                                    @error('form.cod_plano_de_acao') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                                                </div>

                                                {{-- Tipo de Cálculo: aparece apenas para Planos --}}
                                                <div class="mb-4 animate-fade-in">
                                                    <label class="form-label text-muted small text-uppercase fw-bold">
                                                        <i class="bi bi-calculator me-1"></i>Método de Cálculo
                                                    </label>
                                                    <div class="d-flex gap-2 p-1 bg-white rounded-3 shadow-sm">
                                                        <input type="radio" class="btn-check" wire:model.live="form.dsc_calculation_type" value="manual" id="calc_manual" autocomplete="off">
                                                        <label class="btn btn-outline-secondary border-0 rounded-3 flex-grow-1 py-2 small" for="calc_manual">
                                                            <i class="bi bi-pencil-square me-1"></i> Manual
                                                        </label>

                                                        <input type="radio" class="btn-check" wire:model.live="form.dsc_calculation_type" value="action_plan" id="calc_auto" autocomplete="off">
                                                        <label class="btn btn-outline-success border-0 rounded-3 flex-grow-1 py-2 small" for="calc_auto">
                                                            <i class="bi bi-lightning-charge me-1"></i> Automático
                                                        </label>
                                                    </div>
                                                    
                                                    {{-- Dica contextual --}}
                                                    <div class="mt-2 p-2 bg-white rounded border small text-muted lh-sm">
                                                        @if($form['dsc_calculation_type'] === 'action_plan')
                                                            <i class="bi bi-lightning-charge text-success me-1"></i>
                                                            <strong>Cálculo Automático:</strong> O progresso será calculado pela fórmula <code>Σ(Peso × Status)</code> das entregas do plano. 
                                                            <span class="text-success fw-bold">Não é necessário lançar evoluções manualmente.</span>
                                                        @else
                                                            <i class="bi bi-pencil-square text-secondary me-1"></i>
                                                            <strong>Medição Manual:</strong> Você deverá lançar os valores realizados mensalmente na tela de "Lançar Evolução".
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="form-label text-muted small text-uppercase fw-bold">Polaridade do Indicador <span class="text-danger">*</span></label>
                                                    <div class="input-group shadow-sm">
                                                        <span class="input-group-text bg-white border-0 text-primary" x-data="{ 
                                                            polaridade: @entangle('form.dsc_polaridade'),
                                                            getIcon() {
                                                                return {
                                                                    'Positiva': 'bi-arrow-up-circle-fill text-success',
                                                                    'Negativa': 'bi-arrow-down-circle-fill text-danger',
                                                                    'Estabilidade': 'bi-dash-circle-fill text-warning',
                                                                    'Não Aplicável': 'bi-info-circle-fill text-muted'
                                                                }[this.polaridade] || 'bi-question-circle';
                                                            }
                                                        }">
                                                            <i class="bi" :class="getIcon()"></i>
                                                        </span>
                                                        <select wire:model.live="form.dsc_polaridade" class="form-select bg-white border-0 fw-bold">
                                                            @foreach($polaridades as $val => $label)
                                                                <option value="{{ $val }}">{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mt-2 p-2 bg-white rounded border small text-muted lh-sm" x-data="{ polaridade: @entangle('form.dsc_polaridade') }">
                                                        <template x-if="polaridade == 'Positiva'">
                                                            <span><i class="bi bi-info-circle me-1"></i><strong>Quanto maior, melhor:</strong> Valores crescentes indicam melhoria no desempenho (Ex: Satisfação).</span>
                                                        </template>
                                                        <template x-if="polaridade == 'Negativa'">
                                                            <span><i class="bi bi-info-circle me-1"></i><strong>Quanto menor, melhor:</strong> Valores decrescentes indicam melhoria no desempenho (Ex: Defeitos).</span>
                                                        </template>
                                                        <template x-if="polaridade == 'Estabilidade'">
                                                            <span><i class="bi bi-info-circle me-1"></i><strong>Estabilidade:</strong> Busca-se um valor alvo. Desvios para cima ou baixo são ruins (Ex: Taxa Ocupação).</span>
                                                        </template>
                                                        <template x-if="polaridade == 'Não Aplicável'">
                                                            <span><i class="bi bi-info-circle me-1"></i><strong>Informativo:</strong> Sem julgamento de valor, serve apenas para registro (Ex: Nº Colaboradores).</span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Bloco Inferior: Metodologia --}}
                                <div class="col-12">
                                    <div class="card border-0 bg-light rounded-4">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Parâmetros de Medição</h6>
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label text-muted small text-uppercase fw-bold">Unidade de Medida <span class="text-danger">*</span></label>
                                                    <select wire:model="form.dsc_unidade_medida" class="form-select bg-white border-0 shadow-sm fw-bold">
                                                        @foreach($unidadesMedida as $unidade)
                                                            <option value="{{ $unidade }}">{{ $unidade }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label text-muted small text-uppercase fw-bold">Periodicidade</label>
                                                    <select wire:model="form.dsc_periodo_medicao" class="form-select bg-white border-0 shadow-sm fw-bold">
                                                        @foreach($periodosOptions as $per)
                                                            <option value="{{ $per }}">{{ $per }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label text-muted small text-uppercase fw-bold">É Acumulado?</label>
                                                    <select wire:model="form.bln_acumulado" class="form-select bg-white border-0 shadow-sm fw-bold">
                                                        <option value="Sim">Sim (Soma mensal)</option>
                                                        <option value="Não">Não (Valor pontual)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label text-muted small text-uppercase fw-bold">Meta Global</label>
                                                    <input type="text" wire:model="form.dsc_meta" class="form-control bg-white border-0 shadow-sm fw-bold" placeholder="Ex: Atingir 90%">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small text-uppercase fw-bold">Fórmula de Cálculo</label>
                                                    <textarea wire:model="form.dsc_formula" class="form-control bg-white border-0 shadow-sm" rows="2" placeholder="Ex: (Realizado / Previsto) * 100"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small text-uppercase fw-bold">Fonte dos Dados</label>
                                                    <textarea wire:model="form.dsc_fonte" class="form-control bg-white border-0 shadow-sm" rows="2" placeholder="Ex: Sistema Financeiro ERP..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 bg-white rounded-bottom-4 shadow-top-sm">
                            <button type="button" class="btn btn-light px-4 rounded-pill fw-bold text-muted" wire:click="$set('showModal', false)">Cancelar</button>
                            <button type="submit" class="btn btn-primary gradient-theme-btn px-5 rounded-pill shadow-sm hover-scale">
                                <i class="bi bi-check-lg me-2"></i>Salvar Indicador
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Success Modal Premium --}}
    @if($showSuccessModal)
    <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.6); z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-body p-5 text-center bg-white">
                    <div class="mb-4">
                        <div class="icon-circle mx-auto bg-primary text-white shadow-lg scale-in-center" style="width: 80px; height: 80px; font-size: 2.5rem; background: linear-gradient(135deg, #1B408E 0%, #4361EE 100%) !important;">
                            <i class="bi bi-check-lg"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-3">KPI Registrado!</h3>
                    <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.6;">
                        <strong class="text-primary d-block mb-2">"{{ $createdIndicadorName }}"</strong>
                        {{ $successMessage }}
                    </p>
                    <button wire:click="closeSuccessModal" class="btn btn-primary gradient-theme-btn px-5 rounded-pill shadow hover-scale">
                        <i class="bi bi-check2-circle me-2"></i>Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Error Modal Premium --}}
    @if($showErrorModal)
    <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.6); z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-body p-5 text-center bg-white">
                    <div class="mb-4">
                        <div class="icon-circle mx-auto bg-danger text-white shadow-lg scale-in-center" style="width: 80px; height: 80px; font-size: 2.5rem; background: linear-gradient(135deg, #e63946 0%, #d62828 100%) !important;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-3">Falha na Operação</h3>
                    <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.6;">
                        {{ $errorMessage }}
                    </p>
                    <button wire:click="closeErrorModal" class="btn btn-danger px-5 rounded-pill shadow hover-scale">
                        <i class="bi bi-arrow-clockwise me-2"></i>Tentar Novamente
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        .scale-in-center { animation: scale-in-center 0.5s cubic-bezier(0.250, 0.460, 0.450, 0.940) both; }
        @keyframes scale-in-center { 0% { transform: scale(0); opacity: 1; } 100% { transform: scale(1); opacity: 1; } }
    </style>

    {{-- Modal de Exclusão --}}
    <x-confirmation-modal wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="icon-circle-mini modal-icon-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold text-dark">{{ __('Excluir Indicador (KPI)') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Esta ação é irreversível') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation text-start">
                <p class="mb-2 text-dark">
                    {{ __('Tem certeza que deseja excluir este indicador de desempenho?') }}
                </p>
                <div class="alert alert-warning bg-warning-subtle border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção:</strong> Todos os lançamentos de evolução e metas associadas serão perdidos permanentemente.
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDeleteModal', false)" wire:loading.attr="disabled" class="btn-modern">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-danger-button wire:click="delete" wire:loading.attr="disabled" class="btn-delete-modern ms-2">
                <span wire:loading.remove wire:target="delete">
                    <i class="bi bi-trash me-1"></i>{{ __('Excluir Agora') }}
                </span>
                <span wire:loading wire:target="delete">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                    {{ __('Excluindo...') }}
                </span>
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    <!-- Modal Metas -->
    <div class="modal fade @if($showMetasModal) show @endif" tabindex="-1" style="@if($showMetasModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header gradient-theme text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-bullseye me-2"></i>Metas Anuais</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showMetasModal', false)"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-4">Indicador: <strong>{{ $indicadorSelecionado?->nom_indicador }}</strong></p>
                    
                    <form wire:submit.prevent="salvarMeta" class="row g-2 mb-4 p-3 bg-light rounded-3 border">
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted">Ano</label>
                            <input type="number" wire:model="metaAno" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-5">
                            <label class="small fw-bold text-muted">Meta ({{ $indicadorSelecionado?->dsc_unidade_medida }})</label>
                            <input type="number" step="0.01" wire:model="metaValor" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary w-100">Adicionar</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover border">
                            <thead class="table-light">
                                <tr>
                                    <th>Ano</th>
                                    <th>Meta</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($indicadorSelecionado?->metasPorAno ?? [] as $meta)
                                    <tr>
                                        <td>{{ $meta->num_ano }}</td>
                                        <td>@brazil_number($meta->meta, 2)</td>
                                        <td class="text-end">
                                            <button wire:click="excluirMeta('{{ $meta->cod_meta_por_ano }}')" class="btn btn-sm text-danger p-0"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-3 text-muted small">Sem metas cadastradas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Linha Base -->
    <div class="modal fade @if($showLinhaBaseModal) show @endif" tabindex="-1" style="@if($showLinhaBaseModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header gradient-theme text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-bar-chart-steps me-2"></i>Linha de Base</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showLinhaBaseModal', false)"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-4">Indicador: <strong>{{ $indicadorSelecionado?->nom_indicador }}</strong></p>
                    
                    <form wire:submit.prevent="salvarLinhaBase" class="row g-2 mb-4 p-3 bg-light rounded-3 border">
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted">Ano</label>
                            <input type="number" wire:model="linhaBaseAno" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-5">
                            <label class="small fw-bold text-muted">Valor Base</label>
                            <input type="number" step="0.01" wire:model="linhaBaseValor" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary w-100">Salvar</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover border">
                            <thead class="table-light">
                                <tr>
                                    <th>Ano</th>
                                    <th>Valor</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($indicadorSelecionado?->linhaBase ?? [] as $lb)
                                    <tr>
                                        <td>{{ $lb->num_ano }}</td>
                                        <td>@brazil_number($lb->num_linha_base, 2)</td>
                                        <td class="text-end">
                                            <button wire:click="excluirLinhaBase('{{ $lb->cod_linha_base }}')" class="btn btn-sm text-danger p-0"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-3 text-muted small">Sem linha de base.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .farol-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: inline-block;
            border: 1px solid rgba(0,0,0,0.1);
        }
        .small-vinculo { font-size: 0.75rem; }
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</div>
