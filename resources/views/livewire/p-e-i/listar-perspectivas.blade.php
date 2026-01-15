<div>
    <!-- Header -->
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="icon-circle-header gradient-theme-icon">
                    <i class="bi bi-layers-fill"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ __('Perspectivas BSC') }}</h1>
                <span class="badge-modern badge-count">
            </div>
            <p class="text-muted mb-0">
                {{ __('Gerencie as 4 perspectivas fundamentais do Balanced Scorecard para o ciclo:') }} 
                @if($peiAtivo)
                    <strong>{{ $peiAtivo->dsc_pei }}</strong>
                @else
                    <span class="text-danger fw-bold">{{ __('Sem PEI Ativo') }}</span>
                @endif
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($peiAtivo)
                <button type="button" class="btn btn-primary gradient-theme-btn px-4 shadow-sm" wire:click="create">
                    <i class="bi bi-plus-lg me-1"></i> {{ __('Nova Perspectiva') }}
                </button>
            @endif
        </div>
    </div>

    {{-- Seção Educativa: O que são Perspectivas BSC --}}
    <div class="card border-0 shadow-sm mb-4 educational-card-gradient" x-data="{ expanded: false }">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle bg-white bg-opacity-25">
                        <i class="bi bi-book-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">
                            <i class="bi bi-mortarboard me-2"></i>{{ __('O que são Perspectivas BSC?') }}
                        </h5>
                        <p class="mb-0 text-white-50 small">
                            {{ __('Entenda as 4 dimensões estratégicas do Balanced Scorecard') }}
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
                        <i class="bi bi-info-circle me-2"></i>{{ __('O que é BSC?') }}
                    </h6>
                    <p class="text-muted mb-3">
                        O <strong>Balanced Scorecard (BSC)</strong> é uma metodologia de gestão estratégica que organiza objetivos em <strong>4 perspectivas interligadas</strong>.
                        Criado por Robert Kaplan e David Norton, o BSC equilibra indicadores financeiros e não-financeiros, oferecendo uma visão integrada do desempenho organizacional.
                    </p>
                    <p class="text-muted mb-0">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        <strong>Por que usar perspectivas?</strong> Elas garantem que a estratégia não foque apenas em resultados financeiros,
                        mas também em clientes, processos internos e capacidade de aprendizado e crescimento.
                    </p>
                </div>

                {{-- Diagrama de Cascata (Causa e Efeito) --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-diagram-3 me-2"></i>{{ __('Relação de Causa e Efeito') }}
                    </h6>
                    <p class="small text-muted mb-3">
                        As perspectivas se conectam em uma cadeia lógica: investir em <strong>aprendizado</strong> melhora os <strong>processos</strong>,
                        que atendem melhor os <strong>clientes</strong>, gerando melhores <strong>resultados</strong>.
                    </p>

                    <div class="d-flex flex-column gap-2">
                        {{-- Perspectiva 1: Financeira/Resultados (TOP) --}}
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 border border-success bg-success bg-opacity-5">
                            <div class="icon-circle-mini bg-success bg-opacity-10 text-success">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0 text-success">1. Perspectiva Financeira / Resultados</h6>
                                <p class="x-small text-muted mb-0">Como agregar valor à sociedade e aos stakeholders?</p>
                            </div>
                            <span class="badge bg-success">TOPO</span>
                        </div>

                        {{-- Arrow --}}
                        <div class="text-center text-muted">
                            <i class="bi bi-arrow-down-short fs-3"></i>
                            <p class="x-small mb-0">é resultado de</p>
                        </div>

                        {{-- Perspectiva 2: Clientes/Sociedade --}}
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 border border-info bg-info bg-opacity-5">
                            <div class="icon-circle-mini bg-info bg-opacity-10 text-info">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0 text-info">2. Perspectiva Clientes / Sociedade</h6>
                                <p class="x-small text-muted mb-0">Como encantar os cidadãos e usuários dos serviços?</p>
                            </div>
                        </div>

                        {{-- Arrow --}}
                        <div class="text-center text-muted">
                            <i class="bi bi-arrow-down-short fs-3"></i>
                            <p class="x-small mb-0">é resultado de</p>
                        </div>

                        {{-- Perspectiva 3: Processos Internos --}}
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 border border-warning bg-warning bg-opacity-5">
                            <div class="icon-circle-mini bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-gear"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0 text-warning">3. Perspectiva Processos Internos</h6>
                                <p class="x-small text-muted mb-0">Em quais processos devemos ser excelentes?</p>
                            </div>
                        </div>

                        {{-- Arrow --}}
                        <div class="text-center text-muted">
                            <i class="bi bi-arrow-down-short fs-3"></i>
                            <p class="x-small mb-0">é resultado de</p>
                        </div>

                        {{-- Perspectiva 4: Aprendizado e Crescimento (BASE) --}}
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 border border-primary bg-primary bg-opacity-5">
                            <div class="icon-circle-mini bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-mortarboard"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0 text-primary">4. Perspectiva Aprendizado e Crescimento</h6>
                                <p class="x-small text-muted mb-0">Como desenvolver nossa capacidade de inovar e melhorar?</p>
                            </div>
                            <span class="badge bg-primary">BASE</span>
                        </div>
                    </div>
                </div>

                {{-- Detalhamento das 4 Perspectivas --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-journal-text me-2"></i>{{ __('Entenda cada Perspectiva') }}
                    </h6>

                    <div class="row g-3">
                        {{-- Financeira/Resultados --}}
                        <div class="col-md-6">
                            <div class="card h-100 border-2 border-success">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-graph-up-arrow"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-success">Financeira / Resultados</h6>
                                    </div>
                                    <p class="small text-muted mb-3">
                                        Mede o <strong>valor entregue</strong> à sociedade e aos stakeholders. No setor público, foca em eficiência, eficácia e impacto social.
                                    </p>
                                    <div class="mb-3">
                                        <p class="fw-bold x-small mb-1 text-dark">Exemplos de Objetivos:</p>
                                        <ul class="x-small text-muted mb-0">
                                            <li>Reduzir custos operacionais em 15%</li>
                                            <li>Aumentar arrecadação própria</li>
                                            <li>Melhorar índice de execução orçamentária</li>
                                            <li>Aumentar retorno social dos investimentos</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Clientes/Sociedade --}}
                        <div class="col-md-6">
                            <div class="card h-100 border-2 border-info">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-info bg-opacity-10 text-info">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-info">Clientes / Sociedade</h6>
                                    </div>
                                    <p class="small text-muted mb-3">
                                        Avalia a <strong>satisfação dos cidadãos</strong> e usuários dos serviços públicos. Foca em qualidade, acessibilidade e transparência.
                                    </p>
                                    <div class="mb-3">
                                        <p class="fw-bold x-small mb-1 text-dark">Exemplos de Objetivos:</p>
                                        <ul class="x-small text-muted mb-0">
                                            <li>Aumentar satisfação dos cidadãos para 85%</li>
                                            <li>Reduzir tempo de espera no atendimento</li>
                                            <li>Ampliar canais digitais de atendimento</li>
                                            <li>Melhorar transparência e acesso à informação</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Processos Internos --}}
                        <div class="col-md-6">
                            <div class="card h-100 border-2 border-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-warning bg-opacity-10 text-warning">
                                            <i class="bi bi-gear"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-warning">Processos Internos</h6>
                                    </div>
                                    <p class="small text-muted mb-3">
                                        Identifica <strong>processos críticos</strong> que devem ter excelência operacional para entregar valor aos clientes e resultados.
                                    </p>
                                    <div class="mb-3">
                                        <p class="fw-bold x-small mb-1 text-dark">Exemplos de Objetivos:</p>
                                        <ul class="x-small text-muted mb-0">
                                            <li>Digitalizar 100% dos processos administrativos</li>
                                            <li>Reduzir prazo de licitações em 30%</li>
                                            <li>Implementar gestão de riscos operacionais</li>
                                            <li>Padronizar fluxos de trabalho críticos</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Aprendizado e Crescimento --}}
                        <div class="col-md-6">
                            <div class="card h-100 border-2 border-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-mortarboard"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-primary">Aprendizado e Crescimento</h6>
                                    </div>
                                    <p class="small text-muted mb-3">
                                        Foca no <strong>capital humano, tecnológico e organizacional</strong> necessário para sustentar a estratégia e a inovação contínua.
                                    </p>
                                    <div class="mb-3">
                                        <p class="fw-bold x-small mb-1 text-dark">Exemplos de Objetivos:</p>
                                        <ul class="x-small text-muted mb-0">
                                            <li>Capacitar 100% dos servidores em inovação</li>
                                            <li>Implementar cultura de gestão de dados</li>
                                            <li>Modernizar infraestrutura de TI</li>
                                            <li>Aumentar engajamento dos colaboradores</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dicas Profissionais --}}
                <div>
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-star me-2"></i>{{ __('Dicas para Usar as Perspectivas BSC') }}
                    </h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Equilíbrio é essencial</p>
                                    <p class="x-small text-muted mb-0">Distribua objetivos entre as 4 perspectivas. Não concentre tudo em uma dimensão</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Pense em causa-efeito</p>
                                    <p class="x-small text-muted mb-0">Conecte objetivos entre perspectivas: aprendizado → processos → clientes → resultados</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Personalize para o setor público</p>
                                    <p class="x-small text-muted mb-0">Adapte "Financeira" para "Resultados" e "Clientes" para "Sociedade/Cidadãos"</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Defina cores e ícones</p>
                                    <p class="x-small text-muted mb-0">Padronize a identidade visual de cada perspectiva no Mapa Estratégico</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Comece pela base</p>
                                    <p class="x-small text-muted mb-0">Ao planejar, pergunte-se: "O que precisamos aprender?" → "Que processos melhorar?" → "Como atender melhor?" → "Que resultados alcançar?"</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-modern alert-success alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert">
            <div class="icon-circle-mini"><i class="bi bi-check-circle-fill"></i></div>
            <span class="flex-grow-1">{{ session('status') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-modern alert-danger alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert">
            <div class="icon-circle-mini"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <span class="flex-grow-1">{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Mentor de IA --}}
    @if($peiAtivo && $aiEnabled)
        <div class="ai-mentor-wrapper animate-fade-in">
            <button wire:click="pedirAjudaIA" wire:loading.attr="disabled" class="ai-magic-button shadow-sm">
                <span wire:loading.remove wire:target="pedirAjudaIA">
                    <i class="bi bi-robot"></i> {{ __('Sugerir Perspectivas Estratégicas com IA') }}
                </span>
                <span wire:loading wire:target="pedirAjudaIA">
                    <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Analisando Missão e Visão...') }}
                </span>
            </button>

            @if($aiSuggestion)
                <div class="ai-insight-card animate-fade-in">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-chat-left-dots-fill text-primary"></i>
                            <h6 class="fw-bold mb-0">{{ __('Sugestões do Mentor IA') }}</h6>
                        </div>
                        <button type="button" class="btn-close small" style="font-size: 0.7rem;" wire:click="$set('aiSuggestion', '')"></button>
                    </div>
                    <div class="card-body">
                        @if(is_array($aiSuggestion))
                            <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                                @foreach($aiSuggestion as $sugestao)
                                    <div class="list-group-item d-flex align-items-start justify-content-between p-3 bg-light bg-opacity-25 hover-bg-white transition-all gap-3">
                                        <div class="d-flex align-items-start gap-3 flex-grow-1">
                                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center mt-1" style="width: 24px; height: 24px; flex-shrink: 0;">
                                                {{ $sugestao['ordem'] }}
                                            </span>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $sugestao['nome'] }}</div>
                                                <p class="small text-muted mb-0 mt-1 lh-sm">{{ $sugestao['descricao'] ?? '' }}</p>
                                            </div>
                                        </div>
                                        <button wire:click="aplicarSugestao('{{ $sugestao['nome'] }}', {{ $sugestao['ordem'] }})" 
                                                wire:loading.attr="disabled"
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
                        <div class="mt-3 pt-3 border-top d-flex align-items-center gap-2 text-muted small italic">
                            <i class="bi bi-info-circle"></i>
                            {{ __('Clique em "Adicionar" para gravar a sugestão diretamente no seu plano.') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if(!$peiAtivo)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Nenhum PEI ativo encontrado. Configure um PEI antes de gerenciar as perspectivas.
        </div>
    @else
        <!-- Lista de Perspectivas -->
        <div class="card">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-ul me-2"></i>Perspectivas Cadastradas</span>
                    <span class="badge bg-primary">{{ count($perspectivas) }} perspectiva(s)</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if(count($perspectivas) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;" class="text-center">Ordem</th>
                                    <th>Perspectiva</th>
                                    <th style="width: 150px;" class="text-center">Objetivos</th>
                                    <th style="width: 120px;" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($perspectivas as $perspectiva)
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge bg-secondary rounded-pill">
                                                {{ $perspectiva->num_nivel_hierarquico_apresentacao }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="icon-circle-header gradient-theme-icon me-3">
                                                    <i class="bi bi-layers text-white"></i>
                                                </div>
                                                <div>
                                                    <a href="{{ route('pei.perspectivas.detalhes', $perspectiva->cod_perspectiva) }}" wire:navigate class="fw-bold text-dark text-decoration-none hover-primary">
                                                        {{ $perspectiva->dsc_perspectiva }}
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">
                                                {{ $perspectiva->objetivos_count ?? $perspectiva->objetivos()->count() }} objetivo(s)
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('pei.perspectivas.detalhes', $perspectiva->cod_perspectiva) }}" wire:navigate class="btn btn-outline-info" title="Detalhar">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-primary" wire:click="edit('{{ $perspectiva->cod_perspectiva }}')" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" wire:click="confirmDelete('{{ $perspectiva->cod_perspectiva }}')" title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                        <h5 class="text-muted">Nenhuma perspectiva cadastrada</h5>
                        <p class="text-muted mb-3">Comece adicionando as perspectivas do seu Balanced Scorecard</p>
                        <button type="button" class="btn btn-primary gradient-theme-btn px-4 shadow-sm" wire:click="create">
                            <i class="bi bi-plus-lg me-1"></i> Adicionar Perspectiva
                        </button>
                    </div>
                @endif
            </div>
        </div>

    @if($peiAtivo)
        <!-- Modal de Criação/Edição -->
        @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-{{ $perspectivaId ? 'pencil' : 'plus-circle' }} me-2"></i>
                            {{ $perspectivaId ? 'Editar' : 'Nova' }} Perspectiva
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="dsc_perspectiva" class="form-label">Nome da Perspectiva <span class="text-danger">*</span></label>
                                <input type="text" wire:model="dsc_perspectiva" id="dsc_perspectiva" class="form-control @error('dsc_perspectiva') is-invalid @enderror" placeholder="Ex: Financeira, Clientes, Processos Internos..." required>
                                @error('dsc_perspectiva')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="num_nivel_hierarquico_apresentacao" class="form-label">Ordem de Apresentação <span class="text-danger">*</span></label>
                                <input type="number" wire:model="num_nivel_hierarquico_apresentacao" id="num_nivel_hierarquico_apresentacao" class="form-control @error('num_nivel_hierarquico_apresentacao') is-invalid @enderror" min="1" required>
                                <div class="form-text">Define a posição desta perspectiva no mapa estratégico (1 = topo)</div>
                                @error('num_nivel_hierarquico_apresentacao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">
                                <i class="bi bi-x-lg me-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> {{ $perspectivaId ? 'Atualizar' : 'Salvar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endif
    @endif

    {{-- Modal de Exclusão --}}
    <x-confirmation-modal wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="icon-circle-mini modal-icon-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold text-dark">{{ __('Excluir Perspectiva') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Esta ação é irreversível') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation">
                <p class="mb-2 text-dark">
                    {{ __('Tem certeza que deseja excluir esta perspectiva estratégica?') }}
                </p>
                <div class="alert alert-warning bg-warning-subtle border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção:</strong> Todos os objetivos, indicadores e planos associados a esta perspectiva também serão removidos.
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
