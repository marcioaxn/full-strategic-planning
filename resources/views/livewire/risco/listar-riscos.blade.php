<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Gestão de Riscos</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Riscos Estratégicos</h2>
            </div>
            <div class="d-flex gap-2">
                @if($organizacaoId)
                    <a href="{{ route('riscos.matriz') }}" class="btn btn-outline-primary shadow-sm rounded-pill px-3">
                        <i class="bi bi-grid-3x3-gap me-1"></i> Ver Matriz
                    </a>
                    <button wire:click="create" class="btn btn-primary gradient-theme-btn shadow-sm rounded-pill">
                        <i class="bi bi-plus-lg me-2"></i>Identificar Risco
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    {{-- Seção Educativa: O que são Riscos Estratégicos --}}
    <div class="card border-0 shadow-sm mb-4 educational-card-gradient" x-data="{ expanded: false }">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle bg-white bg-opacity-25">
                        <i class="bi bi-book-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">
                            <i class="bi bi-mortarboard me-2"></i>{{ __('O que são Riscos Estratégicos?') }}
                        </h5>
                        <p class="mb-0 text-white-50 small">
                            {{ __('Aprenda a identificar, avaliar e gerenciar riscos') }}
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
                        <i class="bi bi-info-circle me-2"></i>{{ __('O que são Riscos Estratégicos?') }}
                    </h6>
                    <p class="text-muted mb-3">
                        <strong>Riscos Estratégicos</strong> são eventos incertos (positivos ou negativos) que podem <strong>afetar o alcance dos objetivos estratégicos</strong>.
                        São ameaças ou oportunidades externas e internas que devem ser identificadas, avaliadas e gerenciadas proativamente.
                    </p>
                    <p class="text-muted mb-0">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        <strong>Por que gerenciar riscos?</strong> Gestão de riscos permite antecipar problemas antes que se tornem crises,
                        aproveitar oportunidades emergentes e tomar decisões mais informadas.
                    </p>
                </div>

                {{-- Matriz de Riscos (Probabilidade x Impacto) --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-grid-3x3-gap me-2"></i>{{ __('Matriz de Riscos (Probabilidade x Impacto)') }}
                    </h6>
                    <p class="small text-muted mb-3">
                        A <strong>Matriz de Riscos</strong> classifica riscos combinando <strong>Probabilidade</strong> (chance de ocorrer)
                        e <strong>Impacto</strong> (consequência se ocorrer). Isso prioriza quais riscos merecem atenção imediata.
                    </p>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm text-center mb-0">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="align-middle fw-bold small bg-light" style="width: 15%;">
                                        <i class="bi bi-arrow-down me-1"></i>Probabilidade<br>
                                        <i class="bi bi-arrow-right me-1"></i>Impacto
                                    </th>
                                    <th class="fw-bold small bg-light">Muito Baixo</th>
                                    <th class="fw-bold small bg-light">Baixo</th>
                                    <th class="fw-bold small bg-light">Médio</th>
                                    <th class="fw-bold small bg-light">Alto</th>
                                    <th class="fw-bold small bg-light">Muito Alto</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <tr>
                                    <td class="fw-bold bg-light">Muito Alta</td>
                                    <td class="bg-warning bg-opacity-25">Médio</td>
                                    <td class="bg-warning bg-opacity-50">Alto</td>
                                    <td class="bg-danger bg-opacity-25">Alto</td>
                                    <td class="bg-danger bg-opacity-50">Crítico</td>
                                    <td class="bg-danger bg-opacity-75">Crítico</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold bg-light">Alta</td>
                                    <td class="bg-success bg-opacity-25">Baixo</td>
                                    <td class="bg-warning bg-opacity-25">Médio</td>
                                    <td class="bg-warning bg-opacity-50">Alto</td>
                                    <td class="bg-danger bg-opacity-25">Alto</td>
                                    <td class="bg-danger bg-opacity-50">Crítico</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold bg-light">Média</td>
                                    <td class="bg-success bg-opacity-25">Baixo</td>
                                    <td class="bg-success bg-opacity-50">Baixo</td>
                                    <td class="bg-warning bg-opacity-25">Médio</td>
                                    <td class="bg-warning bg-opacity-50">Alto</td>
                                    <td class="bg-danger bg-opacity-25">Alto</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold bg-light">Baixa</td>
                                    <td class="bg-success bg-opacity-10">Muito Baixo</td>
                                    <td class="bg-success bg-opacity-25">Baixo</td>
                                    <td class="bg-success bg-opacity-50">Baixo</td>
                                    <td class="bg-warning bg-opacity-25">Médio</td>
                                    <td class="bg-warning bg-opacity-50">Alto</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold bg-light">Muito Baixa</td>
                                    <td class="bg-success bg-opacity-10">Muito Baixo</td>
                                    <td class="bg-success bg-opacity-10">Muito Baixo</td>
                                    <td class="bg-success bg-opacity-25">Baixo</td>
                                    <td class="bg-success bg-opacity-50">Baixo</td>
                                    <td class="bg-warning bg-opacity-25">Médio</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mb-0">
                        <p class="fw-bold small mb-1"><i class="bi bi-info-circle me-1"></i>Como Interpretar:</p>
                        <ul class="x-small mb-0">
                            <li><strong class="text-danger">Crítico/Alto:</strong> Requer ação imediata e plano de mitigação robusto</li>
                            <li><strong class="text-warning">Médio:</strong> Monitorar atentamente e preparar plano de resposta</li>
                            <li><strong class="text-success">Baixo/Muito Baixo:</strong> Aceitar e revisar periodicamente</li>
                        </ul>
                    </div>
                </div>

                {{-- Categorias de Riscos --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-tags me-2"></i>{{ __('Categorias de Riscos') }}
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-2 border-danger h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="icon-circle-mini bg-danger bg-opacity-10 text-danger">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-danger">Operacional</h6>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        Falhas em processos, sistemas, pessoas ou eventos externos.
                                        <br><strong>Ex:</strong> Queda de sistema crítico, rotatividade de pessoal-chave
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-2 border-warning h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="icon-circle-mini bg-warning bg-opacity-10 text-warning">
                                            <i class="bi bi-currency-dollar"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-warning">Financeiro</h6>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        Mudanças econômicas, orçamentárias ou fiscais.
                                        <br><strong>Ex:</strong> Contingenciamento orçamentário, inflação acima do previsto
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-2 border-info h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="icon-circle-mini bg-info bg-opacity-10 text-info">
                                            <i class="bi bi-briefcase"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-info">Estratégico</h6>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        Mudanças no ambiente político, social ou competitivo.
                                        <br><strong>Ex:</strong> Nova legislação que altera competências, mudança de prioridades governamentais
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-2 border-primary h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="icon-circle-mini bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-shield-check"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-primary">Conformidade</h6>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        Não cumprimento de leis, normas ou regulamentos.
                                        <br><strong>Ex:</strong> Multas por descumprimento de LGPD, processos de auditoria desfavoráveis
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Estratégias de Resposta --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-shield-fill-check me-2"></i>{{ __('Estratégias de Resposta a Riscos') }}
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-2 text-danger">
                                        <i class="bi bi-x-circle me-1"></i>Evitar
                                    </h6>
                                    <p class="x-small text-muted mb-0">
                                        Eliminar completamente o risco mudando o plano.
                                        <br><strong>Ex:</strong> Cancelar projeto com risco regulatório inaceitável
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-2 text-warning">
                                        <i class="bi bi-arrow-down-circle me-1"></i>Mitigar
                                    </h6>
                                    <p class="x-small text-muted mb-0">
                                        Reduzir probabilidade ou impacto com ações preventivas.
                                        <br><strong>Ex:</strong> Criar backup de sistema crítico, treinar equipe redundante
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-2 text-info">
                                        <i class="bi bi-arrow-right-circle me-1"></i>Transferir
                                    </h6>
                                    <p class="x-small text-muted mb-0">
                                        Passar o risco para terceiros (seguros, contratos).
                                        <br><strong>Ex:</strong> Contratar seguro, terceirizar atividade arriscada
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-2 text-success">
                                        <i class="bi bi-check-circle me-1"></i>Aceitar
                                    </h6>
                                    <p class="x-small text-muted mb-0">
                                        Aceitar conscientemente riscos baixos e monitorar.
                                        <br><strong>Ex:</strong> Riscos muito improváveis ou de baixo impacto
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Exemplo Prático --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-star me-2"></i>{{ __('Exemplo de Risco Estratégico Completo') }}
                    </h6>

                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="icon-circle-mini bg-danger bg-opacity-10 text-danger">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <h6 class="fw-bold mb-0">Contingenciamento Orçamentário Abrupto</h6>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="small mb-1 text-muted"><strong>Descrição:</strong></p>
                                    <p class="x-small mb-3">Redução inesperada de 30% do orçamento aprovado devido a ajuste fiscal</p>

                                    <p class="small mb-1 text-muted"><strong>Categoria:</strong></p>
                                    <p class="x-small mb-3">Financeiro</p>

                                    <p class="small mb-1 text-muted"><strong>Objetivo Afetado:</strong></p>
                                    <p class="x-small mb-0">"Implementar novo sistema de gestão até dez/2025"</p>
                                </div>

                                <div class="col-md-6">
                                    <p class="small mb-1 text-muted"><strong>Probabilidade:</strong></p>
                                    <p class="x-small mb-3">Alta (histórico de contingenciamentos nos últimos 3 anos)</p>

                                    <p class="small mb-1 text-muted"><strong>Impacto:</strong></p>
                                    <p class="x-small mb-3">Muito Alto (inviabiliza o projeto)</p>

                                    <p class="small mb-1 text-muted"><strong>Nível de Risco:</strong></p>
                                    <p class="x-small mb-0"><span class="badge bg-danger">Crítico</span></p>
                                </div>

                                <div class="col-12">
                                    <div class="alert alert-warning mb-0 py-2">
                                        <p class="fw-bold small mb-1"><i class="bi bi-shield-check me-1"></i>Plano de Mitigação:</p>
                                        <ul class="x-small mb-0">
                                            <li>Dividir projeto em fases menores e priorizadas</li>
                                            <li>Buscar fontes alternativas de financiamento (parcerias, convênios)</li>
                                            <li>Manter diálogo constante com área orçamentária para antecipar cortes</li>
                                            <li>Criar versão "mínima viável" do sistema que custe 50% menos</li>
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
                        <i class="bi bi-trophy me-2"></i>{{ __('Boas Práticas de Gestão de Riscos') }}
                    </h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Identifique riscos continuamente</p>
                                    <p class="x-small text-muted mb-0">Revisão trimestral mínima. Novos riscos surgem com mudanças no ambiente</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Envolva especialistas</p>
                                    <p class="x-small text-muted mb-0">Quem vive o processo conhece os riscos melhor que gestores. Ouça a equipe</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Priorize pelo nível de risco</p>
                                    <p class="x-small text-muted mb-0">Não dá para mitigar tudo. Foque nos riscos críticos e altos primeiro</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Documente planos de resposta</p>
                                    <p class="x-small text-muted mb-0">Se o risco se materializar, a equipe deve saber exatamente o que fazer</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Monitore indicadores de alerta precoce</p>
                                    <p class="x-small text-muted mb-0">Ex: Para risco de atraso, monitore % de entregas concluídas no prazo semanalmente</p>
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

    {{-- Mentor de IA --}}
    @if($organizacaoId && $aiEnabled)
        <div class="ai-mentor-wrapper animate-fade-in">
            <button wire:click="pedirAjudaIA" wire:loading.attr="disabled" class="ai-magic-button shadow-sm">
                <span wire:loading.remove wire:target="pedirAjudaIA">
                    <i class="bi bi-robot"></i> {{ __('Sugerir Riscos Estratégicos com IA') }}
                </span>
                <span wire:loading wire:target="pedirAjudaIA">
                    <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Analisando Objetivos Estratégicos...') }}
                </span>
            </button>

            @if($aiSuggestion)
                <div class="ai-insight-card animate-fade-in">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-exclamation text-primary"></i>
                            <h6 class="fw-bold mb-0">{{ __('Riscos Identificados pelo Mentor IA') }}</h6>
                        </div>
                        <button type="button" class="btn-close small" style="font-size: 0.7rem;" wire:click="$set('aiSuggestion', '')"></button>
                    </div>
                    <div class="card-body">
                        @if(is_array($aiSuggestion))
                            <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                                @foreach($aiSuggestion as $sug)
                                    <div class="list-group-item d-flex align-items-start justify-content-between p-3 bg-light bg-opacity-25 hover-bg-white transition-all gap-3">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="fw-bold text-dark">{{ $sug['titulo'] }}</span>
                                                <span class="badge bg-secondary-subtle text-secondary small border-0">{{ $sug['categoria'] }}</span>
                                            </div>
                                            <p class="small text-muted mb-2">{{ $sug['descricao'] }}</p>
                                            <div class="p-2 rounded bg-info bg-opacity-10 border-start border-3 border-info">
                                                <small class="text-info fw-bold d-block mb-1"><i class="bi bi-shield-check me-1"></i>Mitigação Sugerida:</small>
                                                <small class="text-dark">{{ $sug['mitigacao'] }}</small>
                                            </div>
                                        </div>
                                        <button wire:click="aplicarSugestao('{{ $sug['titulo'] }}', '{{ $sug['categoria'] }}', '{{ $sug['descricao'] }}')" 
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold flex-shrink-0">
                                            <i class="bi bi-plus-lg me-1"></i> {{ __('Identificar') }}
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

    @if(!$organizacaoId)
        <div class="alert alert-warning shadow-sm border-0 d-flex align-items-center p-4" role="alert">
            <i class="bi bi-building-exclamation fs-2 me-4"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Selecione uma Organização</h5>
                <p class="mb-0">A gestão de riscos é contextualizada por unidade organizacional.</p>
            </div>
        </div>
    @else
        <!-- Filtros -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 bg-light rounded-3">
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" wire:model.live.debounce="search" class="form-control border-start-0 ps-0" placeholder="Buscar por título do risco...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="filtroCategoria" class="form-select">
                            <option value="">Todas as Categorias</option>
                            @foreach($categoriasOptions as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="filtroNivel" class="form-select">
                            <option value="">Todos Níveis</option>
                            <option value="Critico">Críticos (≥ 16)</option>
                            <option value="Baixo">Baixos (< 5)</option>
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <div wire:loading class="spinner-border text-primary spinner-border-sm" role="status"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela -->
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">ID / Título do Risco</th>
                            <th>Categoria</th>
                            <th class="text-center">Matriz (P x I)</th>
                            <th>Nível / Exposição</th>
                            <th>Responsável</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riscos as $risco)
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="text-muted small fw-mono">R-{{ str_pad($risco->num_codigo_risco, 3, '0', STR_PAD_LEFT) }}</span>
                                    <span class="fw-bold text-dark d-block mb-1">{{ $risco->dsc_titulo }}</span>
                                    <small class="text-muted d-block">{{ $risco->dsc_status }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-3 rounded-pill">{{ $risco->dsc_categoria }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <small class="text-muted" style="font-size: 0.65rem;">{{ $risco->num_probabilidade }} x {{ $risco->num_impacto }}</small>
                                        <span class="fw-bold fs-5">{{ $risco->num_nivel_risco }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $risco->getNivelRiscoBadgeClass() }} px-3 py-2 rounded-pill shadow-sm" style="min-width: 80px;">
                                        {{ $risco->getNivelRiscoLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-2 bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 24px; height: 24px; font-size: 0.7rem;">
                                            {{ substr($risco->responsavel->name ?? '?', 0, 1) }}
                                        </div>
                                        <small class="text-dark">{{ Str::limit($risco->responsavel->name ?? 'Não atribuído', 20) }}</small>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <li><h6 class="dropdown-header small text-uppercase">Gestão</h6></li>
                                            <li><a class="dropdown-item" href="{{ route('riscos.mitigacao', $risco->cod_risco) }}"><i class="bi bi-shield-check me-2 text-success"></i> Planos de Mitigação</a></li>
                                            <li><a class="dropdown-item" href="{{ route('riscos.ocorrencias', $risco->cod_risco) }}"><i class="bi bi-exclamation-octagon me-2 text-danger"></i> Registrar Ocorrência</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><button class="dropdown-item" wire:click="edit('{{ $risco->cod_risco }}')"><i class="bi bi-pencil me-2"></i> Editar</button></li>
                                            <li><button class="dropdown-item text-danger" wire:click="confirmDelete('{{ $risco->cod_risco }}')"><i class="bi bi-trash me-2"></i> Excluir</button></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-shield-exclamation fs-1 opacity-25 mb-3 d-block"></i>
                                    Nenhum risco identificado para esta organização.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3">
                {{ $riscos->links() }}
            </div>
        </div>
    @endif

    <!-- Modal Identificar/Editar Risco -->
    <div class="modal fade @if($showModal) show @endif" tabindex="-1" style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header gradient-theme text-white border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-shield-plus me-2"></i> {{ $riscoId ? 'Configurar Risco Estratégico' : 'Identificar Novo Risco' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4 bg-light bg-opacity-50">
                        <div class="row g-4">
                            <!-- Bloco 1: Identificação -->
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Identificação do Risco</h6>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Título do Risco</label>
                                            <input type="text" wire:model="form.dsc_titulo" class="form-control @error('form.dsc_titulo') is-invalid @enderror" placeholder="Ex: Perda de pessoal qualificado">
                                            @error('form.dsc_titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Descrição Detalhada</label>
                                            <textarea wire:model="form.txt_descricao" class="form-control" rows="3" placeholder="Descreva o evento de risco..."></textarea>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Categoria</label>
                                                <select wire:model="form.dsc_categoria" class="form-select">
                                                    @foreach($categoriasOptions as $cat)
                                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Status Atual</label>
                                                <select wire:model="form.dsc_status" class="form-select">
                                                    @foreach($statusOptions as $st)
                                                        <option value="{{ $st }}">{{ $st }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Causas e Consequências</h6>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Causas (O que gera este risco?)</label>
                                            <textarea wire:model="form.txt_causas" class="form-control" rows="2" placeholder="Ex: Salários abaixo do mercado, falta de plano de carreira..."></textarea>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Consequências (O que acontece se o risco materializar?)</label>
                                            <textarea wire:model="form.txt_consequencias" class="form-control" rows="2" placeholder="Ex: Atraso nos projetos estratégicos, redução da qualidade..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bloco 2: Avaliação e Vínculos -->
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Avaliação (Matriz 5x5)</h6>
                                        
                                        <div class="mb-4">
                                            <label class="form-label text-muted small text-uppercase fw-bold d-block">Probabilidade (1 a 5)</label>
                                            <input type="range" class="form-range" min="1" max="5" step="1" wire:model.live="form.num_probabilidade">
                                            <div class="d-flex justify-content-between px-1">
                                                @foreach(['1','2','3','4','5'] as $v) <small class="text-muted" style="font-size: 0.6rem;">{{$v}}</small> @endforeach
                                            </div>
                                            <div class="text-center fw-bold text-primary">{{ $form['num_probabilidade'] }}</div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label text-muted small text-uppercase fw-bold d-block">Impacto (1 a 5)</label>
                                            <input type="range" class="form-range" min="1" max="5" step="1" wire:model.live="form.num_impacto">
                                            <div class="d-flex justify-content-between px-1">
                                                @foreach(['1','2','3','4','5'] as $v) <small class="text-muted" style="font-size: 0.6rem;">{{$v}}</small> @endforeach
                                            </div>
                                            <div class="text-center fw-bold text-primary">{{ $form['num_impacto'] }}</div>
                                        </div>

                                        <div class="p-3 bg-light rounded-3 text-center border">
                                            <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.6rem;">Nível de Risco Calculado</small>
                                            <h2 class="fw-bold mb-0 {{ ($form['num_probabilidade'] * $form['num_impacto']) >= 16 ? 'text-danger' : 'text-dark' }}">
                                                {{ $form['num_probabilidade'] * $form['num_impacto'] }}
                                            </h2>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Responsável</h6>
                                        <div class="mb-0">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Monitorado por:</label>
                                            <select wire:model="form.cod_responsavel_monitoramento" class="form-select @error('form.cod_responsavel_monitoramento') is-invalid @enderror">
                                                <option value="">Selecione...</option>
                                                @foreach($usuarios as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('form.cod_responsavel_monitoramento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Vínculo Estratégico</h6>
                                        <div class="mb-0 overflow-auto" style="max-height: 200px;">
                                            @foreach($objetivos as $obj)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="{{ $obj->cod_objetivo }}" 
                                                           wire:model="form.objetivos_vinculados" id="obj_{{ $obj->cod_objetivo }}">
                                                    <label class="form-check-label small" for="obj_{{ $obj->cod_objetivo }}">
                                                        {{ Str::limit($obj->nom_objetivo, 50) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0 bg-light bg-opacity-50">
                        <button type="button" class="btn btn-light px-4" wire:click="$set('showModal', false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-4 py-2 fw-bold">
                            <i class="bi bi-save me-2"></i>Salvar Risco
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
                    <i class="bi bi-shield-x"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold text-dark">{{ __('Remover Risco') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Ação irreversível') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation text-start">
                <p class="mb-2 text-dark">
                    {{ __('Tem certeza que deseja excluir este risco estratégico?') }}
                </p>
                <div class="alert alert-warning bg-warning-subtle border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção:</strong> Os planos de mitigação e ocorrências vinculadas também serão afetados.
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDeleteModal', false)" wire:loading.attr="disabled" class="btn-modern">
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
</div>
