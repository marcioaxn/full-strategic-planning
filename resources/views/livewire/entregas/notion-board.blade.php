<div class="notion-board" wire:poll.5s="poll">
    {{-- Header --}}
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('planos.index') }}" class="text-decoration-none">Planos de Ação</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Entregas</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0 d-flex align-items-center gap-2">
                    <span class="notion-icon">📋</span>
                    {{ $plano->dsc_plano_de_acao }}
                </h2>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="text-muted small fw-medium">
                        <i class="bi bi-building me-1"></i>{{ $plano->organizacao?->nom_organizacao }}
                    </span>
                </div>
            </div>
            @can('update', $plano)
                <button wire:click="openEditModal" class="btn btn-primary gradient-theme-btn">
                    <i class="bi bi-plus-lg me-2"></i>Nova Entrega
                </button>
            @endcan
        </div>
    </x-slot>

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
    <div class="notion-content">
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

    {{-- Deliverables Help Section (Educational Pattern) --}}
    <div class="card card-modern mt-5 border-0 shadow-sm educational-card-gradient animate-fade-in">
        <div class="card-body p-4 text-white">
            <div class="row g-4">
                {{-- Main Explanation --}}
                <div class="col-12">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="flex-shrink-0">
                            <div class="icon-circle bg-white bg-opacity-25">
                                <i class="bi bi-check2-all fs-3 text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-2 text-white">{{ __('O que são Entregas?') }}</h5>
                            <p class="mb-0 text-white-50" style="line-height: 1.6;">
                                As <strong>Entregas</strong> são os passos concretos e operacionais necessários para concluir um Plano de Ação. Elas transformam grandes projetos em tarefas gerenciáveis, permitindo o acompanhamento diário do progresso, a distribuição de responsabilidades e a identificação de gargalos.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Tips Grid --}}
                <div class="col-md-4">
                    <div class="bg-white bg-opacity-10 rounded-3 p-3 h-100">
                        <h6 class="fw-bold text-white mb-2"><i class="bi bi-kanban me-2"></i>Gestão Visual (Kanban)</h6>
                        <p class="small mb-0 opacity-75">Arraste os cartões entre as colunas para atualizar o status. Use o <strong>"Não Iniciado"</strong> para o backlog, <strong>"Em Andamento"</strong> para o que está sendo feito agora e <strong>"Concluído"</strong> para o que já foi entregue.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="bg-white bg-opacity-10 rounded-3 p-3 h-100">
                        <h6 class="fw-bold text-white mb-2"><i class="bi bi-people me-2"></i>Responsabilidade</h6>
                        <p class="small mb-0 opacity-75">Uma entrega sem responsável tende a não ser concluída. Atribua um ou mais usuários a cada cartão para garantir que todos saibam exatamente o que devem fazer.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="bg-white bg-opacity-10 rounded-3 p-3 h-100">
                        <h6 class="fw-bold text-white mb-2"><i class="bi bi-alarm me-2"></i>Prazos e Prioridades</h6>
                        <p class="small mb-0 opacity-75">Fique atento às cores! Entregas com <strong>prazos vencidos</strong> ganham destaque em vermelho. Utilize os níveis de prioridade para focar o esforço da equipe no que é mais crítico.</p>
                    </div>
                </div>
            </div>
        </div>
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

    {{-- Modal de Edição Completa --}}
    @if($showEditModal)
        <div class="modal fade show" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" wire:click.self="closeEditModal">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg notion-modal">
                    <div class="modal-header gradient-theme text-white border-0">
                        <h5 class="modal-title fw-bold">
                            {{ $editEntregaId ? 'Editar Entrega' : 'Nova Entrega' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeEditModal"></button>
                    </div>
                    <form wire:submit.prevent="salvarEntrega">
                        <div class="modal-body p-4">
                            {{-- Título --}}
                            <div class="mb-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Título da Entrega</label>
                                <textarea 
                                    wire:model="editTitulo" 
                                    class="form-control @error('editTitulo') is-invalid @enderror" 
                                    rows="2"
                                    placeholder="Descreva a entrega..."
                                ></textarea>
                                @error('editTitulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row g-3">
                                {{-- Status --}}
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Status</label>
                                    <select wire:model="editStatus" class="form-select @error('editStatus') is-invalid @enderror">
                                        @foreach(\App\Models\ActionPlan\Entrega::STATUS_OPTIONS as $status)
                                            <option value="{{ $status }}">{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    @error('editStatus') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Prioridade --}}
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Prioridade</label>
                                    <select wire:model="editPrioridade" class="form-select @error('editPrioridade') is-invalid @enderror">
                                        @foreach(\App\Models\ActionPlan\Entrega::PRIORIDADE_OPTIONS as $key => $info)
                                            <option value="{{ $key }}">{{ $info['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('editPrioridade') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Prazo --}}
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Prazo</label>
                                    <input 
                                        type="date" 
                                        wire:model="editPrazo" 
                                        class="form-control @error('editPrazo') is-invalid @enderror"
                                    >
                                    @error('editPrazo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Responsáveis (Múltiplo) --}}
                                <div class="col-12">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Responsáveis</label>
                                    <div class="card bg-light border-0">
                                        <div class="card-body p-2" style="max-height: 150px; overflow-y: auto;">
                                            <div class="row g-2">
                                                @foreach($usuarios as $usuario)
                                                    <div class="col-md-4 col-sm-6">
                                                        <div class="form-check">
                                                            <input 
                                                                class="form-check-input" 
                                                                type="checkbox" 
                                                                value="{{ $usuario->id }}" 
                                                                id="user-{{ $usuario->id }}"
                                                                wire:model="editResponsaveis"
                                                            >
                                                            <label class="form-check-label small text-truncate w-100" for="user-{{ $usuario->id }}">
                                                                {{ $usuario->name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @error('editResponsaveis') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                {{-- Tipo --}}
                                <div class="col-12">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Tipo de Bloco</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach(\App\Models\ActionPlan\Entrega::TIPO_OPTIONS as $key => $info)
                                            <label class="notion-type-option {{ $editTipo === $key ? 'active' : '' }}">
                                                <input type="radio" wire:model="editTipo" value="{{ $key }}" class="d-none">
                                                <i class="bi bi-{{ $info['icon'] }} me-1"></i>
                                                {{ $info['label'] }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="button" class="btn btn-light px-4" wire:click="closeEditModal">Cancelar</button>
                            <button type="submit" class="btn btn-primary gradient-theme-btn px-4">
                                <i class="bi bi-check-lg me-1"></i> Salvar
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

    {{-- Estilos inline temporários (serão movidos para SCSS) --}}
    <style>
        .notion-board {
            --notion-bg: #ffffff;
            --notion-text: #37352f;
            --notion-text-muted: #9b9a97;
            --notion-border: #e4e4e4;
            --notion-hover: #f7f7f5;
        }
        /* ... restante do estilo ... */
        .notion-icon {
            font-size: 1.25rem;
        }

        .notion-progress-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }

        .notion-modal {
            border-radius: 12px;
        }

        .notion-input {
            background: transparent;
            font-size: 1.25rem;
            padding: 0;
        }

        .notion-input:focus {
            box-shadow: none;
            background: transparent;
        }

        .notion-status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .notion-type-option {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid var(--notion-border);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .notion-type-option:hover {
            background: var(--notion-hover);
        }

        .notion-type-option.active {
            background: var(--bs-primary);
            color: white;
            border-color: var(--bs-primary);
        }

        /* View Switcher */
        .notion-view-switcher {
            display: flex;
            gap: 0.25rem;
            background: #f1f1f0;
            padding: 4px;
            border-radius: 8px;
        }

        .notion-view-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border: none;
            background: transparent;
            border-radius: 6px;
            font-size: 0.875rem;
            color: var(--notion-text-muted);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .notion-view-btn:hover {
            background: rgba(255,255,255,0.5);
            color: var(--notion-text);
        }

        .notion-view-btn.active {
            background: white;
            color: var(--notion-text);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Kanban */
        .notion-kanban {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding-bottom: 16px;
        }

        .notion-kanban-column {
            min-width: 280px;
            max-width: 280px;
            flex-shrink: 0;
            background: #f7f7f5;
            border-radius: 8px;
            padding: 8px;
        }

        .notion-kanban-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 8px 12px;
        }

        .notion-kanban-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--notion-text);
        }

        .notion-kanban-count {
            font-size: 0.75rem;
            color: var(--notion-text-muted);
            font-weight: normal;
        }

        .notion-kanban-cards {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-height: 100px;
        }

        .notion-card {
            background: white;
            border-radius: 6px;
            padding: 10px 12px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid transparent;
        }

        .notion-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            border-color: var(--notion-border);
        }

        .notion-card-title {
            font-size: 0.875rem;
            color: var(--notion-text);
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .notion-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 0.75rem;
        }

        .notion-card-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.7rem;
        }

        .notion-card-avatar {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--bs-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            font-weight: 600;
        }

        .notion-add-card {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            color: var(--notion-text-muted);
            font-size: 0.875rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
            margin-top: 4px;
        }

        .notion-add-card:hover {
            background: rgba(0,0,0,0.03);
            color: var(--notion-text);
        }

        /* Labels */
        .notion-label {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        /* Prioridade badges */
        .notion-priority-baixa { background: #e3e2e080; color: #6b6b6b; }
        .notion-priority-media { background: #fdecc880; color: #9a6700; }
        .notion-priority-alta { background: #ffe2dd80; color: #c4311e; }
        .notion-priority-urgente { background: #e03e3e; color: white; }

        /* Side Panel (Detalhes) */
        .notion-side-panel {
            position: fixed;
            top: 0;
            right: 0;
            width: 480px;
            max-width: 100%;
            height: 100vh;
            background: white;
            box-shadow: -4px 0 24px rgba(0,0,0,0.15);
            z-index: 1050;
            overflow-y: auto;
            animation: slideIn 0.2s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }

        .notion-side-panel-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
            z-index: 1040;
        }

        /* Lixeira */
        .notion-trash-banner {
            background: linear-gradient(135deg, #ffe2dd 0%, #fff 100%);
            border-left: 4px solid #e03e3e;
        }

        /* Arquivados */
        .notion-archived-banner {
            background: linear-gradient(135deg, #f1f1f0 0%, #fff 100%);
            border-left: 4px solid #9b9a97;
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .notion-kanban {
                flex-direction: column;
            }
            
            .notion-kanban-column {
                min-width: 100%;
                max-width: 100%;
            }

            .notion-side-panel {
                width: 100%;
            }
        }
    </style>
</div>
