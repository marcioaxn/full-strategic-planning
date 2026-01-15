<div>
    {{-- Cabeçalho Interno --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="icon-circle-header gradient-theme-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ __('Objetivos Estratégicos') }}</h1>
                <span class="badge-modern badge-count">
                    {{ $objetivos->count() }}
                </span>
            </div>
            <p class="text-muted mb-0">
                @if($peiAtivo)
                    Gerenciando objetivos para o ciclo: <strong>{{ $peiAtivo->dsc_pei }}</strong>
                @else
                    <span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Nenhum Ciclo PEI Ativo encontrado.</span>
                @endif
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($peiAtivo)
                <button wire:click="create" class="btn btn-primary gradient-theme-btn shadow-sm">
                    <i class="bi bi-plus-lg me-2"></i>Novo Objetivo
                </button>
            @endif
        </div>
    </div>

    {{-- Seção Educativa: O que são Objetivos Estratégicos --}}
    <div class="card border-0 shadow-sm mb-4 educational-card-gradient" x-data="{ expanded: false }">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle bg-white bg-opacity-25">
                        <i class="bi bi-book-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">
                            <i class="bi bi-mortarboard me-2"></i>{{ __('O que são Objetivos Estratégicos?') }}
                        </h5>
                        <p class="mb-0 text-white-50 small">
                            {{ __('Aprenda a criar objetivos SMART alinhados à estratégia institucional') }}
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
                        <i class="bi bi-info-circle me-2"></i>{{ __('O que são Objetivos Estratégicos?') }}
                    </h6>
                    <p class="text-muted mb-3">
                        <strong>Objetivos Estratégicos</strong> são declarações que descrevem <strong>o que a organização pretende alcançar</strong> no médio e longo prazo
                        para cumprir sua missão e realizar sua visão de futuro. Eles traduzem a estratégia em metas concretas e mensuráveis.
                    </p>
                    <p class="text-muted mb-0">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        <strong>Por que são importantes?</strong> Objetivos estratégicos conectam a alta gestão (missão/visão) com a execução operacional (planos de ação).
                        São o elo entre "onde queremos chegar" e "como vamos chegar lá".
                    </p>
                </div>

                {{-- Metodologia SMART --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-bullseye me-2"></i>{{ __('Critérios SMART para Objetivos') }}
                    </h6>
                    <p class="small text-muted mb-3">
                        Todo objetivo estratégico deve atender aos <strong>5 critérios SMART</strong> para ser eficaz:
                    </p>

                    <div class="row g-3">
                        {{-- S - Specific --}}
                        <div class="col-md-6">
                            <div class="card border-2 border-primary h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-crosshair"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-primary">S - Específico (Specific)</h6>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        O objetivo responde <strong>"O quê?"</strong>, <strong>"Quem?"</strong> e <strong>"Por quê?"</strong> de forma clara.
                                    </p>
                                    <div class="bg-light p-2 rounded">
                                        <p class="x-small mb-1"><strong class="text-danger">❌ Ruim:</strong> "Melhorar atendimento"</p>
                                        <p class="x-small mb-0"><strong class="text-success">✅ Bom:</strong> "Reduzir tempo médio de atendimento ao cidadão de 45min para 15min"</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- M - Measurable --}}
                        <div class="col-md-6">
                            <div class="card border-2 border-success h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-graph-up"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-success">M - Mensurável (Measurable)</h6>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        Possui <strong>indicadores (KPIs)</strong> que permitem acompanhar o progresso quantitativamente.
                                    </p>
                                    <div class="bg-light p-2 rounded">
                                        <p class="x-small mb-1"><strong class="text-danger">❌ Ruim:</strong> "Aumentar satisfação"</p>
                                        <p class="x-small mb-0"><strong class="text-success">✅ Bom:</strong> "Alcançar 85% de satisfação na pesquisa NPS"</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- A - Achievable --}}
                        <div class="col-md-6">
                            <div class="card border-2 border-warning h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-warning bg-opacity-10 text-warning">
                                            <i class="bi bi-ladder"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-warning">A - Atingível (Achievable)</h6>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        Desafiador, mas <strong>realista</strong> considerando recursos, orçamento e capacidade da equipe.
                                    </p>
                                    <div class="bg-light p-2 rounded">
                                        <p class="x-small mb-1"><strong class="text-danger">❌ Ruim:</strong> "Zerar fila de processos em 1 mês" (com backlog de 10 anos)</p>
                                        <p class="x-small mb-0"><strong class="text-success">✅ Bom:</strong> "Reduzir fila em 30% em 12 meses"</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- R - Relevant --}}
                        <div class="col-md-6">
                            <div class="card border-2 border-info h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-info bg-opacity-10 text-info">
                                            <i class="bi bi-link-45deg"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-info">R - Relevante (Relevant)</h6>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        Está <strong>alinhado</strong> com a missão, visão e prioridades estratégicas da organização.
                                    </p>
                                    <div class="bg-light p-2 rounded">
                                        <p class="x-small mb-1"><strong class="text-danger">❌ Ruim:</strong> Objetivo isolado, sem conexão com missão</p>
                                        <p class="x-small mb-0"><strong class="text-success">✅ Bom:</strong> Objetivo contribui diretamente para realizar a visão institucional</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- T - Time-bound --}}
                        <div class="col-12">
                            <div class="card border-2 border-danger">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-danger bg-opacity-10 text-danger">
                                            <i class="bi bi-calendar-check"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-danger">T - Temporal (Time-bound)</h6>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        Possui <strong>prazo definido</strong> para ser alcançado. Sem prazo, não há senso de urgência.
                                    </p>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="bg-light p-2 rounded">
                                                <p class="x-small mb-0"><strong class="text-danger">❌ Ruim:</strong> "Implementar novo sistema"</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light p-2 rounded">
                                                <p class="x-small mb-0"><strong class="text-success">✅ Bom:</strong> "Implementar novo sistema até dezembro/2025"</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Hierarquia de Objetivos (Estratégico vs Tático vs Operacional) --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-diagram-3 me-2"></i>{{ __('Níveis de Objetivos') }}
                    </h6>
                    <p class="small text-muted mb-3">
                        Objetivos se organizam em <strong>3 níveis hierárquicos</strong>:
                    </p>

                    <div class="d-flex flex-column gap-2">
                        {{-- Estratégico (Topo) --}}
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 border border-danger bg-danger bg-opacity-5">
                            <div class="icon-circle-mini bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-trophy"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0 text-danger">1. Estratégico (Alta Gestão)</h6>
                                <p class="x-small text-muted mb-0">
                                    <strong>Prazo:</strong> 3-5 anos | <strong>Foco:</strong> Visão de futuro, transformação institucional<br>
                                    <strong>Ex:</strong> "Tornar-se referência nacional em transparência até 2027"
                                </p>
                            </div>
                            <span class="badge bg-danger">TOPO</span>
                        </div>

                        {{-- Arrow --}}
                        <div class="text-center text-muted">
                            <i class="bi bi-arrow-down-short fs-3"></i>
                            <p class="x-small mb-0">se desdobra em</p>
                        </div>

                        {{-- Tático (Meio) --}}
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 border border-warning bg-warning bg-opacity-5">
                            <div class="icon-circle-mini bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-flag"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0 text-warning">2. Tático (Gerências/Diretorias)</h6>
                                <p class="x-small text-muted mb-0">
                                    <strong>Prazo:</strong> 1-2 anos | <strong>Foco:</strong> Projetos e programas departamentais<br>
                                    <strong>Ex:</strong> "Implementar portal de transparência ativa até dez/2025"
                                </p>
                            </div>
                        </div>

                        {{-- Arrow --}}
                        <div class="text-center text-muted">
                            <i class="bi bi-arrow-down-short fs-3"></i>
                            <p class="x-small mb-0">se desdobra em</p>
                        </div>

                        {{-- Operacional (Base) --}}
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 border border-primary bg-primary bg-opacity-5">
                            <div class="icon-circle-mini bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-gear"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0 text-primary">3. Operacional (Equipes/Setores)</h6>
                                <p class="x-small text-muted mb-0">
                                    <strong>Prazo:</strong> 3-12 meses | <strong>Foco:</strong> Tarefas e atividades do dia a dia<br>
                                    <strong>Ex:</strong> "Publicar relatório mensal de execução orçamentária até dia 10"
                                </p>
                            </div>
                            <span class="badge bg-primary">BASE</span>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <p class="small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Nesta página:</strong> Você gerencia objetivos <strong class="text-danger">Estratégicos</strong> (nível institucional, vinculados ao PEI).
                        </p>
                    </div>
                </div>

                {{-- Exemplo Completo de Objetivo SMART --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-star me-2"></i>{{ __('Exemplo de Objetivo Estratégico SMART Completo') }}
                    </h6>

                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="icon-circle-mini bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-trophy"></i>
                                </div>
                                <h6 class="fw-bold mb-0">Objetivo: Melhorar Eficiência do Atendimento ao Cidadão</h6>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-borderless mb-3">
                                    <tbody class="small">
                                        <tr>
                                            <td class="fw-bold text-primary" style="width: 140px;">
                                                <i class="bi bi-crosshair me-1"></i>Específico
                                            </td>
                                            <td>Reduzir o tempo médio de atendimento presencial nos balcões de atendimento</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-success">
                                                <i class="bi bi-graph-up me-1"></i>Mensurável
                                            </td>
                                            <td>
                                                <strong>Indicador:</strong> Tempo médio de atendimento (minutos)<br>
                                                <strong>Meta:</strong> Reduzir de 45min (baseline 2024) para 15min<br>
                                                <strong>Fonte de dados:</strong> Sistema de senhas eletrônicas
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-warning">
                                                <i class="bi bi-ladder me-1"></i>Atingível
                                            </td>
                                            <td>
                                                Sim. Recursos disponíveis: R$ 150k para novo sistema + treinamento de 20 atendentes.<br>
                                                Benchmark: outras prefeituras similares já alcançaram 12-18min
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-info">
                                                <i class="bi bi-link-45deg me-1"></i>Relevante
                                            </td>
                                            <td>
                                                Alinhado à <strong>Visão:</strong> "Ser referência em atendimento de excelência"<br>
                                                Contribui para <strong>Perspectiva BSC:</strong> Clientes/Sociedade
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-danger">
                                                <i class="bi bi-calendar-check me-1"></i>Temporal
                                            </td>
                                            <td>
                                                <strong>Prazo:</strong> Alcançar meta até 31/12/2025 (18 meses)<br>
                                                <strong>Marcos intermediários:</strong> 35min em jun/25, 25min em set/25, 15min em dez/25
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-success mb-0 py-2">
                                <p class="small mb-0">
                                    <strong>✅ Objetivo aprovado!</strong> Atende aos 5 critérios SMART e está pronto para execução.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dicas Profissionais --}}
                <div>
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-trophy me-2"></i>{{ __('Boas Práticas para Definir Objetivos Estratégicos') }}
                    </h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Limite a quantidade</p>
                                    <p class="x-small text-muted mb-0">5-7 objetivos estratégicos por ciclo PEI. Mais que isso dispersa esforços e recursos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Use verbos de ação</p>
                                    <p class="x-small text-muted mb-0">Aumentar, Reduzir, Implementar, Melhorar, Alcançar. Evite "Garantir", "Assegurar" (vagos)</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Distribua entre perspectivas BSC</p>
                                    <p class="x-small text-muted mb-0">Equilibre objetivos nas 4 perspectivas: Financeira, Clientes, Processos, Aprendizado</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Vincule indicadores desde o início</p>
                                    <p class="x-small text-muted mb-0">Não crie objetivo sem saber como vai medir. Defina KPIs junto com o objetivo</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Envolva stakeholders</p>
                                    <p class="x-small text-muted mb-0">Discuta objetivos com equipes, gestores e alta direção antes de finalizar. Objetivos impostos têm menos engajamento</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!$peiAtivo)
        <div class="alert alert-modern alert-danger shadow-sm border-0 d-flex align-items-center p-4">
            <i class="bi bi-exclamation-octagon fs-2 me-4"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Nenhum Ciclo PEI Ativo</h5>
                <p class="mb-0">Não é possível gerenciar objetivos estratégicos sem um ciclo ativo definido.</p>
            </div>
        </div>
    @else
        {{-- Mentor de IA --}}
        @if($aiEnabled)
            <div class="ai-mentor-wrapper animate-fade-in">
                <button wire:click="pedirAjudaIA" wire:loading.attr="disabled" class="ai-magic-button shadow-sm">
                    <span wire:loading.remove wire:target="pedirAjudaIA">
                        <i class="bi bi-robot"></i> {{ __('Sugerir Objetivos Institucionais com IA') }}
                    </span>
                    <span wire:loading wire:target="pedirAjudaIA">
                        <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Analisando contexto organizacional...') }}
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
                                    @foreach($aiSuggestion as $sug)
                                        <div class="list-group-item d-flex align-items-start justify-content-between p-3 bg-light bg-opacity-25 hover-bg-white transition-all gap-3">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-dark">{{ $sug['nome'] }}</div>
                                            </div>
                                            <button wire:click="aplicarSugestao('{{ $sug['nome'] }}')" 
                                                    class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold flex-shrink-0">
                                                <i class="bi bi-plus-lg me-1"></i> {{ __('Adicionar') }}
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Filtros e Busca --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 bg-light rounded-3">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar por título do objetivo...">
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div wire:loading class="spinner-border text-primary spinner-border-sm mt-2" role="status"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabela de Dados --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4" style="width: 60%">Título do Objetivo Estratégico</th>
                            <th>Unidade</th>
                            <th>Ciclo PEI</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($objetivos as $obj)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark mb-1">{{ $obj->nom_objetivo_estrategico }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-3 rounded-pill">
                                        {{ $obj->organizacao->sgl_organizacao ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <small class="fw-semibold text-primary">{{ $obj->pei->dsc_pei }}</small>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button wire:click="edit('{{ $obj->cod_objetivo_estrategico }}')" class="btn btn-sm btn-icon btn-ghost-primary rounded-circle" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="confirmDelete('{{ $obj->cod_objetivo_estrategico }}')" class="btn btn-sm btn-icon btn-ghost-danger rounded-circle" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-bullseye fs-1 opacity-25 mb-3 d-block"></i>
                                    Nenhum objetivo estratégico encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3">
                {{ $objetivos->links() }}
            </div>
        </div>
    @endif

    {{-- Modal de Cadastro/Edição --}}
    <div class="modal fade @if($showModal) show @endif" tabindex="-1" style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header gradient-theme text-white border-0 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-{{ $objetivoId ? 'pencil' : 'plus-circle' }} me-2"></i>
                        {{ $objetivoId ? 'Editar Objetivo Estratégico' : 'Novo Objetivo Estratégico' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="resetForm"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4 bg-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase">Título do Objetivo <span class="text-danger">*</span></label>
                                <textarea wire:model="nom_objetivo_estrategico" class="form-control @error('nom_objetivo_estrategico') is-invalid @enderror" rows="3" placeholder="Ex: Ampliar a capilaridade dos serviços públicos..."></textarea>
                                @error('nom_objetivo_estrategico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase">Unidade Organizacional <span class="text-danger">*</span></label>
                                <select wire:model="cod_organizacao" class="form-select @error('cod_organizacao') is-invalid @enderror">
                                    <option value="">Selecione uma unidade...</option>
                                    @foreach($organizacoes as $org)
                                        <option value="{{ $org->cod_organizacao }}">{{ $org->nom_organizacao }} ({{ $org->sgl_organizacao }})</option>
                                    @endforeach
                                </select>
                                @error('cod_organizacao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 p-3">
                        <button type="button" class="btn btn-outline-secondary px-4 rounded-pill fw-bold" wire:click="resetForm">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 gradient-theme-btn rounded-pill fw-bold">
                            <i class="bi bi-check-lg me-1"></i> {{ $objetivoId ? 'Atualizar' : 'Salvar Objetivo' }}
                        </button>
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
                    <h5 class="mb-1 fw-bold text-dark">{{ __('Excluir Objetivo Estratégico') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Esta ação é irreversível') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation text-start">
                <p class="mb-2 text-dark">
                    {{ __('Tem certeza que deseja excluir este objetivo institucional?') }}
                </p>
                <div class="alert alert-warning bg-warning-subtle border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção:</strong> Esta ação removerá o objetivo do planejamento corporativo.
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="resetForm" wire:loading.attr="disabled" class="btn-modern">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-danger-button wire:click="delete" wire:loading.attr="disabled" class="btn-delete-modern ms-2">
                <span wire:loading.remove wire:target="delete">
                    <i class="bi bi-trash me-1"></i>{{ __('Excluir') }}
                </span>
                <span wire:loading wire:target="delete">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                </span>
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    <style>
        .header-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .loading-opacity {
        .btn-icon:hover { transform: scale(1.1); }
        .modal-content { animation: fadeInUp 0.2s ease-out; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</div>
