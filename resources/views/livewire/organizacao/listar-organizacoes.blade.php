<div>
    {{-- Page Header --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="header-icon gradient-theme-icon">
                    <i class="bi bi-building"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ __('Organizações') }}</h1>
                <span class="badge-modern badge-count">
                    {{ $organizacoes->total() }}
                </span>
            </div>
            <p class="text-muted mb-0">
                {{ __('Gerencie a estrutura organizacional e hierarquia do sistema.') }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div wire:loading.delay.short wire:target="search,save,delete,create,edit" class="text-primary">
                <span class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">{{ __('Carregando...') }}</span>
                </span>
            </div>

            @can('create', App\Models\Organization::class)
                <x-action-button
                    variant="primary"
                    icon="plus-lg"
                    tooltip="{{ __('Adicionar nova organização') }} "
                    wire:click="create"
                    class="btn-action-primary gradient-theme-btn"
                >
                    {{ __('Nova Organização') }}
                </x-action-button>
            @endcan
        </div>
    </div>

    @if ($flashMessage)
        <div class="alert alert-modern alert-{{ $flashStyle }} alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert">
            <div class="alert-icon">
                @if($flashStyle === 'success')
                    <i class="bi bi-check-circle-fill"></i>
                @elseif($flashStyle === 'danger')
                    <i class="bi bi-exclamation-triangle-fill"></i>
                @else
                    <i class="bi bi-info-circle-fill"></i>
                @endif
            </div>
            <span class="flex-grow-1">{{ $flashMessage }}</span>
            <button type="button" class="btn-close btn-close-modern" aria-label="{{ __('Fechar') }}" wire:click="$set('flashMessage', null)"></button>
        </div>
    @endif

    {{-- Filters Card --}}
    <div class="card card-modern filters-card mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-8">
                    <label for="org-search" class="form-label-modern">
                        <i class="bi bi-search me-2"></i>{{ __('Buscar') }}
                    </label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            id="org-search"
                            type="search"
                            class="form-control"
                            placeholder="{{ __('Buscar por nome ou sigla...') }}"
                            wire:model.live.debounce.250ms="search"
                        >
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    @if ($search !== '')
                        <button type="button" class="btn btn-outline-secondary btn-modern w-100" wire:click="resetFilters">
                            <i class="bi bi-x-lg me-2"></i>{{ __('Limpar Filtros') }}
                        </button>
                    @endif
                </div>
            </div>

            @if ($search !== '')
                <div class="active-filters">
                    <span class="text-muted small fw-semibold">{{ __('Filtros ativos:') }}</span>
                    <button type="button" class="filter-tag filter-tag-primary" wire:click="$set('search', '')">
                        <i class="bi bi-search"></i>
                        <span>"{{ Str::limit($search, 15) }}"</span>
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Desktop Table --}}
    <div class="card card-modern table-card d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="ps-4">{{ __('Organização') }}</th>
                        <th scope="col">{{ __('Hierarquia (Pai)') }}</th>
                        <th scope="col" class="text-end pe-4">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody wire:loading.class="loading-opacity" wire:target="search,resetFilters">
                    @forelse ($organizacoes as $org)
                        <tr class="table-row-hover" wire:key="org-row-{{ $org->cod_organizacao }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-modern">
                                        {{ strtoupper(substr($org->sgl_organizacao, 0, 3)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-body-emphasis">{{ $org->nom_organizacao }}</div>
                                        <div class="text-muted small">
                                            <i class="bi bi-tag me-1"></i>{{ $org->sgl_organizacao }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($org->pai && !$org->isRaiz())
                                    <span class="badge-modern badge-secondary">
                                        <i class="bi bi-diagram-2 me-1"></i>{{ $org->pai->sgl_organizacao }}
                                    </span>
                                @elseif($org->isRaiz())
                                    <span class="badge badge-primary bg-primary-subtle text-primary border border-primary-subtle">
                                        <i class="bi bi-star-fill me-1"></i>Raiz
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="action-buttons">
                                    @can('update', $org)
                                        <x-action-button variant="outline-primary" icon="pencil" tooltip="{{ __('Editar') }}" wire:click="edit('{{ $org->cod_organizacao }}')" class="btn-action-icon" />
                                    @endcan
                                    
                                    @can('delete', $org)
                                        <x-action-button variant="outline-danger" icon="trash" tooltip="{{ __('Excluir') }}" wire:click="confirmDelete('{{ $org->cod_organizacao }}')" class="btn-action-icon" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-5">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-building-slash"></i>
                                    </div>
                                    <h5 class="empty-state-title">{{ __('Nenhuma organização encontrada') }}</h5>
                                    <p class="empty-state-text">
                                        @if ($search !== '')
                                            {{ __('Tente ajustar seus termos de busca.') }}
                                        @else
                                            {{ __('Comece criando sua primeira organização.') }}
                                        @endif
                                    </p>
                                    @if ($search !== '')
                                        <button type="button" class="btn btn-outline-secondary btn-modern" wire:click="resetFilters">
                                            <i class="bi bi-x-lg me-2"></i>{{ __('Limpar filtros') }}
                                        </button>
                                    @else
                                        @can('create', App\Models\Organization::class)
                                            <x-action-button variant="primary" icon="plus-lg" wire:click="create" class="btn-action-primary gradient-theme-btn">
                                                {{ __('Criar Organização') }}
                                            </x-action-button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($organizacoes->hasPages())
            <div class="card-footer pagination-footer">
                <span class="pagination-info">
                    {{ __('Mostrando') }} <span class="fw-semibold">{{ $organizacoes->firstItem() }}</span> {{ __('a') }} <span class="fw-semibold">{{ $organizacoes->lastItem() }}</span> {{ __('de') }} <span class="fw-semibold">{{ $organizacoes->total() }}</span> {{ __('resultados') }}
                </span>
                {{ $organizacoes->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    {{-- Mobile Cards --}}
    <div class="d-md-none">
        <div class="mobile-cards-container" wire:loading.class="loading-opacity" wire:target="search,resetFilters">
            @forelse ($organizacoes as $org)
                <div class="card card-modern mobile-lead-card" wire:key="org-card-{{ $org->cod_organizacao }}">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-modern avatar-mobile">
                                    {{ strtoupper(substr($org->sgl_organizacao, 0, 3)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold">{{ $org->nom_organizacao }}</h6>
                                    <span class="text-muted small">{{ $org->sgl_organizacao }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mobile-contact-info">
                            <div class="contact-item">
                                <i class="bi bi-diagram-2"></i>
                                @if($org->pai && !$org->isRaiz())
                                    {{ $org->pai->sgl_organizacao }}
                                @elseif($org->isRaiz())
                                    <span class="text-primary fw-bold">Raiz</span>
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="mobile-card-footer">
                            <div class="action-buttons w-100 justify-content-end">
                                @can('update', $org)
                                    <x-action-button variant="outline-primary" icon="pencil" tooltip="{{ __('Editar') }}" wire:click="edit('{{ $org->cod_organizacao }}')" size="sm" class="btn-action-icon">
                                        <span class="d-none d-sm-inline">{{ __('Editar') }}</span>
                                    </x-action-button>
                                @endcan
                                
                                @can('delete', $org)
                                    <x-action-button variant="outline-danger" icon="trash" tooltip="{{ __('Excluir') }}" wire:click="confirmDelete('{{ $org->cod_organizacao }}')" size="sm" class="btn-action-icon" />
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card card-modern">
                    <div class="card-body p-4 text-center">
                        <p class="text-muted">{{ __('Nenhuma organização encontrada.') }}</p>
                    </div>
                </div>
            @endforelse
        </div>
        
        @if($organizacoes->hasPages())
             <div class="mobile-pagination">
                {{ $organizacoes->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    <x-dialog-modal wire:key="org-form-modal" wire:model.live="showFormModal" maxWidth="2xl">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="modal-icon modal-icon-primary">
                    <i class="bi bi-{{ $editing ? 'pencil' : 'plus-lg' }}"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold">{{ $editing ? __('Editar Organização') : __('Nova Organização') }}</h5>
                    <p class="text-muted small mb-0">{{ $editing ? __('Atualize as informações da organização') : __('Preencha os detalhes para criar uma nova organização') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="row g-3">
                <div class="col-12 col-lg-4">
                    <label for="sgl_organizacao" class="form-label-modern">
                        {{ __('Sigla') }} <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text"><i class="bi bi-tag"></i></span>
                        <input
                            id="sgl_organizacao"
                            type="text"
                            class="form-control @error('form.sgl_organizacao') is-invalid @enderror"
                            placeholder="Ex: SEPLAN"
                            wire:model="form.sgl_organizacao"
                            required
                        >
                    </div>
                    @error('form.sgl_organizacao')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-lg-8">
                    <label for="nom_organizacao" class="form-label-modern">
                        {{ __('Nome da Organização') }} <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                        <input
                            id="nom_organizacao"
                            type="text"
                            class="form-control @error('form.nom_organizacao') is-invalid @enderror"
                            placeholder="Ex: Secretaria de Planejamento"
                            wire:model="form.nom_organizacao"
                            required
                        >
                    </div>
                    @error('form.nom_organizacao')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="rel_cod_organizacao" class="form-label-modern">
                        {{ __('Organização Pai') }}
                    </label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text"><i class="bi bi-diagram-2"></i></span>
                        <select
                            id="rel_cod_organizacao"
                            class="form-select form-select-modern @error('form.rel_cod_organizacao') is-invalid @enderror"
                            wire:model="form.rel_cod_organizacao"
                        >
                            <option value="">{{ __('Nenhuma (Raiz)') }}</option>
                            @foreach ($this->organizacoesPai as $orgPai)
                                <option value="{{ $orgPai['id'] }}">{{ $orgPai['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-text small">{{ __('Selecione a organização superior na hierarquia. Deixe vazio para criar uma organização raiz.') }}</div>
                    @error('form.rel_cod_organizacao')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <div class="modal-footer-modern">
                <span class="text-muted small">
                    <span class="text-danger">*</span> {{ __('Campos obrigatórios') }}
                </span>
                <div class="d-flex gap-2">
                    <x-secondary-button wire:click="closeFormModal" wire:loading.attr="disabled" class="btn-modern">
                        {{ __('Cancelar') }}
                    </x-secondary-button>

                    <x-button type="button" wire:click="save" wire:loading.attr="disabled" class="btn-save-modern">
                        <span wire:loading.remove wire:target="save">
                            <i class="bi bi-check-lg me-1"></i>{{ __('Salvar') }}
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
    <x-confirmation-modal wire:key="org-delete-modal" wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="modal-icon modal-icon-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold">{{ __('Excluir Organização') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Esta ação não pode ser desfeita') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation">
                <p class="mb-2">
                    {{ __('Tem certeza que deseja excluir a organização') }} <strong class="text-body-emphasis">{{ $editing?->nom_organizacao }}</strong>?
                </p>
                <p class="text-muted small mb-0">
                    {{ __('Todos os dados associados a esta organização podem ser afetados.') }}
                </p>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="cancelDelete" wire:loading.attr="disabled" class="btn-modern">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-danger-button wire:click="delete" wire:loading.attr="disabled" class="btn-delete-modern">
                <span wire:loading.remove wire:target="delete">
                    <i class="bi bi-trash me-1"></i>{{ __('Excluir') }}
                </span>
                <span wire:loading wire:target="delete">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                    {{ __('Excluindo...') }}
                </span>
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
