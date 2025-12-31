{{-- Toolbar de Filtros e Views --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            {{-- View Switcher --}}
            <div class="notion-view-switcher">
                <button 
                    wire:click="setView('kanban')" 
                    class="notion-view-btn {{ $view === 'kanban' ? 'active' : '' }}"
                    title="Kanban"
                >
                    <i class="bi bi-kanban"></i>
                    <span class="d-none d-md-inline">Kanban</span>
                </button>
                <button 
                    wire:click="setView('lista')" 
                    class="notion-view-btn {{ $view === 'lista' ? 'active' : '' }}"
                    title="Lista"
                >
                    <i class="bi bi-list-ul"></i>
                    <span class="d-none d-md-inline">Lista</span>
                </button>
                <button 
                    wire:click="setView('timeline')" 
                    class="notion-view-btn {{ $view === 'timeline' ? 'active' : '' }}"
                    title="Timeline"
                >
                    <i class="bi bi-bar-chart-steps"></i>
                    <span class="d-none d-md-inline">Timeline</span>
                </button>
                <button 
                    wire:click="setView('calendario')" 
                    class="notion-view-btn {{ $view === 'calendario' ? 'active' : '' }}"
                    title="Calendário"
                >
                    <i class="bi bi-calendar3"></i>
                    <span class="d-none d-md-inline">Calendário</span>
                </button>
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
                    @foreach(\App\Models\PEI\Entrega::STATUS_OPTIONS as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>

                {{-- Filtro de Prioridade --}}
                <select wire:model.live="filtroPrioridade" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Todas Prioridades</option>
                    @foreach(\App\Models\PEI\Entrega::PRIORIDADE_OPTIONS as $key => $info)
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
