<div>
    {{-- Page Header --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="header-icon gradient-theme-icon">
                    <i class="bi bi-compass-fill"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ __('Identidade Estratégica') }}</h1>
            </div>
            <p class="text-muted mb-0">
                @if($organizacaoNome)
                    {{ __('Identidade da organização:') }} <strong>{{ $organizacaoNome }}</strong>
                    @if($peiAtivo)
                        <span class="badge bg-success-subtle text-success ms-2">{{ $peiAtivo->dsc_pei }}</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger ms-2">{{ __('Sem Ciclo PEI Ativo') }}</span>
                    @endif
                @else
                    {{ __('Selecione uma organização para visualizar a identidade estratégica.') }}
                @endif
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div wire:loading.delay.short wire:target="salvar,habilitarEdicao,cancelar,adicionarValor,removerValor,editarValor,atualizarValor" class="text-primary">
                <span class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">{{ __('Carregando...') }}</span>
                </span>
            </div>

            @if($organizacaoId && !$isEditing && $peiAtivo)
                <x-action-button
                    variant="primary"
                    icon="pencil"
                    tooltip="{{ __('Editar identidade estratégica') }}"
                    wire:click="habilitarEdicao"
                    class="btn-action-primary gradient-theme-btn px-4"
                >
                    {{ __('Editar Missão/Visão') }}
                </x-action-button>
            @endif
        </div>
    </div>

    {{-- Mentor de IA --}}
    @if($organizacaoId && $peiAtivo && $aiEnabled)
        <div class="ai-mentor-wrapper animate-fade-in">
            <button wire:click="pedirAjudaIA" wire:loading.attr="disabled" class="ai-magic-button shadow-sm">
                <span wire:loading.remove wire:target="pedirAjudaIA">
                    <i class="bi bi-robot"></i> {{ __('Sugerir Missão, Visão e Valores com IA') }}
                </span>
                <span wire:loading wire:target="pedirAjudaIA">
                    <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Inspirando novas ideias...') }}
                </span>
            </button>

            @if($aiSuggestion)
                <div class="ai-insight-card animate-fade-in">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-robot text-primary"></i>
                            <h6 class="fw-bold mb-0">{{ __('Identidade Sugerida pelo Mentor IA') }}</h6>
                        </div>
                        <button type="button" class="btn-close small" style="font-size: 0.7rem;" wire:click="$set('aiSuggestion', '')"></button>
                    </div>
                    <div class="card-body">
                        @if(is_array($aiSuggestion))
                            {{-- Missão e Visão --}}
                            @if(!isset($aiSuggestion['missao_aplicada']))
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 border bg-light h-100">
                                            <h6 class="fw-bold text-primary small mb-2 text-uppercase">{{ __('Sugestão de Missão') }}</h6>
                                            <p class="small mb-0 italic">"{{ $aiSuggestion['missao'] }}"</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 border bg-light h-100">
                                            <h6 class="fw-bold text-success small mb-2 text-uppercase">{{ __('Sugestão de Visão') }}</h6>
                                            <p class="small mb-0 italic">"{{ $aiSuggestion['visao'] }}"</p>
                                        </div>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button wire:click="aplicarIdentidade" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                            <i class="bi bi-check-all me-1"></i> {{ __('Aplicar Missão e Visão') }}
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-success py-2 small mb-4 d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle-fill"></i> {{ __('Missão e Visão aplicadas com sucesso!') }}
                                </div>
                            @endif

                            {{-- Valores --}}
                            <h6 class="fw-bold text-dark small mb-3 text-uppercase border-bottom pb-2">{{ __('Valores Sugeridos') }}</h6>
                            <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                                @foreach($aiSuggestion['valores'] as $valor)
                                    <div class="list-group-item d-flex align-items-start justify-content-between p-3 bg-light bg-opacity-25 hover-bg-white transition-all gap-3">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark">{{ $valor['nome'] }}</div>
                                            <p class="small text-muted mb-0 mt-1">{{ $valor['descricao'] }}</p>
                                        </div>
                                        <button wire:click="adicionarValorSugerido('{{ $valor['nome'] }}', '{{ $valor['descricao'] }}')" 
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

    @if (session()->has('error'))
        <div class="alert alert-modern alert-danger alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert">
            <div class="alert-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <span class="flex-grow-1">{{ session('error') }}</span>
            <button type="button" class="btn-close btn-close-modern" data-bs-dismiss="alert" aria-label="{{ __('Fechar') }}"></button>
        </div>
    @endif

    @if(!$organizacaoId)
        {{-- Empty State: No Organization Selected --}}
        <div class="card card-modern">
            <div class="card-body p-5 text-center">
                <div class="empty-state">
                    <div class="empty-state-icon mb-3">
                        <i class="bi bi-building"></i>
                    </div>
                    <h5 class="empty-state-title">{{ __('Nenhuma organização selecionada') }}</h5>
                    <p class="empty-state-text">
                        {{ __('Selecione uma organização no menu superior para visualizar e editar a identidade estratégica.') }}
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="row g-4">
            {{-- Missão --}}
            <div class="col-12 col-lg-6">
                <div class="card card-modern h-100">
                    <div class="card-header border-0 bg-transparent">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-circle bg-primary-subtle text-primary">
                                <i class="bi bi-bullseye"></i>
                            </div>
                            <h5 class="mb-0 fw-bold">{{ __('Missão') }}</h5>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <p class="text-muted small mb-3">
                            {{ __('A razão de existência da organização. O propósito fundamental que guia todas as ações.') }}
                        </p>

                        @if($isEditing)
                            <textarea
                                class="form-control form-control-modern @error('missao') is-invalid @enderror"
                                rows="6"
                                placeholder="{{ __('Descreva a missão da organização...') }}"
                                wire:model="missao"
                            ></textarea>
                            @error('missao')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        @else
                            <div class="content-display p-3 rounded-3 bg-light border">
                                @if($missao)
                                    <p class="mb-0 text-body-emphasis" style="white-space: pre-wrap;">{{ $missao }}</p>
                                @else
                                    <p class="mb-0 text-muted fst-italic">{{ __('Missão não definida.') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Visão --}}
            <div class="col-12 col-lg-6">
                <div class="card card-modern h-100">
                    <div class="card-header border-0 bg-transparent">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-circle bg-success-subtle text-success">
                                <i class="bi bi-eye-fill"></i>
                            </div>
                            <h5 class="mb-0 fw-bold">{{ __('Visão') }}</h5>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <p class="text-muted small mb-3">
                            {{ __('O futuro desejado pela organização. O estado ideal que se busca alcançar.') }}
                        </p>

                        @if($isEditing)
                            <textarea
                                class="form-control form-control-modern @error('visao') is-invalid @enderror"
                                rows="6"
                                placeholder="{{ __('Descreva a visão da organização...') }}"
                                wire:model="visao"
                            ></textarea>
                            @error('visao')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        @else
                            <div class="content-display p-3 rounded-3 bg-light border">
                                @if($visao)
                                    <p class="mb-0 text-body-emphasis" style="white-space: pre-wrap;">{{ $visao }}</p>
                                @else
                                    <p class="mb-0 text-muted fst-italic">{{ __('Visão não definida.') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            @if($isEditing)
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-2">
                        <x-secondary-button wire:click="cancelar" wire:loading.attr="disabled" class="btn-modern">
                            <i class="bi bi-x-lg me-1"></i>{{ __('Cancelar') }}
                        </x-secondary-button>

                        <x-button type="button" wire:click="salvar" wire:loading.attr="disabled" class="btn-save-modern">
                            <span wire:loading.remove wire:target="salvar">
                                <i class="bi bi-check-lg me-1"></i>{{ __('Salvar Alterações') }}
                            </span>
                            <span wire:loading wire:target="salvar">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                {{ __('Salvando...') }}
                            </span>
                        </x-button>
                    </div>
                </div>
            @endif

            {{-- Valores --}}
            <div class="col-12">
                <div class="card card-modern">
                    <div class="card-header border-0 bg-transparent d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-circle bg-warning-subtle text-warning">
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <h5 class="mb-0 fw-bold">{{ __('Valores Organizacionais') }}</h5>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <p class="text-muted small mb-4">
                            {{ __('Os princípios fundamentais e crenças que orientam o comportamento e as decisões da organização.') }}
                        </p>

                        <div class="row g-4">
                            <!-- Lista de Valores -->
                            @foreach($valores as $valor)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100 border-0 shadow-sm bg-light hover-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="fw-bold text-primary mb-0">{{ $valor->nom_valor }}</h6>
                                                @if($peiAtivo)
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-icon btn-ghost-secondary rounded-circle" type="button" data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                                                            <li>
                                                                <button class="dropdown-item small" wire:click="editarValor('{{ $valor->cod_valor }}')">
                                                                    <i class="bi bi-pencil me-2"></i>Editar
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button class="dropdown-item small text-danger" wire:click="confirmDeleteValor('{{ $valor->cod_valor }}')">
                                                                    <i class="bi bi-trash me-2"></i>Remover
                                                                </button>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                            <p class="small text-muted mb-0">{{ $valor->dsc_valor }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Formulário de Adição/Edição -->
                            @if($peiAtivo)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100 border-2 border-dashed border-secondary bg-transparent">
                                        <div class="card-body d-flex flex-column justify-content-center">
                                            @if($isEditingValores)
                                                <div class="d-flex flex-column gap-2">
                                                    <input type="text" class="form-control form-control-sm" placeholder="Título do Valor" wire:model="novoValorTitulo">
                                                    <textarea class="form-control form-control-sm" rows="2" placeholder="Descrição do Valor" wire:model="novoValorDescricao"></textarea>
                                                    <div class="d-flex justify-content-end gap-1 mt-1">
                                                        <button class="btn btn-sm btn-secondary" wire:click="cancelarEdicaoValor">Cancelar</button>
                                                        <button class="btn btn-sm btn-primary" wire:click="atualizarValor">Salvar</button>
                                                    </div>
                                                </div>
                                            @elseif($novoValorTitulo || $novoValorDescricao)
                                                <div class="d-flex flex-column gap-2">
                                                    <input type="text" class="form-control form-control-sm" placeholder="Título do Valor" wire:model="novoValorTitulo">
                                                    <textarea class="form-control form-control-sm" rows="2" placeholder="Descrição do Valor" wire:model="novoValorDescricao"></textarea>
                                                    <div class="d-flex justify-content-end gap-1 mt-1">
                                                        <button class="btn btn-sm btn-secondary" wire:click="$set('novoValorTitulo', '')">Cancelar</button>
                                                        <button class="btn btn-sm btn-primary" wire:click="adicionarValor">Adicionar</button>
                                                    </div>
                                                </div>
                                            @else
                                                <button class="btn btn-link text-decoration-none text-muted d-flex flex-column align-items-center p-3"
                                                        wire:click="$set('novoValorTitulo', ' ')" {{-- Hack to show form --}}>
                                                    <div class="rounded-circle bg-secondary bg-opacity-10 p-2 mb-2">
                                                        <i class="bi bi-plus-lg text-secondary"></i>
                                                    </div>
                                                    <span class="small fw-semibold">Adicionar Novo Valor</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Exclusão de Valor --}}
    <x-confirmation-modal wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="modal-icon modal-icon-danger">
                    <i class="bi bi-trash"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold text-dark">{{ __('Remover Valor') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Ação irreversível') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation">
                <p class="text-dark">
                    {{ __('Tem certeza que deseja remover este princípio da identidade estratégica da organização?') }}
                </p>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDeleteModal', false)" wire:loading.attr="disabled" class="btn-modern">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-danger-button wire:click="removerValor" wire:loading.attr="disabled" class="btn-delete-modern ms-2">
                <span wire:loading.remove wire:target="removerValor">
                    <i class="bi bi-trash me-1"></i>{{ __('Remover Agora') }}
                </span>
                <span wire:loading wire:target="removerValor">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                </span>
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
