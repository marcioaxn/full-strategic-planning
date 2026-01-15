{{-- Toolbar de Filtros e Views --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            {{-- View Switcher --}}
            <div class="d-flex align-items-center gap-3">
                <div class="notion-view-switcher">
                    <button
                        wire:click="setView('kanban')"
                        wire:loading.class="loading"
                        wire:target="setView('kanban')"
                        class="notion-view-btn {{ $view === 'kanban' ? 'active' : '' }}"
                        title="Kanban"
                    >
                        <span wire:loading.remove wire:target="setView('kanban')">
                            <i class="bi bi-kanban"></i>
                        </span>
                        <span wire:loading wire:target="setView('kanban')">
                            <span class="spinner-border spinner-border-sm btn-spinner" role="status"></span>
                        </span>
                        <span class="d-none d-md-inline">Kanban</span>
                    </button>
                    <button
                        wire:click="setView('lista')"
                        wire:loading.class="loading"
                        wire:target="setView('lista')"
                        class="notion-view-btn {{ $view === 'lista' ? 'active' : '' }}"
                        title="Lista"
                    >
                        <span wire:loading.remove wire:target="setView('lista')">
                            <i class="bi bi-list-ul"></i>
                        </span>
                        <span wire:loading wire:target="setView('lista')">
                            <span class="spinner-border spinner-border-sm btn-spinner" role="status"></span>
                        </span>
                        <span class="d-none d-md-inline">Lista</span>
                    </button>
                    <button
                        wire:click="setView('calendario')"
                        wire:loading.class="loading"
                        wire:target="setView('calendario')"
                        class="notion-view-btn {{ $view === 'calendario' ? 'active' : '' }}"
                        title="Calendário"
                    >
                        <span wire:loading.remove wire:target="setView('calendario')">
                            <i class="bi bi-calendar3"></i>
                        </span>
                        <span wire:loading wire:target="setView('calendario')">
                            <span class="spinner-border spinner-border-sm btn-spinner" role="status"></span>
                        </span>
                        <span class="d-none d-md-inline">Calendário</span>
                    </button>
                    <button
                        wire:click="setView('timeline')"
                        wire:loading.class="loading"
                        wire:target="setView('timeline')"
                        class="notion-view-btn {{ $view === 'timeline' ? 'active' : '' }}"
                        title="Gantt"
                    >
                        <span wire:loading.remove wire:target="setView('timeline')">
                            <i class="bi bi-bar-chart-steps"></i>
                        </span>
                        <span wire:loading wire:target="setView('timeline')">
                            <span class="spinner-border spinner-border-sm btn-spinner" role="status"></span>
                        </span>
                        <span class="d-none d-md-inline">Gantt</span>
                    </button>
                </div>

            {{-- Navegação Estratégica em Cascata --}}
            <div class="d-flex align-items-center gap-2 flex-grow-1 mx-3" style="max-width: 800px;">
                {{-- 1. Perspectiva --}}
                <div class="flex-grow-1">
                    <select wire:model.live="perspectivaId" class="form-select form-select-sm border-0 bg-light shadow-xs fw-semibold">
                        <option value="">Todas Perspectivas</option>
                        @foreach($perspectivasDisponiveis as $p)
                            <option value="{{ $p->cod_perspectiva }}">{{ $p->dsc_perspectiva }}</option>
                        @endforeach
                    </select>
                </div>

                <i class="bi bi-chevron-right text-muted small"></i>

                {{-- 2. Objetivo --}}
                <div class="flex-grow-1">
                    <select wire:model.live="objetivoId" class="form-select form-select-sm border-0 bg-light shadow-xs fw-semibold">
                        <option value="">Todos Objetivos</option>
                        @foreach($objetivosDisponiveis as $obj)
                            <option value="{{ $obj->cod_objetivo }}">{{ Str::limit($obj->nom_objetivo, 40) }}</option>
                        @endforeach
                    </select>
                </div>

                <i class="bi bi-chevron-right text-muted small"></i>

                {{-- 3. Plano de Ação --}}
                <div class="flex-grow-1">
                    <select class="form-select form-select-sm border-0 bg-primary bg-opacity-10 text-primary fw-bold shadow-xs" 
                            onchange="$wire.mudarPlano(this.value)">
                        @foreach($planosDisponiveis as $p)
                            <option value="{{ $p->cod_plano_de_acao }}" {{ $p->cod_plano_de_acao === $plano->cod_plano_de_acao ? 'selected' : '' }}>
                                {{ Str::limit($p->dsc_plano_de_acao, 40) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            </div>

            {{-- Filtros --}}
            <div class="d-flex flex-wrap align-items-center gap-2">
                {{-- Busca --}}
                <div class="input-group input-group-sm" style="width: 200px;">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="busca" 
                        class="form-control border-start-0" 
                        placeholder="Buscar..."
                    >
                </div>

                {{-- Filtro de Status --}}
                <select wire:model.live="filtroStatus" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Todos os Status</option>
                    @foreach(\App\Models\ActionPlan\Entrega::STATUS_OPTIONS as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>

                {{-- Filtro de Prioridade --}}
                <select wire:model.live="filtroPrioridade" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Todas Prioridades</option>
                    @foreach(\App\Models\ActionPlan\Entrega::PRIORIDADE_OPTIONS as $key => $info)
                        <option value="{{ $key }}">{{ $info['label'] }}</option>
                    @endforeach
                </select>

                {{-- Filtro de Responsável --}}
                <select wire:model.live="filtroResponsavel" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Todos Responsáveis</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                    @endforeach
                </select>

                {{-- Opções Extras --}}
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button class="dropdown-item" wire:click="toggleArquivados">
                                <i class="bi bi-{{ $mostrarArquivados ? 'check-square' : 'square' }} me-2"></i>
                                Mostrar Arquivados
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item" wire:click="toggleLixeira">
                                <i class="bi bi-{{ $mostrarLixeira ? 'check-square' : 'square' }} me-2"></i>
                                Mostrar Lixeira
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button class="dropdown-item" wire:click="limparFiltros">
                                <i class="bi bi-x-circle me-2"></i>
                                Limpar Filtros
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Indicadores de Filtro Ativo --}}
        @if($mostrarLixeira)
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-0 mt-3 py-2 notion-trash-banner">
                <i class="bi bi-trash"></i>
                <span>Visualizando lixeira (itens serão excluídos permanentemente após 24 horas)</span>
                <button class="btn btn-sm btn-link ms-auto p-0" wire:click="toggleLixeira">Voltar</button>
            </div>
        @elseif($mostrarArquivados)
            <div class="alert alert-secondary d-flex align-items-center gap-2 mb-0 mt-3 py-2 notion-archived-banner">
                <i class="bi bi-archive"></i>
                <span>Incluindo itens arquivados</span>
                <button class="btn btn-sm btn-link ms-auto p-0" wire:click="toggleArquivados">Ocultar</button>
            </div>
        @endif
    </div>
</div>
