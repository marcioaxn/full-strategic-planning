<div>
    {{-- Page Header --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="icon-circle-header gradient-theme-icon">
                    <i class="bi bi-bullseye"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ __('Objetivos BSC') }}</h1>
                <span class="badge-modern badge-count">
                    {{ $perspectivas->sum(fn($p) => $p->objetivos->count()) }}
                </span>
            </div>
            <p class="text-muted mb-0">
                @if($peiAtivo)
                    {{ __('Objetivos definidos para o ciclo:') }} <strong>{{ $peiAtivo->dsc_pei }}</strong>
                @else
                    <span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i> {{ __('Nenhum Ciclo PEI Ativo encontrado.') }}</span>
                @endif
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($peiAtivo)
                <button type="button" class="btn btn-primary gradient-theme-btn px-4 shadow-sm" wire:click="create">
                    <i class="bi bi-plus-lg me-1"></i> {{ __('Novo Objetivo') }}
                </button>
            @endif
        </div>
    </div>

    {{-- Seção Educativa: O que são Objetivos Estratégicos BSC --}}
    <div class="card border-0 shadow-sm mb-4 educational-card-gradient" x-data="{ expanded: false }">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle bg-white bg-opacity-25">
                        <i class="bi bi-book-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">
                            <i class="bi bi-mortarboard me-2"></i>{{ __('O que são Objetivos BSC?') }}
                        </h5>
                        <p class="mb-0 text-white small">
                            {{ __('Aprenda sobre o Balanced Scorecard e as 4 perspectivas estratégicas') }}
                        </p>
                    </div>
                </div>
                <button
                    @click="expanded = !expanded"
                    class="btn btn-sm btn-light rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 36px; height: 36px;"
                    :aria-expanded="expanded"
                    aria-label="{{ __('Expandir/Recolher') }}"
                >
                    <i class="bi" :class="expanded ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
            </div>
        </div>

        <div x-show="expanded"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             style="display: none;"
        >
            <div class="card-body p-4 bg-white border-top">
                <div class="row g-4">
                    {{-- Introdução --}}
                    <div class="col-12">
                        <div class="alert alert-info border-0 d-flex align-items-start gap-3 mb-0">
                            <div class="icon-circle-mini bg-info bg-opacity-10 text-info flex-shrink-0">
                                <i class="bi bi-lightbulb-fill"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2">{{ __('O que é Balanced Scorecard (BSC)?') }}</h6>
                                <p class="mb-2 small">
                                    {{ __('O BSC é uma metodologia de gestão estratégica que organiza objetivos em 4 perspectivas interligadas, criando um mapa de causa e efeito. Ao invés de focar apenas em resultados financeiros, o BSC equilibra a visão estratégica considerando:') }}
                                </p>
                                <ul class="small mb-0">
                                    <li><strong>Como aprendemos e crescemos?</strong> {{ __('(Base - Aprendizado e Crescimento)') }}</li>
                                    <li><strong>Como otimizamos processos?</strong> {{ __('(Processos Internos)') }}</li>
                                    <li><strong>Como entregamos valor?</strong> {{ __('(Clientes/Sociedade)') }}</li>
                                    <li><strong>Qual o resultado final?</strong> {{ __('(Financeira/Resultados)') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Diagrama Visual das 4 Perspectivas --}}
                    <div class="col-12">
                        <div class="card border-2 border-primary">
                            <div class="card-header bg-primary bg-opacity-10">
                                <h6 class="fw-bold mb-0 text-dark text-center">
                                    <i class="bi bi-diagram-3 me-2 text-primary"></i>{{ __('As 4 Perspectivas do BSC (Cadeia de Causa e Efeito)') }}
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    {{-- Perspectiva 1: Financeira/Resultados --}}
                                    <div class="col-12">
                                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 border border-success bg-success">
                                            <div class="icon-circle-mini bg-white bg-opacity-20 text-white flex-shrink-0">
                                                <i class="bi bi-graph-up-arrow text-dark"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="fw-bold mb-0 text-white">{{ __('1. Perspectiva Financeira / Resultados') }}</h6>
                                                    <span class="badge bg-white text-success fw-bold">{{ __('TOPO') }}</span>
                                                </div>
                                                <p class="small text-white mb-0 mt-1 opacity-90">{{ __('Resultado final - Sucesso econômico ou impacto social') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Seta Descendente --}}
                                    <div class="col-12 text-center">
                                        <i class="bi bi-arrow-down-circle-fill text-dark fs-4"></i>
                                        <p class="small text-dark mb-0">{{ __('é resultado de') }}</p>
                                    </div>

                                    {{-- Perspectiva 2: Clientes/Sociedade --}}
                                    <div class="col-12">
                                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 border border-info bg-info">
                                            <div class="icon-circle-mini bg-white bg-opacity-20 text-dark flex-shrink-0">
                                                <i class="bi bi-people-fill"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold mb-0 text-dark">{{ __('2. Perspectiva de Clientes / Sociedade') }}</h6>
                                                <p class="small text-dark mb-0 mt-1 opacity-90">{{ __('Valor entregue ao público-alvo - Satisfação e impacto') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Seta Descendente --}}
                                    <div class="col-12 text-center">
                                        <i class="bi bi-arrow-down-circle-fill text-dark fs-4"></i>
                                        <p class="small text-dark mb-0">{{ __('é resultado de') }}</p>
                                    </div>

                                    {{-- Perspectiva 3: Processos Internos --}}
                                    <div class="col-12">
                                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 border border-warning bg-warning">
                                            <div class="icon-circle-mini bg-white bg-opacity-20 text-dark flex-shrink-0">
                                                <i class="bi bi-gear-fill"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold mb-0 text-dark">{{ __('3. Perspectiva de Processos Internos') }}</h6>
                                                <p class="small text-dark mb-0 mt-1 opacity-90">{{ __('Eficiência operacional - Como fazemos nosso trabalho') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Seta Descendente --}}
                                    <div class="col-12 text-center">
                                        <i class="bi bi-arrow-down-circle-fill text-dark fs-4"></i>
                                        <p class="small text-dark mb-0">{{ __('é resultado de') }}</p>
                                    </div>

                                    {{-- Perspectiva 4: Aprendizado e Crescimento --}}
                                    <div class="col-12">
                                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 border border-primary bg-primary">
                                            <div class="icon-circle-mini bg-white bg-opacity-20 text-white flex-shrink-0">
                                                <i class="bi bi-mortarboard-fill text-dark"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="fw-bold mb-0 text-white">{{ __('4. Perspectiva de Aprendizado e Crescimento') }}</h6>
                                                    <span class="badge bg-white text-primary fw-bold">{{ __('BASE') }}</span>
                                                </div>
                                                <p class="small text-white mb-0 mt-1 opacity-90">{{ __('Capacitação e inovação - Pessoas, tecnologia e cultura') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Cards Detalhados: Exemplos por Perspectiva --}}
                    <div class="col-md-6">
                        <div class="card h-100 border border-success border-opacity-25 shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="icon-circle bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-graph-up-arrow fs-5"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-dark">{{ __('Financeira / Resultados') }}</h6>
                                </div>
                                <p class="small text-dark mb-3">
                                    <strong>{{ __('Definição:') }}</strong> {{ __('Objetivos relacionados ao sucesso econômico ou impacto social mensurado em recursos.') }}
                                </p>
                                <div class="bg-light p-3 rounded-3 border mb-3">
                                    <p class="small mb-2 fw-semibold text-dark">{{ __('Exemplos:') }}</p>
                                    <ul class="small mb-0 ps-3 text-dark">
                                        <li>{{ __('Aumentar a arrecadação em 15% até 2025') }}</li>
                                        <li>{{ __('Reduzir custos operacionais em 10%') }}</li>
                                        <li>{{ __('Captar R$ 500K em editais') }}</li>
                                        <li>{{ __('Atingir sustentabilidade financeira') }}</li>
                                    </ul>
                                </div>
                                <div class="alert alert-success alert-sm py-2 px-3 mb-0">
                                    <p class="small mb-1 fw-semibold text-dark">{{ __('Exemplo completo:') }}</p>
                                    <p class="small mb-0 fst-italic text-dark">
                                        {{ __('"Aumentar a receita própria da instituição de R$ 2M para R$ 2,5M até dezembro de 2025"') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 border border-info border-opacity-25 shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="icon-circle bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-people-fill fs-5"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-dark">{{ __('Clientes / Sociedade') }}</h6>
                                </div>
                                <p class="small text-dark mb-3">
                                    <strong>{{ __('Definição:') }}</strong> {{ __('Objetivos focados na satisfação, fidelização e impacto no público-alvo.') }}
                                </p>
                                <div class="bg-light p-3 rounded-3 border mb-3">
                                    <p class="small mb-2 fw-semibold text-dark">{{ __('Exemplos:') }}</p>
                                    <ul class="small mb-0 ps-3 text-dark">
                                        <li>{{ __('Atingir 90% de satisfação dos usuários') }}</li>
                                        <li>{{ __('Ampliar atendimento em 20 municípios') }}</li>
                                        <li>{{ __('Reduzir tempo de espera para 10 dias') }}</li>
                                        <li>{{ __('Tornar-se referência nacional na área') }}</li>
                                    </ul>
                                </div>
                                <div class="alert alert-info alert-sm py-2 px-3 mb-0">
                                    <p class="small mb-1 fw-semibold text-dark">{{ __('Exemplo completo:') }}</p>
                                    <p class="small mb-0 fst-italic text-dark">
                                        {{ __('"Elevar o índice de satisfação dos cidadãos atendidos de 75% para 90% até junho de 2025"') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 border border-warning border-opacity-25 shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="icon-circle bg-warning bg-opacity-10 text-warning">
                                        <i class="bi bi-gear-fill fs-5"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-dark">{{ __('Processos Internos') }}</h6>
                                </div>
                                <p class="small text-dark mb-3">
                                    <strong>{{ __('Definição:') }}</strong> {{ __('Objetivos voltados à eficiência, qualidade e inovação dos processos organizacionais.') }}
                                </p>
                                <div class="bg-light p-3 rounded-3 border mb-3">
                                    <p class="small mb-2 fw-semibold text-dark">{{ __('Exemplos:') }}</p>
                                    <ul class="small mb-0 ps-3 text-dark">
                                        <li>{{ __('Automatizar 80% dos processos manuais') }}</li>
                                        <li>{{ __('Obter certificação ISO 9001') }}</li>
                                        <li>{{ __('Reduzir retrabalho em 50%') }}</li>
                                        <li>{{ __('Implementar gestão por projetos') }}</li>
                                    </ul>
                                </div>
                                <div class="alert alert-warning alert-sm py-2 px-3 mb-0">
                                    <p class="small mb-1 fw-semibold text-dark">{{ __('Exemplo completo:') }}</p>
                                    <p class="small mb-0 fst-italic text-dark">
                                        {{ __('"Digitalizar 100% dos processos administrativos até dezembro de 2024"') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 border border-primary border-opacity-25 shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-mortarboard-fill fs-5"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-dark">{{ __('Aprendizado e Crescimento') }}</h6>
                                </div>
                                <p class="small text-dark mb-3">
                                    <strong>{{ __('Definição:') }}</strong> {{ __('Objetivos relacionados à capacitação, cultura organizacional e infraestrutura.') }}
                                </p>
                                <div class="bg-light p-3 rounded-3 border mb-3">
                                    <p class="small mb-2 fw-semibold text-dark">{{ __('Exemplos:') }}</p>
                                    <ul class="small mb-0 ps-3 text-dark">
                                        <li>{{ __('Capacitar 100% dos servidores em BI') }}</li>
                                        <li>{{ __('Implementar programa de mentoria') }}</li>
                                        <li>{{ __('Modernizar infraestrutura de TI') }}</li>
                                        <li>{{ __('Criar cultura de inovação') }}</li>
                                    </ul>
                                </div>
                                <div class="alert alert-primary alert-sm py-2 px-3 mb-0">
                                    <p class="small mb-1 fw-semibold text-dark">{{ __('Exemplo completo:') }}</p>
                                    <p class="small mb-0 fst-italic text-dark">
                                        {{ __('"Treinar 100% da equipe em metodologias ágeis até março de 2025"') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Metodologia SMART --}}
                    <div class="col-12">
                        <div class="card border-2 border-secondary">
                            <div class="card-header bg-secondary bg-opacity-10">
                                <h6 class="fw-bold mb-0 text-secondary">
                                    <i class="bi bi-bullseye me-2"></i>{{ __('Objetivos SMART - Como escrever bem?') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="d-flex gap-2 align-items-start">
                                            <div class="icon-circle-mini bg-primary bg-opacity-10 text-primary flex-shrink-0">
                                                <strong class="small">S</strong>
                                            </div>
                                            <div>
                                                <h6 class="small fw-bold mb-1">{{ __('Específico (Specific)') }}</h6>
                                                <p class="small text-muted mb-0">{{ __('O que exatamente será feito? Evite ambiguidades.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex gap-2 align-items-start">
                                            <div class="icon-circle-mini bg-success bg-opacity-10 text-success flex-shrink-0">
                                                <strong class="small">M</strong>
                                            </div>
                                            <div>
                                                <h6 class="small fw-bold mb-1">{{ __('Mensurável (Measurable)') }}</h6>
                                                <p class="small text-muted mb-0">{{ __('Como saberemos que foi alcançado? Defina métrica.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex gap-2 align-items-start">
                                            <div class="icon-circle-mini bg-warning bg-opacity-10 text-warning flex-shrink-0">
                                                <strong class="small">A</strong>
                                            </div>
                                            <div>
                                                <h6 class="small fw-bold mb-1">{{ __('Atingível (Achievable)') }}</h6>
                                                <p class="small text-muted mb-0">{{ __('É realista considerando recursos disponíveis?') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex gap-2 align-items-start">
                                            <div class="icon-circle-mini bg-info bg-opacity-10 text-info flex-shrink-0">
                                                <strong class="small">R</strong>
                                            </div>
                                            <div>
                                                <h6 class="small fw-bold mb-1">{{ __('Relevante (Relevant)') }}</h6>
                                                <p class="small text-muted mb-0">{{ __('Alinha com a missão e visão da organização?') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex gap-2 align-items-start">
                                            <div class="icon-circle-mini bg-danger bg-opacity-10 text-danger flex-shrink-0">
                                                <strong class="small">T</strong>
                                            </div>
                                            <div>
                                                <h6 class="small fw-bold mb-1">{{ __('Temporal (Time-bound)') }}</h6>
                                                <p class="small text-muted mb-0">{{ __('Qual o prazo para conclusão?') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Dica Final --}}
                    <div class="col-12">
                        <div class="alert alert-secondary border-0 d-flex align-items-start gap-3 mb-0">
                            <div class="icon-circle-mini bg-secondary bg-opacity-10 text-secondary flex-shrink-0">
                                <i class="bi bi-stars"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2">{{ __('Dicas Profissionais') }}</h6>
                                <ul class="small mb-0 ps-3">
                                    <li>{{ __('Comece sempre com um verbo de ação (Aumentar, Reduzir, Implementar, Melhorar, Conquistar).') }}</li>
                                    <li>{{ __('Limite-se a 3-5 objetivos por perspectiva. Foco é essencial!') }}</li>
                                    <li>{{ __('Garanta que objetivos de perspectivas diferentes se conectem (causa e efeito).') }}</li>
                                    <li>{{ __('Use números sempre que possível (%, R$, quantidade, prazo).') }}</li>
                                    <li>{{ __('Use o Mentor de IA para gerar objetivos alinhados à sua visão!') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mentor de IA --}}
    @if($peiAtivo && $perspectivas->isNotEmpty() && $aiEnabled)
        <div class="ai-mentor-wrapper animate-fade-in mb-4">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <button wire:click="pedirAjudaIA" wire:loading.attr="disabled" class="ai-magic-button shadow-sm">
                    <span wire:loading.remove wire:target="pedirAjudaIA">
                        <i class="bi bi-robot"></i> {{ __('Gerar Objetivos com IA') }}
                    </span>
                    <span wire:loading wire:target="pedirAjudaIA">
                        <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Analisando e gerando...') }}
                    </span>
                </button>

                <div class="d-flex align-items-center gap-2 bg-white rounded-pill px-3 py-1 shadow-sm border">
                    <i class="bi bi-funnel text-muted small"></i>
                    <select wire:model.live="cod_perspectiva" class="form-select form-select-sm border-0 shadow-none bg-transparent" style="min-width: 200px; cursor: pointer;">
                        <option value="">{{ __('Sugestão para qual perspectiva?') }}</option>
                        @foreach($perspectivas as $p)
                            <option value="{{ $p->cod_perspectiva }}">{{ $p->dsc_perspectiva }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($aiSuggestion)
                <div class="ai-insight-card animate-fade-in">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-robot text-primary"></i>
                            <h6 class="fw-bold mb-0">{{ __('Objetivos Recomendados pelo Mentor IA') }}</h6>
                        </div>
                        <button type="button" class="btn-close small" style="font-size: 0.7rem;" wire:click="$set('aiSuggestion', '')"></button>
                    </div>
                    <div class="card-body">
                        @if(is_array($aiSuggestion))
                            <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                                @foreach($aiSuggestion as $obj)
                                    <div class="list-group-item d-flex align-items-start justify-content-between p-3 bg-light bg-opacity-25 hover-bg-white transition-all gap-3">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark">{{ $obj['nome'] }}</div>
                                            <p class="small text-muted mb-0 mt-1 lh-sm">{{ $obj['descricao'] }}</p>
                                        </div>
                                        <button wire:click="aplicarSugestao('{{ $obj['nome'] }}', '{{ $obj['descricao'] }}', {{ $obj['ordem'] ?? 1 }})" 
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

    @if (session()->has('status'))
        <div class="alert alert-modern alert-success alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert">
            <div class="alert-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <span class="flex-grow-1">{{ session('status') }}</span>
            <button type="button" class="btn-close btn-close-modern" data-bs-dismiss="alert" aria-label="{{ __('Fechar') }}"></button>
        </div>
    @endif

    @forelse($perspectivas as $perspectiva)
        <div class="card card-modern mb-4 border-0 shadow-sm">
            <div class="card-header border-bottom-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary small">
                            {{ $perspectiva->num_nivel_hierarquico_apresentacao }}
                        </div>
                        <h5 class="mb-0 fw-bold text-dark">{{ $perspectiva->dsc_perspectiva }}</h5>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">
                        {{ $perspectiva->objetivos->count() }} {{ __('objetivo(s)') }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-25 small text-muted text-uppercase fw-bold">
                            <tr>
                                <th class="ps-4" style="width: 80px;">{{ __('Ordem') }}</th>
                                <th>{{ __('Objetivo') }}</th>
                                <th>{{ __('Descrição') }}</th>
                                <th class="text-end pe-4">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($perspectiva->objetivos as $objetivo)
                                <tr>
                                    <td class="ps-4 fw-semibold text-primary">
                                        {{ $objetivo->num_nivel_hierarquico_apresentacao }}
                                    </td>
                                    <td>
                                        <a href="{{ route('objetivos.detalhes', $objetivo->cod_objetivo) }}" wire:navigate class="fw-bold text-dark text-decoration-none hover-primary">{{ $objetivo->nom_objetivo }}</a>
                                    </td>
                                    <td>
                                        <div class="text-muted small text-truncate" style="max-width: 400px;" title="{{ $objetivo->dsc_objetivo }}">
                                            {{ $objetivo->dsc_objetivo ?: __('Sem descrição') }}
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('objetivos.detalhes', $objetivo->cod_objetivo) }}" wire:navigate class="btn btn-sm btn-icon btn-ghost-info rounded-circle" title="{{ __('Detalhar') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button wire:click="edit('{{ $objetivo->cod_objetivo }}')" class="btn btn-sm btn-icon btn-ghost-primary rounded-circle" title="{{ __('Editar') }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button wire:click="confirmDelete('{{ $objetivo->cod_objetivo }}')" class="btn btn-sm btn-icon btn-ghost-danger rounded-circle" title="{{ __('Excluir') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small italic">
                                        <i class="bi bi-inbox me-1"></i> {{ __('Nenhum objetivo cadastrado nesta perspectiva.') }}
                                        <button wire:click="create('{{ $perspectiva->cod_perspectiva }}')" class="btn btn-link btn-sm p-0 text-primary fw-bold ms-1">{{ __('Adicionar o primeiro') }}</button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="card card-modern border-dashed">
            <div class="card-body p-5 text-center">
                <div class="empty-state">
                    <div class="empty-state-icon mb-3 text-muted">
                        <i class="bi bi-layers fs-1"></i>
                    </div>
                    <h5 class="empty-state-title">{{ __('Nenhuma perspectiva encontrada') }}</h5>
                    <p class="empty-state-text">
                        {{ __('Antes de criar objetivos, você precisa cadastrar as Perspectivas do BSC para este ciclo.') }}
                    </p>
                    <a href="{{ route('pei.perspectivas') }}" class="btn btn-primary mt-3" wire:navigate>
                        <i class="bi bi-layers me-1"></i> {{ __('Gerenciar Perspectivas') }}
                    </a>
                </div>
            </div>
        </div>
    @endforelse
    
    {{-- Modal de Cadastro/Edição Premium XL --}}
    @if($showModal)
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5); z-index: 1055;" wire:click.self="$set('showModal', false)">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    
                    {{-- Header Premium --}}
                    <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle-mini bg-white bg-opacity-25 text-white">
                                <i class="bi bi-{{ $objetivoId ? 'pencil-square' : 'plus-circle' }}"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">{{ $objetivoId ? 'Editar Objetivo Estratégico' : 'Novo Objetivo Estratégico' }}</h5>
                                <p class="mb-0 small text-white-50">Definição de metas e resultados de médio prazo</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                    </div>

                    <form wire:submit.prevent="save">
                        <div class="modal-body p-4 bg-white">
                            <div class="row g-4">
                                
                                {{-- Coluna Principal: Definição --}}
                                <div class="col-lg-7">
                                    <div class="card border-0 bg-light rounded-4 h-100">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">O que se pretende alcançar?</h6>
                                            
                                            {{-- Título --}}
                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between align-items-end mb-1">
                                                    <label class="form-label text-muted small text-uppercase fw-bold mb-0">Título do Objetivo <span class="text-danger">*</span></label>
                                                    <div wire:loading wire:target="nom_objetivo" class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                </div>
                                                <input type="text" wire:model.live.debounce.2000ms="nom_objetivo" class="form-control form-control-lg bg-white border-0 shadow-sm @error('nom_objetivo') is-invalid @enderror" placeholder="Ex: Aumentar a eficiência operacional">
                                                @error('nom_objetivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                
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

                                            <div class="mb-0">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Descrição Detalhada</label>
                                                <textarea wire:model="dsc_objetivo" class="form-control bg-white border-0 shadow-sm @error('dsc_objetivo') is-invalid @enderror" rows="5" placeholder="Explique o que se pretende alcançar com este objetivo..."></textarea>
                                                @error('dsc_objetivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Coluna Lateral: Vínculo e Hierarquia --}}
                                <div class="col-lg-5">
                                    <div class="card border-0 bg-light rounded-4 h-100">
                                        <div class="card-body p-4 text-center">
                                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-4">Contexto Estratégico</h6>
                                            
                                            {{-- Perspectiva --}}
                                            <div class="mb-4 text-start">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Perspectiva BSC <span class="text-danger">*</span></label>
                                                <select wire:model.live="cod_perspectiva" class="form-select bg-white border-0 shadow-sm fw-bold">
                                                    <option value="">Selecione uma perspectiva...</option>
                                                    @foreach($perspectivas as $p)
                                                        <option value="{{ $p->cod_perspectiva }}">{{ $p->dsc_perspectiva }}</option>
                                                    @endforeach
                                                </select>
                                                @error('cod_perspectiva') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                                            </div>

                                            {{-- Ordem --}}
                                            <div class="mb-4 text-start">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Ordem de Apresentação <span class="text-danger">*</span></label>
                                                <div class="input-group shadow-sm">
                                                    <span class="input-group-text bg-white border-0 text-primary"><i class="bi bi-sort-numeric-down"></i></span>
                                                    <input type="number" wire:model="num_nivel_hierarquico_apresentacao" class="form-control bg-white border-0 fw-bold" min="1">
                                                </div>
                                                @error('num_nivel_hierarquico_apresentacao') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                                            </div>

                                            {{-- Dica SMART --}}
                                            <div class="p-3 bg-white rounded-4 border shadow-sm mt-auto text-start">
                                                <h6 class="fw-bold small mb-2 text-primary"><i class="bi bi-stars me-2"></i>Dica SMART</h6>
                                                <p class="x-small mb-0 text-muted lh-sm">
                                                    Garanta que seu objetivo seja <strong>Específico</strong>, <strong>Mensurável</strong>, <strong>Atingível</strong>, <strong>Relevante</strong> e tenha um <strong>Prazo</strong> definido.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer Premium --}}
                        <div class="modal-footer border-0 p-4 bg-white rounded-bottom-4 shadow-top-sm">
                            <button type="button" class="btn btn-light px-4 rounded-pill fw-bold text-muted" wire:click="$set('showModal', false)">Cancelar</button>
                            <button type="submit" class="btn btn-primary gradient-theme-btn px-5 rounded-pill shadow-sm hover-scale">
                                <i class="bi bi-check-lg me-2"></i>{{ $objetivoId ? 'Atualizar Objetivo' : 'Salvar Objetivo' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Exclusão --}}
    <x-confirmation-modal wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="icon-circle-mini modal-icon-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold text-dark">{{ __('Excluir Objetivo') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Esta ação é irreversível') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation">
                <p class="mb-2 text-dark">
                    {{ __('Tem certeza que deseja excluir este objetivo estratégico?') }}
                </p>
                <div class="alert alert-warning bg-warning-subtle border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção:</strong> A exclusão deste objetivo afetará:
                    <ul class="mb-0 mt-2">
                        <li>{{ $impactoExclusao['indicadores'] ?? 0 }} Indicadores vinculados</li>
                        <li>{{ $impactoExclusao['planos'] ?? 0 }} Planos de Ação vinculados</li>
                    </ul>
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
                    <h3 class="fw-bold text-dark mb-3">Operação Concluída!</h3>
                    <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.6;">
                        <strong class="text-primary d-block mb-2">"{{ $createdObjetivoName }}"</strong>
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
                    <h3 class="fw-bold text-dark mb-3">Não foi possível salvar</h3>
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
</div>
