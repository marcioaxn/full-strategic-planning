<div class="notion-board" wire:poll.5s="poll">
    {{-- Header Interno --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('planos.index') }}" class="text-decoration-none">Planos de Ação</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Entregas</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                <h2 class="h4 fw-bold mb-0 d-flex align-items-center gap-2">
                    <span class="notion-icon">📋</span>
                    {{ $plano->dsc_plano_de_acao }}
                </h2>
            </div>
            <div class="d-flex align-items-center gap-2 mt-1">
                <span class="text-muted small fw-medium">
                    <i class="bi bi-building me-1"></i>{{ $plano->organizacao?->nom_organizacao }}
                </span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @can('update', $plano)
                <button wire:click="openEditModal" class="btn btn-primary gradient-theme-btn">
                    <i class="bi bi-plus-lg me-2"></i>Nova Entrega
                </button>
            @endcan
        </div>
    </div>

    {{-- Seção Educativa: O que são Entregas/Marcos --}}
    <div class="card border-0 shadow-sm mb-4 educational-card-gradient" x-data="{ expanded: false }">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle bg-white bg-opacity-25">
                        <i class="bi bi-book-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">
                            <i class="bi bi-mortarboard me-2"></i>{{ __('O que são Entregas?') }}
                        </h5>
                        <p class="mb-0 text-white-50 small">
                            {{ __('Aprenda a gerenciar marcos e entregas com metodologia Kanban') }}
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
                        <i class="bi bi-info-circle me-2"></i>{{ __('O que são Entregas?') }}
                    </h6>
                    <p class="text-muted mb-3">
                        <strong>Entregas</strong> (ou Marcos) são os <strong>passos operacionais concretos</strong> que compõem um Plano de Ação.
                        Enquanto o Plano de Ação define o "o quê" em alto nível, as Entregas detalham o "como" passo a passo,
                        transformando grandes projetos em <strong>tarefas gerenciáveis</strong>.
                    </p>
                    <p class="text-muted mb-0">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        <strong>Por que usar Entregas?</strong> Elas permitem acompanhamento diário do progresso, distribuição clara de responsabilidades,
                        identificação rápida de gargalos e celebração de conquistas incrementais.
                    </p>
                </div>

                {{-- Metodologia Kanban --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-kanban me-2"></i>{{ __('Gestão Visual com Kanban') }}
                    </h6>
                    <p class="small text-muted mb-3">
                        O sistema <strong>Kanban</strong> organiza entregas em 3 colunas que representam o fluxo de trabalho:
                    </p>

                    <div class="row g-3 mb-4">
                        {{-- Não Iniciado --}}
                        <div class="col-md-4">
                            <div class="card border-2 border-secondary h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-secondary bg-opacity-10 text-secondary">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-secondary">Não Iniciado</h6>
                                    </div>
                                    <p class="small text-muted mb-3">
                                        <strong>Backlog:</strong> Entregas planejadas que ainda não foram iniciadas. Aguardam priorização ou recursos disponíveis.
                                    </p>
                                    <div class="bg-light p-2 rounded">
                                        <p class="x-small mb-0 text-muted">
                                            <strong>Exemplo:</strong> "Elaborar Termo de Referência para licitação"
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Em Andamento --}}
                        <div class="col-md-4">
                            <div class="card border-2 border-primary h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-play-circle"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-primary">Em Andamento</h6>
                                    </div>
                                    <p class="small text-muted mb-3">
                                        <strong>WIP (Work in Progress):</strong> Entregas em execução ativa. Limite a quantidade para evitar sobrecarga da equipe.
                                    </p>
                                    <div class="bg-light p-2 rounded">
                                        <p class="x-small mb-0 text-muted">
                                            <strong>Exemplo:</strong> "Realizar treinamento da equipe técnica"
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Concluído --}}
                        <div class="col-md-4">
                            <div class="card border-2 border-success h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-check-circle"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-success">Concluído</h6>
                                    </div>
                                    <p class="small text-muted mb-3">
                                        <strong>Done:</strong> Entregas finalizadas e validadas. O que está aqui já foi entregue e aprovado.
                                    </p>
                                    <div class="bg-light p-2 rounded">
                                        <p class="x-small mb-0 text-muted">
                                            <strong>Exemplo:</strong> "Contrato assinado com fornecedor"
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-lightbulb-fill"></i>
                            <div>
                                <p class="fw-bold small mb-1">Dica de Fluxo Kanban</p>
                                <p class="x-small mb-0">
                                    Arraste e solte os cartões entre as colunas para atualizar o status automaticamente.
                                    Evite ter muitas entregas "Em Andamento" ao mesmo tempo (ideal: 2-3 por pessoa).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Anatomia de uma Boa Entrega --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-journal-text me-2"></i>{{ __('Elementos de uma Entrega Completa') }}
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <h6 class="fw-bold mb-0 small">Título claro e específico</h6>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        Use verbos de ação e seja específico. <br>
                                        ✅ "Publicar edital de licitação no DOU"<br>
                                        ❌ "Trabalhar na licitação"
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <h6 class="fw-bold mb-0 small">Responsável definido</h6>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        Atribua cada entrega a uma pessoa específica.<br>
                                        Entregas sem dono tendem a não serem concluídas.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <h6 class="fw-bold mb-0 small">Prazo realista</h6>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        Defina data de início e conclusão. <br>
                                        Entregas com prazo vencido aparecem em destaque vermelho.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <h6 class="fw-bold mb-0 small">Prioridade definida</h6>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        Use Alta/Média/Baixa para orientar a equipe sobre<br>
                                        o que deve ser feito primeiro.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Exemplo Prático --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-star me-2"></i>{{ __('Exemplo: Plano "Modernizar Atendimento" dividido em Entregas') }}
                    </h6>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-bold small">Entrega</th>
                                    <th class="fw-bold small">Responsável</th>
                                    <th class="fw-bold small">Prazo</th>
                                    <th class="fw-bold small">Status</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <tr>
                                    <td>1. Mapear processos atuais de atendimento</td>
                                    <td>Ana Silva (Processos)</td>
                                    <td>15/02 - 28/02</td>
                                    <td><span class="badge bg-success">Concluído</span></td>
                                </tr>
                                <tr>
                                    <td>2. Elaborar Termo de Referência para sistema</td>
                                    <td>Carlos Souza (TI)</td>
                                    <td>01/03 - 15/03</td>
                                    <td><span class="badge bg-primary">Em Andamento</span></td>
                                </tr>
                                <tr>
                                    <td>3. Publicar licitação no portal de compras</td>
                                    <td>Maria Santos (Compras)</td>
                                    <td>20/03 - 25/03</td>
                                    <td><span class="badge bg-secondary">Não Iniciado</span></td>
                                </tr>
                                <tr>
                                    <td>4. Contratar fornecedor vencedor</td>
                                    <td>Maria Santos (Compras)</td>
                                    <td>15/04 - 30/04</td>
                                    <td><span class="badge bg-secondary">Não Iniciado</span></td>
                                </tr>
                                <tr>
                                    <td>5. Treinar equipe no novo sistema</td>
                                    <td>João Pereira (RH)</td>
                                    <td>15/05 - 30/05</td>
                                    <td><span class="badge bg-secondary">Não Iniciado</span></td>
                                </tr>
                                <tr>
                                    <td>6. Realizar piloto em uma unidade</td>
                                    <td>Ana Silva (Processos)</td>
                                    <td>01/06 - 30/06</td>
                                    <td><span class="badge bg-secondary">Não Iniciado</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Dicas Profissionais --}}
                <div>
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-trophy me-2"></i>{{ __('Boas Práticas de Gestão de Entregas') }}
                    </h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Granularidade adequada</p>
                                    <p class="x-small text-muted mb-0">Entregas devem durar entre 3-15 dias. Muito curtas geram burocracia; muito longas dificultam acompanhamento</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Atualize status diariamente</p>
                                    <p class="x-small text-muted mb-0">Reserve 5 minutos por dia para mover cartões. Kanban desatualizado perde utilidade</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Evite sobrecarga (WIP limit)</p>
                                    <p class="x-small text-muted mb-0">Limite entregas "Em Andamento". É melhor concluir 2 bem feitas do que iniciar 10 e não terminar nenhuma</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Celebre conquistas</p>
                                    <p class="x-small text-muted mb-0">Cada entrega concluída é um marco. Reconheça o progresso da equipe publicamente</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Realize reuniões rápidas (Daily Standup)</p>
                                    <p class="x-small text-muted mb-0">Encontros de 15min diários ou semanais para revisar o board: "O que fiz?", "O que vou fazer?", "Há impedimentos?"</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    @include('livewire.entregas.partials.toolbar')

    {{-- Barra de Progresso --}}
    <div class="card border-0 shadow-sm mb-4 overflow-hidden notion-progress-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0">Progresso Consolidado</h6>
                <span class="fw-bold text-primary fs-5">@brazil_percent($progresso, 1)</span>
            </div>
            <div class="progress rounded-pill" style="height: 10px;">
                <div class="progress-bar gradient-theme" 
                     role="progressbar" 
                     style="width: {{ $progresso }}%; transition: width 0.5s ease-in-out;" 
                     aria-valuenow="{{ $progresso }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    {{-- Conteúdo Principal (Views) --}}
    <div class="notion-content position-relative">
        {{-- Overlay de Loading durante transição de views --}}
        <div
            wire:loading.flex
            wire:target="setView, calendarioAnterior, calendarioProximo, calendarioHoje, calendarioIrPara, timelineAnterior, timelineProximo, timelineHoje, timelineZoomIn, timelineZoomOut"
            class="notion-view-loading-overlay"
        >
            <div class="notion-view-spinner">
                <div class="spinner-wrapper">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-muted fw-medium">Carregando visualização...</p>
            </div>
        </div>

        @switch($view)
            @case('kanban')
                @include('livewire.entregas.views.kanban')
                @break
            @case('lista')
                @include('livewire.entregas.views.lista')
                @break
            @case('timeline')
                @include('livewire.entregas.views.timeline')
                @break
            @case('calendario')
                @include('livewire.entregas.views.calendario')
                @break
            @default
                @include('livewire.entregas.views.kanban')
        @endswitch
    </div>

    {{-- Modal de Criação Rápida --}}
    @if($showQuickAdd)
        <div class="modal fade show" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" wire:click.self="closeQuickAdd">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg notion-modal">
                    <form wire:submit.prevent="criarRapido">
                        <div class="modal-body p-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="notion-status-dot" style="background-color: {{ \App\Models\ActionPlan\Entrega::STATUS_COLORS[$quickAddStatus] ?? '#e3e2e0' }}"></span>
                                <span class="text-muted small">{{ $quickAddStatus }}</span>
                            </div>
                            <input 
                                type="text" 
                                wire:model="quickAddTitulo" 
                                class="form-control form-control-lg border-0 notion-input @error('quickAddTitulo') is-invalid @enderror" 
                                placeholder="Digite o título da entrega..."
                                autofocus
                            >
                            @error('quickAddTitulo') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="button" class="btn btn-light" wire:click="closeQuickAdd">Cancelar</button>
                            <button type="submit" class="btn btn-primary gradient-theme-btn">
                                <i class="bi bi-plus-lg me-1"></i> Criar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Edição/Criação Premium --}}
    @if($showEditModal)
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5); z-index: 1055;" wire:click.self="closeEditModal">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    
                    {{-- Header Premium --}}
                    <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle-mini bg-white bg-opacity-25 text-white">
                                <i class="bi bi-{{ $editEntregaId ? 'pencil-square' : 'plus-circle' }}"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">{{ $editEntregaId ? 'Editar Entrega' : 'Nova Entrega' }}</h5>
                                <p class="mb-0 small text-white-50">Gerenciamento operacional do plano</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeEditModal"></button>
                    </div>

                    <form wire:submit.prevent="salvarEntrega">
                        <div class="modal-body p-4 bg-white">
                            <div class="row g-4">
                                
                                {{-- Coluna Principal: Definição --}}
                                <div class="col-lg-8">
                                    <div class="card border-0 bg-light rounded-4 h-100">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">O que será entregue?</h6>
                                            
                                            {{-- Título --}}
                                            <div class="mb-4">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Descrição da Entrega <span class="text-danger">*</span></label>
                                                <textarea 
                                                    wire:model="editTitulo" 
                                                    class="form-control form-control-lg bg-white border-0 shadow-sm @error('editTitulo') is-invalid @enderror" 
                                                    rows="3"
                                                    placeholder="Descreva a atividade ou marco de entrega..."
                                                ></textarea>
                                                @error('editTitulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="row g-3">
                                                {{-- Status --}}
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small text-uppercase fw-bold">Status Atual</label>
                                                    <div class="input-group shadow-sm" x-data="{ status: @entangle('editStatus') }">
                                                        <span class="input-group-text border-0 text-white" 
                                                              :class="{
                                                                  'bg-secondary': ['Não Iniciado', 'Suspenso', 'Cancelado'].includes(status),
                                                                  'bg-primary': status == 'Em Andamento',
                                                                  'bg-success': status == 'Concluído',
                                                                  'bg-danger': status == 'Atrasado'
                                                              }">
                                                            <i class="bi bi-activity"></i>
                                                        </span>
                                                        <select wire:model.live="editStatus" class="form-select bg-white border-0 fw-bold">
                                                            @foreach(\App\Models\ActionPlan\Entrega::STATUS_OPTIONS as $status)
                                                                <option value="{{ $status }}">{{ $status }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- Prioridade --}}
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small text-uppercase fw-bold">Prioridade</label>
                                                    <div class="input-group shadow-sm">
                                                        <span class="input-group-text bg-white border-0 text-muted"><i class="bi bi-flag"></i></span>
                                                        <select wire:model="editPrioridade" class="form-select bg-white border-0 fw-bold">
                                                            @foreach(\App\Models\ActionPlan\Entrega::PRIORIDADE_OPTIONS as $key => $info)
                                                                <option value="{{ $key }}">{{ $info['label'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- Tipo (Apenas Edição) --}}
                                                @if($editEntregaId)
                                                    <div class="col-12 mt-3">
                                                        <label class="form-label text-muted small text-uppercase fw-bold">Tipo de Registro</label>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach(\App\Models\ActionPlan\Entrega::TIPO_OPTIONS as $key => $info)
                                                                <label class="notion-type-option border-0 shadow-sm {{ $editTipo === $key ? 'active shadow-lg' : 'bg-white' }}" style="cursor: pointer;">
                                                                    <input type="radio" wire:model="editTipo" value="{{ $key }}" class="d-none">
                                                                    <i class="bi bi-{{ $info['icon'] }} me-1"></i>
                                                                    {{ $info['label'] }}
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Coluna Lateral: Prazo e Responsáveis --}}
                                <div class="col-lg-4">
                                    {{-- Card Prazo --}}
                                    <div class="card border-0 bg-light rounded-4 mb-3">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-calendar-event me-2 text-primary"></i>Cronograma</h6>
                                            
                                            <div x-data="{
                                                init() {
                                                    flatpickr(this.$refs.prazoInput, {
                                                        dateFormat: 'Y-m-d',
                                                        altInput: true,
                                                        altFormat: 'd/m/Y',
                                                        locale: 'pt',
                                                        minDate: 'today',
                                                        onChange: (selectedDates, dateStr) => {
                                                            @this.set('editPrazo', dateStr);
                                                        }
                                                    });
                                                }
                                            }" wire:ignore>
                                                <label class="form-label small text-muted fw-bold text-uppercase">Data Limite (Prazo)</label>
                                                <div class="input-group shadow-sm">
                                                    <span class="input-group-text bg-white border-0 text-primary"><i class="bi bi-clock"></i></span>
                                                    <input x-ref="prazoInput" type="text" value="{{ $editPrazo }}" class="form-control bg-white border-0 fw-bold text-dark" placeholder="Selecione a data...">
                                                </div>
                                            </div>
                                            @error('editPrazo') <div class="text-danger x-small mt-1 text-end">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    {{-- Card Responsáveis --}}
                                    <div class="card border-0 bg-light rounded-4">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-people me-2 text-primary"></i>Equipe</h6>
                                            
                                            <label class="form-label small text-muted fw-bold text-uppercase">Atribuir a:</label>
                                            <div class="bg-white rounded-3 shadow-sm p-3" style="max-height: 250px; overflow-y: auto;">
                                                @foreach($usuarios as $usuario)
                                                    <div class="form-check mb-2">
                                                        <input 
                                                            class="form-check-input" 
                                                            type="checkbox" 
                                                            value="{{ $usuario->id }}" 
                                                            id="user-{{ $usuario->id }}"
                                                            wire:model="editResponsaveis"
                                                        >
                                                        <label class="form-check-label small text-dark fw-medium" for="user-{{ $usuario->id }}">
                                                            {{ $usuario->name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('editResponsaveis') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer Premium --}}
                        <div class="modal-footer border-0 p-4 bg-white rounded-bottom-4 shadow-top-sm">
                            <button type="button" class="btn btn-light px-4 rounded-pill fw-bold text-muted" wire:click="closeEditModal">Cancelar</button>
                            <button type="submit" class="btn btn-primary gradient-theme-btn px-5 rounded-pill shadow-sm hover-scale">
                                <i class="bi bi-check-lg me-2"></i>Salvar Entrega
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Detalhes (Side Panel) --}}
    @if($showDetails && $entregaDetalhe)
        @include('livewire.entregas.modals.detalhes', ['entrega' => $entregaDetalhe])
    @endif

    {{-- Modal de Labels --}}
    @include('livewire.entregas.modals.labels')

    {{-- Modal de Exclusão --}}
    <x-confirmation-modal wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="icon-circle-mini modal-icon-danger">
                    <i class="bi bi-trash3"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold text-dark">{{ $isPermanentDelete ? __('Excluir Permanentemente') : __('Excluir Entrega') }}</h5>
                    <p class="text-muted small mb-0">{{ $isPermanentDelete ? __('Esta ação é irreversível') : __('Mover para a lixeira') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation text-start">
                <p class="mb-2 text-dark">
                    {{ __('Tem certeza que deseja excluir esta entrega?') }}
                </p>
                <div class="alert alert-warning bg-warning-subtle border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ $isPermanentDelete ? __('Atenção: Os dados serão apagados definitivamente do banco de dados.') : __('Ela poderá ser restaurada da lixeira em até 24 horas.') }}
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDeleteModal', false)" wire:loading.attr="disabled" class="btn-modern">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-danger-button wire:click="excluir" wire:loading.attr="disabled" class="btn-delete-modern ms-2">
                <span wire:loading.remove wire:target="excluir">
                    <i class="bi bi-trash3 me-1"></i>{{ __('Excluir') }}
                </span>
                <span wire:loading wire:target="excluir">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                </span>
            </x-danger-button>
                </x-slot>
            </x-confirmation-modal>
        
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
                            <h3 class="fw-bold text-dark mb-3">Entrega Registrada!</h3>
                            <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.6;">
                                A entrega <strong class="text-primary">"{{ $createdDeliverableName }}"</strong><br>
                                foi vinculada com sucesso ao plano:<br>
                                <span class="fst-italic text-dark fw-bold">"{{ $plano->dsc_plano_de_acao }}"</span>
                            </p>
                            <button wire:click="closeSuccessModal" class="btn btn-primary gradient-theme-btn px-5 rounded-pill shadow hover-scale">
                                <i class="bi bi-check2-circle me-2"></i>Continuar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                .scale-in-center { animation: scale-in-center 0.5s cubic-bezier(0.250, 0.460, 0.450, 0.940) both; }
                @keyframes scale-in-center { 0% { transform: scale(0); opacity: 1; } 100% { transform: scale(1); opacity: 1; } }
            </style>
            @endif
        </div>
