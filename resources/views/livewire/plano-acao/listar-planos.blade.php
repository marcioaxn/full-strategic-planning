<div>
    {{-- Cabeçalho Interno --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Planos de Ação</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="h3 fw-bold mb-0">Planos de Ação</h1>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($organizacaoId)
                <button wire:click.prevent="create" wire:loading.attr="disabled" class="btn btn-primary gradient-theme-btn shadow-sm">
                    <span wire:loading.remove wire:target="create">
                        <i class="bi bi-plus-lg me-2"></i>Novo Plano
                    </span>
                    <span wire:loading wire:target="create">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>Carregando...
                    </span>
                </button>
            @endif
        </div>
    </div>

    {{-- Seção Educativa: O que são Planos de Ação --}}
    <div class="card border-0 shadow-sm mb-4 educational-card-gradient" x-data="{ expanded: false }">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle bg-white bg-opacity-25">
                        <i class="bi bi-book-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">
                            <i class="bi bi-mortarboard me-2"></i>{{ __('O que são Planos de Ação?') }}
                        </h5>
                        <p class="mb-0 text-white-50 small">
                            {{ __('Aprenda a transformar objetivos estratégicos em ações concretas') }}
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
                        <i class="bi bi-info-circle me-2"></i>{{ __('O que é um Plano de Ação?') }}
                    </h6>
                    <p class="text-muted mb-3">
                        Um <strong>Plano de Ação</strong> é o conjunto de atividades organizadas para alcançar um <strong>objetivo estratégico</strong>.
                        Ele detalha <strong>o quê</strong> será feito, <strong>quem</strong> será responsável, <strong>quando</strong> será executado,
                        <strong>onde</strong> acontecerá, <strong>por quê</strong> é necessário, <strong>como</strong> será feito e <strong>quanto</strong> custará.
                    </p>
                    <p class="text-muted mb-0">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        <strong>Por que é importante?</strong> Sem planos de ação, os objetivos estratégicos ficam apenas no papel.
                        Os planos garantem execução prática, alocação de recursos e acompanhamento de resultados.
                    </p>
                </div>

                {{-- Metodologia 5W2H --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-grid-3x3 me-2"></i>{{ __('Metodologia 5W2H') }}
                    </h6>
                    <p class="small text-muted mb-3">
                        A ferramenta <strong>5W2H</strong> estrutura o plano de ação respondendo 7 perguntas essenciais:
                    </p>

                    <div class="row g-3">
                        {{-- What --}}
                        <div class="col-md-6">
                            <div class="card border-2 border-primary h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="icon-circle-mini bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-question-circle"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-primary">What - O Quê?</h6>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        Qual ação será executada? Descreva de forma clara e específica.
                                        <br><strong>Ex:</strong> "Implementar sistema de gestão de processos"
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Who --}}
                        <div class="col-md-6">
                            <div class="card border-2 border-success h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="icon-circle-mini bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-success">Who - Quem?</h6>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        Quem será o responsável pela execução?
                                        <br><strong>Ex:</strong> "Coordenação de TI + Consultor externo"
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- When --}}
                        <div class="col-md-6">
                            <div class="card border-2 border-warning h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="icon-circle-mini bg-warning bg-opacity-10 text-warning">
                                            <i class="bi bi-calendar"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-warning">When - Quando?</h6>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        Qual o prazo de início e término?
                                        <br><strong>Ex:</strong> "Início: 01/03/2025 - Término: 30/08/2025"
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Where --}}
                        <div class="col-md-6">
                            <div class="card border-2 border-info h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="icon-circle-mini bg-info bg-opacity-10 text-info">
                                            <i class="bi bi-geo-alt"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-info">Where - Onde?</h6>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        Onde será executado? Local, setor ou unidade.
                                        <br><strong>Ex:</strong> "Sede Central + 5 unidades regionais"
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Why --}}
                        <div class="col-md-6">
                            <div class="card border-2 border-danger h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="icon-circle-mini bg-danger bg-opacity-10 text-danger">
                                            <i class="bi bi-lightbulb"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-danger">Why - Por Quê?</h6>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        Qual a justificativa? Por que é necessário?
                                        <br><strong>Ex:</strong> "Reduzir tempo de tramitação de processos em 40%"
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- How --}}
                        <div class="col-md-6">
                            <div class="card border-2 border-primary h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="icon-circle-mini bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-tools"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-primary">How - Como?</h6>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        Como será executado? Método, etapas, estratégia.
                                        <br><strong>Ex:</strong> "Licitação → Contratação → Treinamento → Implantação"
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- How Much --}}
                        <div class="col-12">
                            <div class="card border-2 border-success">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="icon-circle-mini bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-currency-dollar"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-success">How Much - Quanto?</h6>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        Qual o custo estimado? Orçamento previsto.
                                        <br><strong>Ex:</strong> "R$ 150.000,00 (software + consultoria + treinamento)"
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Exemplo Completo --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-star me-2"></i>{{ __('Exemplo de Plano de Ação Completo') }}
                    </h6>

                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="icon-circle-mini bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-clipboard-check"></i>
                                </div>
                                <h6 class="fw-bold mb-0">Plano: Modernizar Atendimento ao Cidadão</h6>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-borderless mb-0">
                                    <tbody class="small">
                                        <tr>
                                            <td class="fw-bold text-primary" style="width: 120px;">What</td>
                                            <td>Implementar sistema de atendimento multicanal (presencial, telefone, WhatsApp, portal web)</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-success">Who</td>
                                            <td>Diretoria de Atendimento + TI (coordenador: José Silva)</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-warning">When</td>
                                            <td>Início: 15/02/2025 | Término: 30/09/2025 (7 meses)</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-info">Where</td>
                                            <td>Sede Central + 3 postos de atendimento regionais</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-danger">Why</td>
                                            <td>Reduzir tempo de espera de 45min para 15min e aumentar satisfação dos cidadãos para 85%</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-primary">How</td>
                                            <td>
                                                1. Mapear processos atuais<br>
                                                2. Licitar sistema integrado<br>
                                                3. Treinar equipe<br>
                                                4. Pilotar em 1 unidade<br>
                                                5. Expandir para todas as unidades
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-success">How Much</td>
                                            <td>R$ 280.000,00 (software R$ 150k + treinamento R$ 30k + equipamentos R$ 100k)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dicas Profissionais --}}
                <div>
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-trophy me-2"></i>{{ __('Dicas para Planos de Ação Eficazes') }}
                    </h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Seja específico</p>
                                    <p class="x-small text-muted mb-0">Evite descrições genéricas. Use verbos de ação: "Implementar", "Capacitar", "Reduzir"</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Defina responsáveis claros</p>
                                    <p class="x-small text-muted mb-0">Cada plano deve ter um gestor identificado nominalmente, não apenas "setor X"</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Estabeleça marcos intermediários</p>
                                    <p class="x-small text-muted mb-0">Divida planos longos em entregas menores para facilitar monitoramento</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Estime orçamento realisticamente</p>
                                    <p class="x-small text-muted mb-0">Preveja custos diretos e indiretos. Adicione margem de contingência (10-15%)</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Monitore progresso regularmente</p>
                                    <p class="x-small text-muted mb-0">Acompanhe status semanalmente ou quinzenalmente. Identifique desvios cedo para agir a tempo</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-{{ session('style') }} alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$organizacaoId && !$filtroObjetivo)
        <div class="alert alert-warning shadow-sm border-0 d-flex align-items-center p-4" role="alert">
            <i class="bi bi-building-exclamation fs-2 me-4"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Selecione uma Organizacao</h5>
                <p class="mb-0">Selecione uma organizacao no menu superior para gerenciar os planos de acao.</p>
            </div>
        </div>
    @else
        @if($filtroObjetivo)
            @php
                $objetivoFiltrado = \App\Models\StrategicPlanning\Objetivo::with(['perspectiva.pei', 'indicadores.evolucoes', 'indicadores.metasPorAno', 'planosAcao.entregas'])->find($filtroObjetivo);
            @endphp

            {{-- Contexto Completo do Objetivo --}}
            @include('livewire.partials.objetivo-contexto', ['objetivo' => $objetivoFiltrado])

            {{-- Cards de Resumo e Grafico dos Planos --}}
            @if($objetivoFiltrado && $objetivoFiltrado->planosAcao->count() > 0)
                @php
                    $planosDoObjetivo = $objetivoFiltrado->planosAcao;
                    $totalPlanos = $planosDoObjetivo->count();
                    $planosConcluidos = $planosDoObjetivo->where('bln_status', 'Concluido')->count();
                    $planosEmAndamento = $planosDoObjetivo->where('bln_status', 'Em Andamento')->count();
                    $planosNaoIniciados = $planosDoObjetivo->where('bln_status', 'Nao Iniciado')->count();
                    $planosAtrasados = $planosDoObjetivo->filter(fn($p) => $p->dte_fim < now() && $p->bln_status !== 'Concluido')->count();

                    $orcamentoTotal = $planosDoObjetivo->sum('vlr_orcamento_previsto');
                    $totalEntregas = 0;
                    $entregasConcluidas = 0;
                    foreach ($planosDoObjetivo as $p) {
                        $totalEntregas += $p->entregas->count();
                        $entregasConcluidas += $p->entregas->where('bln_status', 'Concluida')->count();
                    }
                    $percentualEntregas = $totalEntregas > 0 ? round(($entregasConcluidas / $totalEntregas) * 100, 1) : 0;
                @endphp

                <div class="row g-3 mb-4">
                    <!-- Cards de Status -->
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-pie-chart text-info me-2"></i>Status dos Planos
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <canvas id="chartStatusPlanos" height="150"></canvas>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="d-flex align-items-center p-2 bg-success bg-opacity-10 rounded">
                                                    <div class="rounded-circle bg-success me-2" style="width:12px;height:12px;"></div>
                                                    <div>
                                                        <div class="fw-bold">{{ $planosConcluidos }}</div>
                                                        <small class="text-muted">Concluidos</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="d-flex align-items-center p-2 bg-primary bg-opacity-10 rounded">
                                                    <div class="rounded-circle bg-primary me-2" style="width:12px;height:12px;"></div>
                                                    <div>
                                                        <div class="fw-bold">{{ $planosEmAndamento }}</div>
                                                        <small class="text-muted">Em Andamento</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="d-flex align-items-center p-2 bg-secondary bg-opacity-10 rounded">
                                                    <div class="rounded-circle bg-secondary me-2" style="width:12px;height:12px;"></div>
                                                    <div>
                                                        <div class="fw-bold">{{ $planosNaoIniciados }}</div>
                                                        <small class="text-muted">Nao Iniciados</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="d-flex align-items-center p-2 bg-danger bg-opacity-10 rounded">
                                                    <div class="rounded-circle bg-danger me-2" style="width:12px;height:12px;"></div>
                                                    <div>
                                                        <div class="fw-bold">{{ $planosAtrasados }}</div>
                                                        <small class="text-muted">Atrasados</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card de Resumo Financeiro e Entregas -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-cash-stack text-warning me-2"></i>Resumo
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted text-uppercase">Orcamento Total Previsto</small>
                                    <h4 class="fw-bold text-success mb-0">
                                        R$ {{ number_format($orcamentoTotal, 2, ',', '.') }}
                                    </h4>
                                </div>
                                <hr>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted">Entregas Concluidas</small>
                                        <small class="fw-bold">{{ $entregasConcluidas }}/{{ $totalEntregas }}</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-info" style="width: {{ $percentualEntregas }}%;"></div>
                                    </div>
                                    <small class="text-muted">{{ $percentualEntregas }}% concluido</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    (function() {
                        function initChartStatusPlanos() {
                            const ctx = document.getElementById('chartStatusPlanos');
                            if (ctx && typeof Chart !== 'undefined') {
                                // Destruir grafico existente se houver
                                if (ctx.chartInstance) {
                                    ctx.chartInstance.destroy();
                                }
                                ctx.chartInstance = new Chart(ctx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: ['Concluidos', 'Em Andamento', 'Nao Iniciados', 'Atrasados'],
                                        datasets: [{
                                            data: [{{ $planosConcluidos }}, {{ $planosEmAndamento }}, {{ $planosNaoIniciados }}, {{ $planosAtrasados }}],
                                            backgroundColor: ['#198754', '#0d6efd', '#6c757d', '#dc3545'],
                                            borderWidth: 0
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: { display: false }
                                        },
                                        cutout: '65%'
                                    }
                                });
                            }
                        }
                        // Inicializar na carga inicial e na navegacao Livewire
                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', initChartStatusPlanos);
                        } else {
                            initChartStatusPlanos();
                        }
                        document.addEventListener('livewire:navigated', initChartStatusPlanos);
                    })();
                </script>
            @endif
        @endif
        <!-- Filtros -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 bg-light rounded-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar planos...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="filtroStatus" class="form-select">
                            <option value="">Todos Status</option>
                            @foreach($statusOptions as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="filtroTipo" class="form-select">
                            <option value="">Todos Tipos</option>
                            @foreach($tiposExecucao as $tipo)
                                <option value="{{ $tipo->cod_tipo_execucao }}">{{ $tipo->dsc_tipo_execucao }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="filtroAno" class="form-select">
                            <option value="">Ano</option>
                            @for($i = now()->year - 2; $i <= now()->year + 5; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <div wire:loading class="spinner-border text-primary spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Legenda de Status (Dinâmica via Model/Partial) --}}
        @include('livewire.partials.legenda-status-planos')

        <!-- Lista de Planos -->
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Descrição do Plano</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Período</th>
                            <th>Orçamento</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($planos as $plano)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark mb-1">{{ Str::limit($plano->dsc_plano_de_acao, 60) }}</div>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-bullseye me-1"></i>
                                        {{ Str::limit($plano->objetivo->nom_objetivo ?? 'Sem Objetivo', 50) }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $plano->tipoExecucao->dsc_tipo_execucao ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $corStatus = $plano->getSatisfacaoColor();
                                        $textClass = $plano->getSatisfacaoTextClass();
                                        $statusLabel = $plano->isAtrasado() ? 'Atrasado' : $plano->bln_status;
                                    @endphp
                                    <span class="badge {{ $textClass }} rounded-pill border shadow-sm px-3 py-1" style="background-color: {{ $corStatus }};">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td>
                                    <small class="d-block text-nowrap">
                                        {{ $plano->dte_inicio?->format('d/m/Y') }} a
                                    </small>
                                    <small class="d-block text-nowrap fw-bold {{ $plano->isAtrasado() ? 'text-danger' : 'text-dark' }}">
                                        {{ $plano->dte_fim?->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td>
                                    <small class="fw-mono text-muted">
                                        R$ {{ number_format($plano->vlr_orcamento_previsto, 2, ',', '.') }}
                                    </small>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <li>
                                                <h6 class="dropdown-header small text-uppercase">Gestão</h6>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('planos.detalhes', $plano->cod_plano_de_acao) }}">
                                                    <i class="bi bi-eye me-2 text-primary"></i> Detalhes
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('planos.entregas', $plano->cod_plano_de_acao) }}">
                                                    <i class="bi bi-list-check me-2 text-info"></i> Entregas
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('planos.responsaveis', $plano->cod_plano_de_acao) }}">
                                                    <i class="bi bi-people me-2 text-warning"></i> Responsáveis
                                                </a>
                                            </li>
                                            <li>
                                                <button class="dropdown-item" wire:click="edit('{{ $plano->cod_plano_de_acao }}')">
                                                    <i class="bi bi-pencil me-2 text-secondary"></i> Editar
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger" wire:click="confirmDelete('{{ $plano->cod_plano_de_acao }}')">
                                                    <i class="bi bi-trash me-2"></i> Excluir
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="bi bi-clipboard-x fs-1 text-muted opacity-25"></i>
                                    </div>
                                    <h5 class="text-muted">Nenhum plano encontrado.</h5>
                                    <p class="text-muted small">Tente ajustar os filtros ou crie um novo plano.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3">
                {{ $planos->links() }}
            </div>
        </div>
    @endif

    <!-- Modal Criar/Editar -->
    <div class="modal fade @if($showModal) show @endif" tabindex="-1" role="dialog" wire:key="modal-plano-acao"
         style="@if($showModal) display: block; background: rgba(0,0,0,0.5); z-index: 1055; @else display: none; @endif">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header gradient-theme text-white border-0">
                    <h5 class="modal-title fw-bold">
                        {{ $planoId ? 'Editar Plano de Ação' : 'Novo Plano de Ação' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <!-- Linha 1: Descrição -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small text-uppercase fw-bold mb-0">Descrição do Plano</label>
                                @if($aiEnabled)
                                    <button type="button" wire:click="pedirAjudaIA" wire:loading.attr="disabled" class="btn btn-xs btn-outline-magic py-0" style="font-size: 0.65rem;">
                                        <i class="bi bi-robot me-1"></i> Sugerir com IA
                                    </button>
                                @endif
                            </div>
                            
                            @if($aiSuggestion)
                                <div class="alert alert-magic bg-primary bg-opacity-10 border-0 p-2 mb-3 animate-fade-in">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="fw-bold text-primary"><i class="bi bi-robot me-1"></i>Sugestões IA:</small>
                                        <button type="button" class="btn-close" style="font-size: 0.5rem;" wire:click="$set('aiSuggestion', '')"></button>
                                    </div>
                                    <div class="list-group list-group-flush rounded border">
                                        @foreach($aiSuggestion as $sug)
                                            <button type="button" wire:click="aplicarSugestao('{{ $sug['nome'] }}')" class="list-group-item list-group-item-action py-1 px-2 x-small">
                                                <div class="fw-bold">{{ $sug['nome'] }}</div>
                                                <div class="text-muted" style="font-size: 0.65rem;">{{ $sug['justificativa'] }}</div>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <textarea wire:model="dsc_plano_de_acao" class="form-control @error('dsc_plano_de_acao') is-invalid @enderror" rows="2" placeholder="Descreva o plano de ação..."></textarea>
                            @error('dsc_plano_de_acao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Linha 2: Objetivo e Tipo -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label text-muted small text-uppercase fw-bold">Objetivo</label>
                                <select wire:model="cod_objetivo" class="form-select @error('cod_objetivo') is-invalid @enderror">
                                    <option value="">Selecione...</option>
                                    @foreach($objetivos as $obj)
                                        <option value="{{ $obj->cod_objetivo }}">
                                            {{ $obj->perspectiva->dsc_perspectiva }} > {{ Str::limit($obj->nom_objetivo, 80) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cod_objetivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Tipo de Execução</label>
                                <select wire:model="cod_tipo_execucao" class="form-select @error('cod_tipo_execucao') is-invalid @enderror">
                                    <option value="">Selecione...</option>
                                    @foreach($tiposExecucao as $tipo)
                                        <option value="{{ $tipo->cod_tipo_execucao }}">{{ $tipo->dsc_tipo_execucao }}</option>
                                    @endforeach
                                </select>
                                @error('cod_tipo_execucao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Linha 3: Datas e Status -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Data Início</label>
                                <input type="date" wire:model.live="dte_inicio" class="form-control @error('dte_inicio') is-invalid @enderror">
                                @error('dte_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Data Fim</label>
                                <input type="date" wire:model.live="dte_fim" class="form-control @error('dte_fim') is-invalid @enderror">
                                @error('dte_fim') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Status</label>
                                <select wire:model="bln_status" class="form-select @error('bln_status') is-invalid @enderror">
                                    @foreach($statusOptions as $st)
                                        <option value="{{ $st }}">{{ $st }}</option>
                                    @endforeach
                                </select>
                                @error('bln_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Alertas Educativos de Data --}}
                            @if($dte_inicio && $dte_fim)
                                <div class="col-12 mt-1">
                                    @php
                                        $start = \Carbon\Carbon::parse($dte_inicio);
                                        $end = \Carbon\Carbon::parse($dte_fim);
                                        $diff = $start->diffInDays($end, false);
                                    @endphp

                                    @if($diff < 0)
                                        <div class="alert alert-danger py-1 px-2 small mb-0">
                                            <i class="bi bi-exclamation-octagon me-1"></i>
                                            A data de término deve ser posterior à data de início.
                                        </div>
                                    @elseif($diff < 30)
                                        <div class="alert alert-warning py-1 px-2 small mb-0">
                                            <i class="bi bi-info-circle me-1"></i>
                                            <strong>Dica:</strong> Um prazo de apenas {{ $diff }} dias pode ser curto para um plano estratégico. Considere se não é apenas uma "Entrega".
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Linha 4: Orçamento e Códigos -->
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Orçamento Previsto (R$)</label>
                                <input type="number" step="0.01" wire:model="vlr_orcamento_previsto" class="form-control @error('vlr_orcamento_previsto') is-invalid @enderror">
                                @error('vlr_orcamento_previsto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Cód. PPA</label>
                                <input type="text" wire:model="cod_ppa" class="form-control @error('cod_ppa') is-invalid @enderror" placeholder="Opcional">
                                @error('cod_ppa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Cód. LOA</label>
                                <input type="text" wire:model="cod_loa" class="form-control @error('cod_loa') is-invalid @enderror" placeholder="Opcional">
                                @error('cod_loa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" wire:click="$set('showModal', false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-4">Salvar Plano</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal de Exclusão --}}
    <x-confirmation-modal wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="icon-circle-mini modal-icon-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold text-dark">{{ __('Excluir Plano de Ação') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Esta ação é irreversível') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation text-start">
                <p class="mb-2 text-dark">
                    {{ __('Tem certeza que deseja excluir este plano de ação?') }}
                </p>
                <div class="alert alert-warning bg-warning-subtle border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção:</strong> Todas as entregas, comentários e históricos vinculados a este plano serão removidos permanentemente.
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
</div>
