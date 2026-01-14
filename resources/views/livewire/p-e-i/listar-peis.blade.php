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
                                        <i class="bi bi-calendar-check"></i>
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
                                    <span class="badge badge-status status-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>Vigente
                                    </span>
                                @elseif($isFuturo)
                                    <span class="badge badge-status status-info">
                                        <i class="bi bi-clock-fill me-1"></i>Futuro
                                    </span>
                                @else
                                    <span class="badge badge-status status-secondary">
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

    {{-- PEI Help Section (Educational) --}}
    <div class="card card-modern mt-4 border-0 shadow-sm educational-card-gradient">
        <div class="card-body p-4 text-white">
            <div class="row g-4">
                {{-- Main Explanation --}}
                <div class="col-12">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="flex-shrink-0">
                            <div class="icon-circle bg-white bg-opacity-25">
                                <i class="bi bi-lightbulb-fill fs-3 text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-2 text-white">O que é o PEI?</h5>
                            <p class="mb-0 text-white-50" style="line-height: 1.6;">
                                O <strong>Planejamento Estratégico Institucional (PEI)</strong> é um instrumento de gestão estratégica de médio e longo prazo
                                que define a direção da organização. Ele estabelece onde queremos chegar (Visão), como vamos chegar (Objetivos Estratégicos)
                                e como saberemos que chegamos (Indicadores de Desempenho).
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Step-by-Step Implementation Guide --}}
                <div class="col-12">
                    <div class="bg-white bg-opacity-10 rounded-3 p-4">
                        <h5 class="fw-bold mb-4 text-white text-center">
                            <i class="bi bi-diagram-3-fill me-2"></i>
                            Sequência de Implementação do PEI
                        </h5>

                        {{-- Steps Flow --}}
                        <div class="row g-3">
                            {{-- Step 1 --}}
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="bg-body rounded-3 p-3 h-100 position-relative text-body shadow-sm">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-primary text-white fw-bold px-2 py-1">1º</span>
                                        <h6 class="fw-bold mb-0 small">Ciclo PEI</h6>
                                    </div>
                                    <p class="small mb-2 opacity-90">Defina o período de vigência do planejamento.</p>
                                    <div class="bg-body-secondary rounded p-2 small">
                                        <i class="bi bi-calendar-check me-1"></i>
                                        <strong>Exemplo:</strong><br>
                                        PEI 2024-2028<br>
                                        <span class="opacity-75">(5 anos)</span>
                                    </div>
                                    {{-- Arrow --}}
                                    <div class="d-none d-lg-block position-absolute top-50 translate-middle-y text-white" style="right: -15px;">
                                        <i class="bi bi-arrow-right fs-4"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 2 --}}
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="bg-body rounded-3 p-3 h-100 position-relative text-body shadow-sm">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-primary text-white fw-bold px-2 py-1">2º</span>
                                        <h6 class="fw-bold mb-0 small">Identidade</h6>
                                    </div>
                                    <p class="small mb-2 opacity-90">Estabeleça Missão, Visão e Valores da organização.</p>
                                    <div class="bg-body-secondary rounded p-2 small">
                                        <i class="bi bi-bullseye me-1"></i>
                                        <strong>Exemplo:</strong><br>
                                        Visão: "Ser referência em gestão pública até 2028"
                                    </div>
                                    {{-- Arrow --}}
                                    <div class="d-none d-lg-block position-absolute top-50 translate-middle-y text-white" style="right: -15px;">
                                        <i class="bi bi-arrow-right fs-4"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 3 --}}
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="bg-body rounded-3 p-3 h-100 position-relative text-body shadow-sm">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-primary text-white fw-bold px-2 py-1">3º</span>
                                        <h6 class="fw-bold mb-0 small">Perspectivas BSC</h6>
                                    </div>
                                    <p class="small mb-2 opacity-90">Defina as 4 perspectivas do Balanced Scorecard.</p>
                                    <div class="bg-body-secondary rounded p-2 small">
                                        <i class="bi bi-layers me-1"></i>
                                        <strong>Padrão:</strong><br>
                                        • Financeira<br>
                                        • Clientes<br>
                                        • Processos<br>
                                        • Aprendizado
                                    </div>
                                    {{-- Arrow --}}
                                    <div class="d-none d-lg-block position-absolute top-50 translate-middle-y text-white" style="right: -15px;">
                                        <i class="bi bi-arrow-right fs-4"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 4 --}}
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="bg-body rounded-3 p-3 h-100 text-body shadow-sm">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-primary text-white fw-bold px-2 py-1">4º</span>
                                        <h6 class="fw-bold mb-0 small">Objetivos</h6>
                                    </div>
                                    <p class="small mb-2 opacity-90">Crie objetivos estratégicos por perspectiva.</p>
                                    <div class="bg-body-secondary rounded p-2 small">
                                        <i class="bi bi-trophy me-1"></i>
                                        <strong>Exemplo:</strong><br>
                                        "Ampliar a satisfação do cidadão em 20%"
                                    </div>
                                </div>
                            </div>

                            {{-- Steps 5-7 (Second Row) --}}
                            <div class="col-12 mt-3">
                                <div class="d-flex align-items-center justify-content-center mb-3">
                                    <i class="bi bi-arrow-down fs-2 text-white opacity-75"></i>
                                </div>
                            </div>

                            {{-- Step 5 --}}
                            <div class="col-12 col-md-4">
                                <div class="bg-body rounded-3 p-3 h-100 text-body shadow-sm">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-primary text-white fw-bold px-2 py-1">5º</span>
                                        <h6 class="fw-bold mb-0 small">Indicadores (KPIs)</h6>
                                    </div>
                                    <p class="small mb-2 opacity-90">Defina métricas para cada objetivo.</p>
                                    <div class="bg-body-secondary rounded p-2 small">
                                        <i class="bi bi-graph-up me-1"></i>
                                        <strong>Exemplo:</strong><br>
                                        "Índice de Satisfação do Cidadão"<br>
                                        <span class="opacity-75">Meta: 85% até 2028</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 6 --}}
                            <div class="col-12 col-md-4">
                                <div class="bg-body rounded-3 p-3 h-100 text-body shadow-sm">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-primary text-white fw-bold px-2 py-1">6º</span>
                                        <h6 class="fw-bold mb-0 small">Planos de Ação</h6>
                                    </div>
                                    <p class="small mb-2 opacity-90">Crie projetos e iniciativas para alcançar os objetivos.</p>
                                    <div class="bg-body-secondary rounded p-2 small">
                                        <i class="bi bi-kanban me-1"></i>
                                        <strong>Exemplo:</strong><br>
                                        "Implementar Ouvidoria Digital"<br>
                                        <span class="opacity-75">Prazo: 12 meses</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 7 --}}
                            <div class="col-12 col-md-4">
                                <div class="bg-body rounded-3 p-3 h-100 text-body shadow-sm">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-primary text-white fw-bold px-2 py-1">7º</span>
                                        <h6 class="fw-bold mb-0 small">Monitoramento</h6>
                                    </div>
                                    <p class="small mb-2 opacity-90">Acompanhe a execução e resultados continuamente.</p>
                                    <div class="bg-body-secondary rounded p-2 small">
                                        <i class="bi bi-speedometer2 me-1"></i>
                                        <strong>Atividades:</strong><br>
                                        • Atualizar evolução mensal<br>
                                        • Revisar semestralmente
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bottom Info --}}
                <div class="col-12">
                    <div class="bg-body rounded-3 p-3 text-body shadow-sm">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <i class="bi bi-info-circle-fill mt-1"></i>
                            <div>
                                <strong class="small d-block mb-1">⚠️ Importante: Siga a ordem!</strong>
                                <p class="mb-0 small opacity-90">
                                    Cada etapa depende da anterior. Não é possível criar objetivos sem antes definir as perspectivas BSC,
                                    nem criar indicadores sem ter objetivos cadastrados. O sistema guiará você nesta sequência.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Why PEI --}}
                <div class="col-12">
                    <div class="bg-body rounded-3 p-3 text-body shadow-sm">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-question-circle-fill"></i>
                            <strong class="small">Por que ter um PEI?</strong>
                        </div>
                        <p class="mb-0 small opacity-90">
                            O PEI garante que todos na organização trabalhem alinhados aos mesmos objetivos, permite mensurar resultados de forma objetiva,
                            facilita a tomada de decisões estratégicas e demonstra transparência na gestão dos recursos públicos ou privados.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <x-dialog-modal wire:key="pei-form-modal" wire:model.live="showModal" maxWidth="lg">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="icon-circle-mini modal-icon-primary">
                    <i class="bi bi-{{ $peiId ? 'pencil' : 'calendar-plus' }}"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold">{{ $peiId ? __('Editar PEI') : __('Novo PEI') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Configure o ciclo de planejamento estratégico') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="row g-4">
                <div class="col-12">
                    <label for="dsc_pei" class="form-label-modern">
                        {{ __('Descrição do PEI') }} <span class="text-danger">*</span>
                        <x-tooltip title="Nome do ciclo estratégico (ex: PEI 2025-2029)" />
                    </label>
                    <input
                        id="dsc_pei"
                        type="text"
                        class="form-control form-control-modern @error('dsc_pei') is-invalid @enderror"
                        placeholder="Ex: PEI 2024-2028"
                        wire:model="dsc_pei"
                        required
                    >
                    @error('dsc_pei')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="num_ano_inicio_pei" class="form-label-modern">
                        {{ __('Ano de Início') }} <span class="text-danger">*</span>
                        <x-tooltip title="Primeiro ano de vigência do planejamento" />
                    </label>
                    <input
                        id="num_ano_inicio_pei"
                        type="number"
                        class="form-control form-control-modern @error('num_ano_inicio_pei') is-invalid @enderror"
                        min="2000"
                        max="2100"
                        wire:model="num_ano_inicio_pei"
                        required
                    >
                    @error('num_ano_inicio_pei')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="num_ano_fim_pei" class="form-label-modern">
                        {{ __('Ano de Término') }} <span class="text-danger">*</span>
                        <x-tooltip title="Último ano de vigência do planejamento" />
                    </label>
                    <input
                        id="num_ano_fim_pei"
                        type="number"
                        class="form-control form-control-modern @error('num_ano_fim_pei') is-invalid @enderror"
                        min="2000"
                        max="2100"
                        wire:model="num_ano_fim_pei"
                        required
                    >
                    @error('num_ano_fim_pei')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="alert alert-info bg-info-subtle border-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Dica:</strong> Um PEI típico tem duração de 4 a 5 anos. O PEI vigente é determinado automaticamente com base no ano atual.
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <span class="text-muted small d-none d-sm-inline">
                <span class="text-danger">*</span> {{ __('Campos obrigatórios') }}
            </span>
            <div class="d-flex gap-2">
                <x-secondary-button wire:click="$set('showModal', false)" wire:loading.attr="disabled" class="btn-modern">
                    {{ __('Cancelar') }}
                </x-secondary-button>

                <x-button type="button" wire:click="save" wire:loading.attr="disabled" class="btn-save-modern">
                    <span wire:loading.remove wire:target="save">
                        <i class="bi bi-check-lg me-1"></i>{{ __('Salvar PEI') }}
                    </span>
                    <span wire:loading wire:target="save">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                        {{ __('Salvando...') }}
                    </span>
                </x-button>
            </div>
        </x-slot>
    </x-dialog-modal>

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
