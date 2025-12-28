<div>
    {{-- Page Header --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="header-icon gradient-theme-icon">
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
                                    <div class="avatar-modern {{ $isAtivo ? 'bg-success' : ($isFuturo ? 'bg-info' : 'bg-secondary') }}">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-body-emphasis">{{ $pei->dsc_pei }}</div>
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
                                    <x-action-button variant="primary" icon="plus-lg" wire:click="create" class="btn-action-primary gradient-theme-btn">
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

    {{-- Create/Edit Modal --}}
    <x-dialog-modal wire:key="pei-form-modal" wire:model.live="showModal" maxWidth="lg">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="modal-icon modal-icon-primary">
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
            <div class="modal-footer-modern">
                <span class="text-muted small">
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
            </div>
        </x-slot>
    </x-dialog-modal>

    {{-- Delete Confirmation Modal --}}
    <x-confirmation-modal wire:key="pei-delete-modal" wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="modal-icon modal-icon-danger">
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
                    <strong>Atenção:</strong> Todos os dados associados (perspectivas, objetivos, indicadores, planos de ação) também serão removidos.
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
