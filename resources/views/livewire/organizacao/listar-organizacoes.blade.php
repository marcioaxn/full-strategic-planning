<div>
    {{-- Page Header --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="icon-circle-header gradient-theme-icon">
                    <i class="bi bi-building-fill"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ __('Unidades Organizacionais') }}</h1>
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
                        @php $nivel = $org->getNivelHierarquico(); @endphp
                        <tr class="table-row-hover" wire:key="org-row-{{ $org->cod_organizacao }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3" style="margin-left: {{ $nivel * 30 }}px;">
                                    @if($nivel > 0)
                                        <i class="bi bi-arrow-return-right text-muted opacity-50"></i>
                                    @endif
                                    <div class="icon-circle-header avatar-modern {{ $org->isRaiz() ? 'bg-primary text-white' : '' }}">
                                        {{ strtoupper(substr($org->sgl_organizacao, 0, 3)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('organizacoes.detalhes', $org->cod_organizacao) }}" wire:navigate class="fw-semibold text-body-emphasis text-decoration-none hover-primary">
                                            {{ $org->nom_organizacao }}
                                        </a>
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
                                    <a href="{{ route('organizacoes.detalhes', $org->cod_organizacao) }}" wire:navigate class="btn btn-icon btn-outline-info" data-bs-toggle="tooltip" title="{{ __('Detalhar') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
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
                                <div class="icon-circle-header avatar-modern avatar-mobile">
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

    {{-- Create/Edit Modal Premium XL --}}
    <x-dialog-modal wire:key="org-form-modal" wire:model.live="showFormModal" maxWidth="xl">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="icon-circle-mini modal-icon-primary">
                    <i class="bi bi-{{ $editing ? 'pencil-square' : 'building-add' }}"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold">{{ $editing ? __('Configurar Unidade Organizacional') : __('Nova Unidade Organizacional') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Definição de estrutura e vínculo hierárquico') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="row g-4">
                {{-- Coluna Direita: Dados Básicos --}}
                <div class="col-lg-7">
                    <div class="card border-0 bg-light rounded-4 h-100">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Identificação e Marca</h6>
                            
                            <div class="row g-3">
                                <div class="col-12 col-lg-4">
                                    <label for="sgl_organizacao" class="form-label-modern">
                                        {{ __('Sigla') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-modern">
                                        <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                        <input id="sgl_organizacao" type="text" class="form-control @error('form.sgl_organizacao') is-invalid @enderror" placeholder="Ex: SEAE" wire:model="form.sgl_organizacao" required>
                                    </div>
                                    @error('form.sgl_organizacao') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-lg-8">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="nom_organizacao" class="form-label-modern mb-0">
                                            {{ __('Nome da Organização') }} <span class="text-danger">*</span>
                                        </label>
                                        @if($aiEnabled)
                                            <button type="button" wire:click="pedirAjudaIA" wire:loading.attr="disabled" class="btn btn-xs btn-outline-magic py-0" style="font-size: 0.65rem;">
                                                <i class="bi bi-robot me-1"></i> Sugerir com IA
                                            </button>
                                        @endif
                                    </div>
                                    
                                    @if($aiSuggestion)
                                        <div class="alert alert-magic bg-primary bg-opacity-10 border-0 p-3 mb-3 animate-fade-in rounded-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold text-primary small mb-0"><i class="bi bi-stars me-1"></i>Insights do Mentor IA</h6>
                                                <button type="button" class="btn-close" style="font-size: 0.5rem;" wire:click="$set('aiSuggestion', '')"></button>
                                            </div>
                                            <button type="button" wire:click="aplicarSugestaoSigla('{{ $aiSuggestion['sigla'] }}')" class="btn btn-sm btn-white border px-3">
                                                Sugestão de Sigla: <strong>{{ $aiSuggestion['sigla'] }}</strong> <i class="bi bi-arrow-down-short ms-1"></i>
                                            </button>
                                        </div>
                                    @endif

                                    <div class="input-group input-group-modern">
                                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                                        <input id="nom_organizacao" type="text" class="form-control @error('form.nom_organizacao') is-invalid @enderror" placeholder="Ex: Secretaria de Estado..." wire:model="form.nom_organizacao" required>
                                    </div>
                                    @error('form.nom_organizacao') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Coluna Direita: Hierarquia --}}
                <div class="col-lg-5">
                    <div class="card border-0 bg-light rounded-4 h-100">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Vínculo Hierárquico</h6>
                            
                            <div class="mb-4">
                                <label for="rel_cod_organizacao" class="form-label-modern">
                                    {{ __('Unidade Superior (Pai)') }}
                                    <x-tooltip title="Defina a unidade que está acima desta na árvore organizacional." />
                                </label>
                                <div class="input-group input-group-modern">
                                    <span class="input-group-text"><i class="bi bi-diagram-2"></i></span>
                                    <select id="rel_cod_organizacao" class="form-select @error('form.rel_cod_organizacao') is-invalid @enderror" wire:model="form.rel_cod_organizacao">
                                        <option value="">{{ __('Sem Unidade Superior (Unidade Raiz)') }}</option>
                                        @foreach($this->organizacoesPai as $org)
                                            <option value="{{ $org['id'] }}">{!! $org['label'] !!}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('form.rel_cod_organizacao') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="alert alert-info border-0 bg-white shadow-sm rounded-4 p-3 mb-0">
                                <div class="d-flex gap-3">
                                    <i class="bi bi-info-circle-fill text-primary fs-4"></i>
                                    <div class="small">
                                        <p class="fw-bold mb-1 text-dark">Dica de Estrutura:</p>
                                        <p class="text-muted mb-0">Unidades raiz são o topo da instituição. Subunidades herdam diretrizes estratégicas da unidade pai.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center w-100">
                <span class="text-muted small"><span class="text-danger">*</span> {{ __('Campos obrigatórios') }}</span>
                <div class="d-flex gap-2">
                    <button type="button" wire:click="closeFormModal" class="btn btn-light px-4 rounded-pill fw-bold text-muted">Cancelar</button>
                    <button type="button" wire:click="save" class="btn btn-primary gradient-theme-btn px-5 rounded-pill shadow-sm hover-scale">
                        <i class="bi bi-check-lg me-2"></i>{{ __('Salvar Unidade') }}
                    </button>
                </div>
            </div>
        </x-slot>
    </x-dialog-modal>

    {{-- Delete Confirmation Modal --}}
    <x-confirmation-modal wire:key="org-delete-modal" wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="icon-circle-mini modal-icon-danger">
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
                    <h3 class="fw-bold text-dark mb-3">Sucesso!</h3>
                    <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.6;">
                        A unidade <strong class="text-primary">"{{ $createdOrgName }}"</strong><br>
                        foi processada com êxito na hierarquia organizacional.
                    </p>
                    <button wire:click="closeSuccessModal" class="btn btn-primary gradient-theme-btn px-5 rounded-pill shadow hover-scale">
                        <i class="bi bi-check2-circle me-2"></i>Entendido
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
                    <h3 class="fw-bold text-dark mb-3">Falha na Operação</h3>
                    <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.6;">
                        Não foi possível salvar as alterações da organização.<br>
                        <span class="small text-danger fw-bold">{{ $errorMessage }}</span>
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
        .hover-primary:hover { color: var(--bs-primary) !important; }
    </style>
</div>
