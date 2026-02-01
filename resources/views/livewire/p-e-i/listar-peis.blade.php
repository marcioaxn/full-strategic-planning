<div>
    {{-- Page Header --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="icon-circle-header gradient-theme-icon">
                    <i class="bi bi-calendar-range-fill"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ __('Planos Estratégicos Institucionais (PEI)') }}</h1>
                <span class="badge-modern badge-count">
                    {{ $peis->total() }}
                </span>
            </div>
            <p class="text-muted mb-0">
                {{ __('Gerencie os ciclos de planejamento estratégico da instituição.') }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div wire:loading.delay.short wire:target="search,save,delete,create,edit" class="text-primary">
                <span class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">{{ __('Carregando...') }}</span>
                </span>
            </div>

            <x-action-button
                variant="primary"
                icon="plus-lg"
                tooltip="{{ __('Adicionar novo PEI') }}"
                wire:click="create"
                class="btn-action-primary gradient-theme-btn"
            >
                {{ __('Novo PEI') }}
            </x-action-button>
        </div>
    </div>

    @if (session()->has('status'))
        <div class="alert alert-modern alert-success alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert">
            <div class="alert-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <span class="flex-grow-1">{{ session('status') }}</span>
            <button type="button" class="btn-close btn-close-modern" data-bs-dismiss="alert" aria-label="{{ __('Fechar') }}"></button>
        </div>
    @endif

    {{-- Seção Educativa: O que é o PEI? --}}
    <div class="card border-0 shadow-sm mb-4 educational-card-gradient" x-data="{ expanded: false }">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle bg-white bg-opacity-25">
                        <i class="bi bi-book-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">
                            <i class="bi bi-mortarboard me-2"></i>{{ __('O que é o PEI?') }}
                        </h5>
                        <p class="mb-0 text-white small">
                            {{ __('Aprenda sobre o ciclo de planejamento estratégico') }}
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
                    {{-- Main Explanation --}}
                    <div class="col-12">
                        <div class="alert alert-info border-0 d-flex align-items-start gap-3 mb-0">
                            <div class="icon-circle-mini bg-info bg-opacity-10 text-info flex-shrink-0">
                                <i class="bi bi-lightbulb-fill"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2 text-dark">{{ __('Definição') }}</h6>
                                <p class="mb-0 small text-dark">
                                    O <strong>Planejamento Estratégico Institucional (PEI)</strong> é um instrumento de gestão estratégica de médio e longo prazo que define a direção da organização. Ele estabelece onde queremos chegar (Visão), como vamos chegar (Objetivos Estratégicos) e como saberemos que chegamos (Indicadores de Desempenho).
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Step-by-Step Implementation Guide --}}
                    <div class="col-12">
                        <div class="bg-light rounded-3 p-4 border">
                            <h5 class="fw-bold mb-4 text-dark text-center">
                                <i class="bi bi-diagram-3-fill me-2 text-primary"></i>
                                Sequência de Implementação do PEI
                            </h5>

                            {{-- Steps Flow --}}
                            <div class="row g-3">
                                {{-- Step 1 --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="bg-white rounded-3 p-3 h-100 position-relative text-body shadow-sm border">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary text-white fw-bold px-2 py-1">1º</span>
                                            <h6 class="fw-bold mb-0 small text-dark">Ciclo PEI</h6>
                                        </div>
                                        <p class="small mb-2 text-muted">Defina o período de vigência do planejamento.</p>
                                        <div class="bg-light rounded p-2 small border">
                                            <i class="bi bi-calendar-check me-1 text-primary"></i>
                                            <strong class="text-dark">Exemplo:</strong><br>
                                            <span class="text-dark">PEI 2024-2028</span><br>
                                            <span class="text-muted">(5 anos)</span>
                                        </div>
                                        {{-- Arrow --}}
                                        <div class="d-none d-lg-block position-absolute top-50 translate-middle-y text-primary" style="right: -15px;">
                                            <i class="bi bi-arrow-right fs-4"></i>
                                        </div>
                                    </div>
                                </div>

                                {{-- Step 2 --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="bg-white rounded-3 p-3 h-100 position-relative text-body shadow-sm border">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary text-white fw-bold px-2 py-1">2º</span>
                                            <h6 class="fw-bold mb-0 small text-dark">Identidade</h6>
                                        </div>
                                        <p class="small mb-2 text-muted">Estabeleça Missão, Visão e Valores da organização.</p>
                                        <div class="bg-light rounded p-2 small border">
                                            <i class="bi bi-bullseye me-1 text-success"></i>
                                            <strong class="text-dark">Exemplo:</strong><br>
                                            <span class="text-dark">Visão: "Ser referência em gestão pública até 2028"</span>
                                        </div>
                                        {{-- Arrow --}}
                                        <div class="d-none d-lg-block position-absolute top-50 translate-middle-y text-primary" style="right: -15px;">
                                            <i class="bi bi-arrow-right fs-4"></i>
                                        </div>
                                    </div>
                                </div>

                                {{-- Step 3 --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="bg-white rounded-3 p-3 h-100 position-relative text-body shadow-sm border">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary text-white fw-bold px-2 py-1">3º</span>
                                            <h6 class="fw-bold mb-0 small text-dark">Perspectivas BSC</h6>
                                        </div>
                                        <p class="small mb-2 text-muted">Defina as 4 perspectivas do Balanced Scorecard.</p>
                                        <div class="bg-light rounded p-2 small border">
                                            <i class="bi bi-layers me-1 text-warning"></i>
                                            <strong class="text-dark">Padrão:</strong><br>
                                            <span class="text-dark">• Financeira<br>• Clientes<br>• Processos<br>• Aprendizado</span>
                                        </div>
                                        {{-- Arrow --}}
                                        <div class="d-none d-lg-block position-absolute top-50 translate-middle-y text-primary" style="right: -15px;">
                                            <i class="bi bi-arrow-right fs-4"></i>
                                        </div>
                                    </div>
                                </div>

                                {{-- Step 4 --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="bg-white rounded-3 p-3 h-100 text-body shadow-sm border">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary text-white fw-bold px-2 py-1">4º</span>
                                            <h6 class="fw-bold mb-0 small text-dark">Objetivos</h6>
                                        </div>
                                        <p class="small mb-2 text-muted">Crie objetivos estratégicos por perspectiva.</p>
                                        <div class="bg-light rounded p-2 small border">
                                            <i class="bi bi-trophy me-1 text-info"></i>
                                            <strong class="text-dark">Exemplo:</strong><br>
                                            <span class="text-dark">"Ampliar a satisfação do cidadão em 20%"</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Steps 5-7 (Second Row) --}}
                                <div class="col-12 mt-3">
                                    <div class="d-flex align-items-center justify-content-center mb-3">
                                        <i class="bi bi-arrow-down fs-2 text-primary opacity-50"></i>
                                    </div>
                                </div>

                                {{-- Step 5 --}}
                                <div class="col-12 col-md-4">
                                    <div class="bg-white rounded-3 p-3 h-100 text-body shadow-sm border">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary text-white fw-bold px-2 py-1">5º</span>
                                            <h6 class="fw-bold mb-0 small text-dark">Indicadores (KPIs)</h6>
                                        </div>
                                        <p class="small mb-2 text-muted">Defina métricas para cada objetivo.</p>
                                        <div class="bg-light rounded p-2 small border">
                                            <i class="bi bi-graph-up me-1 text-danger"></i>
                                            <strong class="text-dark">Exemplo:</strong><br>
                                            <span class="text-dark">"Índice de Satisfação do Cidadão"</span><br>
                                            <span class="text-muted">Meta: 85% até 2028</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Step 6 --}}
                                <div class="col-12 col-md-4">
                                    <div class="bg-white rounded-3 p-3 h-100 text-body shadow-sm border">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary text-white fw-bold px-2 py-1">6º</span>
                                            <h6 class="fw-bold mb-0 small text-dark">Planos de Ação</h6>
                                        </div>
                                        <p class="small mb-2 text-muted">Crie projetos e iniciativas para alcançar os objetivos.</p>
                                        <div class="bg-light rounded p-2 small border">
                                            <i class="bi bi-kanban me-1 text-success"></i>
                                            <strong class="text-dark">Exemplo:</strong><br>
                                            <span class="text-dark">"Implementar Ouvidoria Digital"</span><br>
                                            <span class="text-muted">Prazo: 12 meses</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Step 7 --}}
                                <div class="col-12 col-md-4">
                                    <div class="bg-white rounded-3 p-3 h-100 text-body shadow-sm border">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary text-white fw-bold px-2 py-1">7º</span>
                                            <h6 class="fw-bold mb-0 small text-dark">Monitoramento</h6>
                                        </div>
                                        <p class="small mb-2 text-muted">Acompanhe a execução e resultados continuamente.</p>
                                        <div class="bg-light rounded p-2 small border">
                                            <i class="bi bi-speedometer2 me-1 text-warning"></i>
                                            <strong class="text-dark">Atividades:</strong><br>
                                            <span class="text-dark">• Atualizar evolução mensal<br>• Revisar semestralmente</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bottom Info --}}
                    <div class="col-12">
                        <div class="alert alert-warning bg-warning bg-opacity-10 border-0 d-flex align-items-start gap-3 mb-0">
                            <div class="icon-circle-mini bg-warning bg-opacity-25 text-warning flex-shrink-0">
                                <i class="bi bi-info-circle-fill"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2 text-dark">{{ __('Importante: Siga a ordem!') }}</h6>
                                <p class="mb-0 small text-dark">
                                    {{ __('Cada etapa depende da anterior. Não é possível criar objetivos sem antes definir as perspectivas BSC, nem criar indicadores sem ter objetivos cadastrados. O sistema guiará você nesta sequência.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Why PEI --}}
                    <div class="col-12">
                        <div class="alert alert-secondary border-0 d-flex align-items-start gap-3 mb-0">
                            <div class="icon-circle-mini bg-secondary bg-opacity-10 text-secondary flex-shrink-0">
                                <i class="bi bi-question-circle-fill"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2 text-dark">{{ __('Por que ter um PEI?') }}</h6>
                                <p class="mb-0 small text-dark">
                                    {{ __('O PEI garante que todos na organização trabalhem alinhados aos mesmos objetivos, permite mensurar resultados de forma objetiva, facilita a tomada de decisões estratégicas e demonstra transparência na gestão dos recursos públicos ou privados.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card card-modern filters-card mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-5">
                    <label for="pei-search" class="form-label-modern">
                        <i class="bi bi-search me-2"></i>{{ __('Buscar') }}
                    </label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            id="pei-search"
                            type="search"
                            class="form-control"
                            placeholder="{{ __('Buscar por descrição...') }}"
                            wire:model.live.debounce.250ms="search"
                        >
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <label for="pei-status" class="form-label-modern">
                        <i class="bi bi-funnel me-2"></i>{{ __('Status') }}
                    </label>
                    <select id="pei-status" class="form-select form-select-modern" wire:model.live="filtroStatus">
                        <option value="">Todos</option>
                        <option value="ativo">Ativo (Vigente)</option>
                        <option value="futuro">Futuro</option>
                        <option value="passado">Encerrado</option>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    @if ($search !== '' || $filtroStatus !== '')
                        <button type="button" class="btn btn-outline-secondary btn-modern w-100" wire:click="resetFilters">
                            <i class="bi bi-x-lg me-2"></i>{{ __('Limpar Filtros') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card card-modern table-card">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="ps-4">{{ __('Descrição') }}</th>
                        <th scope="col">{{ __('Período') }}</th>
                        <th scope="col">{{ __('Perspectivas') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col" class="text-end pe-4">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody wire:loading.class="loading-opacity" wire:target="search,resetFilters">
                    @forelse ($peis as $pei)
                        @php
                            $anoAtual = now()->year;
                            $isAtivo = $anoAtual >= $pei->num_ano_inicio_pei && $anoAtual <= $pei->num_ano_fim_pei;
                            $isFuturo = $pei->num_ano_inicio_pei > $anoAtual;
                            $isPassado = $pei->num_ano_fim_pei < $anoAtual;
                        @endphp
                        <tr class="table-row-hover" wire:key="pei-row-{{ $pei->cod_pei }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle-header avatar-modern {{ $isAtivo ? 'bg-success' : ($isFuturo ? 'bg-info' : 'bg-secondary') }}">
                                        <i class="bi bi-calendar-check text-white"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('pei.detalhes', $pei->cod_pei) }}" wire:navigate class="fw-semibold text-body-emphasis text-decoration-none hover-primary">
                                            {{ $pei->dsc_pei }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $pei->num_ano_inicio_pei }} - {{ $pei->num_ano_fim_pei }}</span>
                                <small class="d-block text-muted">{{ $pei->num_ano_fim_pei - $pei->num_ano_inicio_pei + 1 }} anos</small>
                            </td>
                            <td>
                                <span class="badge-modern badge-secondary">
                                    <i class="bi bi-layers me-1"></i>{{ $pei->perspectivas_count }} perspectivas
                                </span>
                            </td>
                            <td>
                                @if($isAtivo)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>Vigente
                                    </span>
                                @elseif($isFuturo)
                                    <span class="badge bg-info text-dark">
                                        <i class="bi bi-clock-fill me-1"></i>Futuro
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-archive-fill me-1"></i>Encerrado
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="action-buttons">
                                    <a href="{{ route('pei.detalhes', $pei->cod_pei) }}" wire:navigate class="btn btn-icon btn-outline-info" data-bs-toggle="tooltip" title="{{ __('Detalhar') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <x-action-button variant="outline-primary" icon="pencil" tooltip="{{ __('Editar') }}" wire:click="edit('{{ $pei->cod_pei }}')" class="btn-action-icon" />
                                    <x-action-button variant="outline-danger" icon="trash" tooltip="{{ __('Excluir') }}" wire:click="confirmDelete('{{ $pei->cod_pei }}')" class="btn-action-icon" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-5">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-calendar-x"></i>
                                    </div>
                                    <h5 class="empty-state-title">{{ __('Nenhum PEI encontrado') }}</h5>
                                    <p class="empty-state-text">
                                        {{ __('Crie um novo Plano Estratégico Institucional para começar.') }}
                                    </p>
                                    <x-action-button variant="primary" icon="plus-lg" wire:click="create" class="btn-action-primary gradient-theme-btn px-4">
                                        {{ __('Criar PEI') }}
                                    </x-action-button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($peis->hasPages())
            <div class="card-footer pagination-footer">
                <span class="pagination-info">
                    {{ __('Mostrando') }} <span class="fw-semibold">{{ $peis->firstItem() }}</span> {{ __('a') }} <span class="fw-semibold">{{ $peis->lastItem() }}</span> {{ __('de') }} <span class="fw-semibold">{{ $peis->total() }}</span> {{ __('resultados') }}
                </span>
                {{ $peis->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    {{-- Create/Edit Modal Premium XL --}}
    @if($showModal)
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5); z-index: 1055;" wire:click.self="showModal = false">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    
                    {{-- Header Premium --}}
                    <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle-mini bg-white bg-opacity-25 text-white">
                                <i class="bi bi-{{ $peiId ? 'pencil-square' : 'calendar-plus' }}"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">{{ $peiId ? 'Editar Ciclo PEI' : 'Novo Ciclo PEI' }}</h5>
                                <p class="mb-0 small text-white-50">Configuração do período de vigência estratégica</p>
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
                                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Identificação do Ciclo</h6>
                                            
                                            {{-- Descrição --}}
                                            <div class="mb-4">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Descrição do PEI <span class="text-danger">*</span></label>
                                                <input type="text"
                                                       class="form-control form-control-lg bg-white border-0 shadow-sm @error('dsc_pei') is-invalid @enderror"
                                                       wire:model="dsc_pei"
                                                       placeholder="Ex: PEI 2024-2028">
                                                @error('dsc_pei') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="alert alert-info border-0 bg-white shadow-sm rounded-4 p-3 mb-0">
                                                <div class="d-flex gap-3">
                                                    <i class="bi bi-info-circle-fill text-primary fs-4"></i>
                                                    <div class="small">
                                                        <p class="fw-bold mb-1 text-dark">Dica de Gestão:</p>
                                                        <p class="text-muted mb-0">Um ciclo estratégico institucional costuma durar de 4 a 5 anos. O nome do PEI deve refletir esse período para facilitar a identificação nos relatórios.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Coluna Lateral: Período --}}
                                <div class="col-lg-5">
                                    <div class="card border-0 bg-light rounded-4 h-100">
                                        <div class="card-body p-4 text-center">
                                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-4">Período de Vigência</h6>
                                            
                                            <div class="row g-3">
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label small text-muted fw-bold text-uppercase">Ano de Início <span class="text-danger">*</span></label>
                                                    <div class="input-group input-group-lg shadow-sm">
                                                        <span class="input-group-text bg-white border-0 text-primary"><i class="bi bi-calendar-event"></i></span>
                                                        <input type="number" wire:model="num_ano_inicio_pei" class="form-control bg-white border-0 fw-bold text-center @error('num_ano_inicio_pei') is-invalid @enderror" min="2000" max="2100">
                                                    </div>
                                                    @error('num_ano_inicio_pei') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                                                </div>

                                                <div class="col-md-12 mb-4">
                                                    <label class="form-label small text-muted fw-bold text-uppercase">Ano de Término <span class="text-danger">*</span></label>
                                                    <div class="input-group input-group-lg shadow-sm">
                                                        <span class="input-group-text bg-white border-0 text-primary"><i class="bi bi-calendar-check"></i></span>
                                                        <input type="number" wire:model="num_ano_fim_pei" class="form-control bg-white border-0 fw-bold text-center @error('num_ano_fim_pei') is-invalid @enderror" min="2000" max="2100">
                                                    </div>
                                                    @error('num_ano_fim_pei') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                                                </div>
                                            </div>

                                            <div class="p-3 bg-white rounded-3 border shadow-sm mt-auto" x-data="{ 
                                                inicio: @entangle('num_ano_inicio_pei'), 
                                                fim: @entangle('num_ano_fim_pei') 
                                            }">
                                                <p class="small text-muted mb-1">Duração Estimada:</p>
                                                <div class="h4 fw-bold text-primary mb-0">
                                                    <span x-text="fim - inicio + 1"></span> anos
                                                </div>
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
                                <i class="bi bi-check-lg me-2"></i>Salvar Ciclo PEI
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

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
                    <h3 class="fw-bold text-dark mb-3">Ciclo PEI Registrado!</h3>
                    <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.6;">
                        <strong class="text-primary d-block mb-2">"{{ $createdPeiName }}"</strong>
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

    {{-- Delete Confirmation Modal --}}
    <x-confirmation-modal wire:key="pei-delete-modal" wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="icon-circle-mini modal-icon-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold">{{ __('Excluir PEI') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Esta ação é irreversível') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation">
                <p class="mb-2">
                    {{ __('Tem certeza que deseja excluir este Plano Estratégico Institucional?') }}
                </p>
                <div class="alert alert-warning bg-warning-subtle border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção:</strong> A exclusão deste ciclo afetará:
                    <ul class="mb-0 mt-2">
                        <li>{{ $impactoExclusao['perspectivas'] ?? 0 }} Perspectivas</li>
                        <li>{{ $impactoExclusao['objetivos'] ?? 0 }} Objetivos</li>
                        <li>{{ $impactoExclusao['indicadores'] ?? 0 }} Indicadores</li>
                        <li>{{ $impactoExclusao['planos'] ?? 0 }} Planos de Ação</li>
                    </ul>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDeleteModal', false)" wire:loading.attr="disabled" class="btn-modern">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-danger-button wire:click="delete" wire:loading.attr="disabled" class="btn-delete-modern">
                <span wire:loading.remove wire:target="delete">
                    <i class="bi bi-trash me-1"></i>{{ __('Excluir PEI') }}
                </span>
                <span wire:loading wire:target="delete">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                    {{ __('Excluindo...') }}
                </span>
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
