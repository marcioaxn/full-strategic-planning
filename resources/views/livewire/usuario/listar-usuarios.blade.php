<div>
    {{-- Page Header --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="header-icon gradient-theme-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ __('Usuários') }}</h1>
                <span class="badge-modern badge-count">
                    {{ $usuarios->total() }}
                </span>
            </div>
            <p class="text-muted mb-0">
                {{ __('Gerencie os usuários, suas permissões e vínculos organizacionais.') }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div wire:loading.delay.short wire:target="search,save,delete,create,edit" class="text-primary">
                <span class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">{{ __('Carregando...') }}</span>
                </span>
            </div>

            @can('create', App\Models\User::class)
                <x-action-button
                    variant="primary"
                    icon="person-plus"
                    tooltip="{{ __('Adicionar novo usuário') }}"
                    wire:click="create"
                    class="btn-action-primary gradient-theme-btn"
                >
                    {{ __('Novo Usuário') }}
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
                <div class="col-12 col-md-5">
                    <label for="user-search" class="form-label-modern">
                        <i class="bi bi-search me-2"></i>{{ __('Buscar') }}
                    </label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            id="user-search"
                            type="search"
                            class="form-control"
                            placeholder="{{ __('Buscar por nome ou email...') }}"
                            wire:model.live.debounce.250ms="search"
                        >
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <label for="user-status" class="form-label-modern">
                        <i class="bi bi-toggle-on me-2"></i>{{ __('Status') }}
                    </label>
                    <select id="user-status" class="form-select form-select-modern" wire:model.live="filtroAtivo">
                        <option value="todos">Todos</option>
                        <option value="ativos">Ativos</option>
                        <option value="inativos">Inativos</option>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    @if ($search !== '' || $filtroAtivo !== 'todos')
                        <button type="button" class="btn btn-outline-secondary btn-modern w-100" wire:click="resetFilters">
                            <i class="bi bi-x-lg me-2"></i>{{ __('Limpar Filtros') }}
                        </button>
                    @endif
                </div>
            </div>

            @if ($search !== '' || $filtroAtivo !== 'todos')
                <div class="active-filters">
                    <span class="text-muted small fw-semibold">{{ __('Filtros ativos:') }}</span>
                    @if($search !== '')
                        <button type="button" class="filter-tag filter-tag-primary" wire:click="$set('search', '')">
                            <i class="bi bi-search"></i>
                            <span>"{{ Str::limit($search, 15) }}"</span>
                            <i class="bi bi-x"></i>
                        </button>
                    @endif
                     @if($filtroAtivo !== 'todos')
                        <button type="button" class="filter-tag filter-tag-secondary" wire:click="$set('filtroAtivo', 'todos')">
                            <i class="bi bi-toggle-on"></i>
                            <span>{{ ucfirst($filtroAtivo) }}</span>
                            <i class="bi bi-x"></i>
                        </button>
                    @endif
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
                        <th scope="col" class="ps-4">{{ __('Usuário') }}</th>
                        <th scope="col">{{ __('Vínculos') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col" class="text-end pe-4">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody wire:loading.class="loading-opacity" wire:target="search,resetFilters">
                    @forelse ($usuarios as $user)
                        <tr class="table-row-hover" wire:key="user-row-{{ $user->id }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-modern">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-body-emphasis">{{ $user->name }}</div>
                                        <div class="text-muted small">
                                            <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $vinculosCount = $user->organizacoes->count();
                                @endphp
                                @if($vinculosCount > 0)
                                    <span class="badge-modern badge-secondary" title="{{ $user->organizacoes->pluck('sgl_organizacao')->join(', ') }}">
                                        <i class="bi bi-diagram-3 me-1"></i>{{ $vinculosCount }} Organizações
                                    </span>
                                @else
                                    <span class="text-muted small">Sem vínculos</span>
                                @endif
                                
                                @if($user->isSuperAdmin())
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1">Super Admin</span>
                                @endif
                            </td>
                            <td>
                                @if($user->ativo)
                                    <span class="badge badge-status status-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>Ativo
                                    </span>
                                @else
                                    <span class="badge badge-status status-warning">
                                        <i class="bi bi-dash-circle-fill me-1"></i>Inativo
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="action-buttons">
                                    @can('update', $user)
                                        <x-action-button variant="outline-primary" icon="pencil" tooltip="{{ __('Editar') }}" wire:click="edit('{{ $user->id }}')" class="btn-action-icon" />
                                    @endcan
                                    
                                    @can('delete', $user)
                                        <x-action-button variant="outline-danger" icon="trash" tooltip="{{ __('Excluir') }}" wire:click="confirmDelete('{{ $user->id }}')" class="btn-action-icon" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-5">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-person-x"></i>
                                    </div>
                                    <h5 class="empty-state-title">{{ __('Nenhum usuário encontrado') }}</h5>
                                    <p class="empty-state-text">
                                        {{ __('Tente ajustar seus termos de busca ou filtros.') }}
                                    </p>
                                    @if ($search !== '' || $filtroAtivo !== 'todos')
                                        <button type="button" class="btn btn-outline-secondary btn-modern" wire:click="resetFilters">
                                            <i class="bi bi-x-lg me-2"></i>{{ __('Limpar filtros') }}
                                        </button>
                                    @else
                                        @can('create', App\Models\User::class)
                                            <x-action-button variant="primary" icon="plus-lg" wire:click="create" class="btn-action-primary gradient-theme-btn">
                                                {{ __('Criar Usuário') }}
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

        @if($usuarios->hasPages())
            <div class="card-footer pagination-footer">
                <span class="pagination-info">
                    {{ __('Mostrando') }} <span class="fw-semibold">{{ $usuarios->firstItem() }}</span> {{ __('a') }} <span class="fw-semibold">{{ $usuarios->lastItem() }}</span> {{ __('de') }} <span class="fw-semibold">{{ $usuarios->total() }}</span> {{ __('resultados') }}
                </span>
                {{ $usuarios->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    {{-- Mobile Cards --}}
    <div class="d-md-none">
        <div class="mobile-cards-container" wire:loading.class="loading-opacity" wire:target="search,resetFilters">
            @forelse ($usuarios as $user)
                <div class="card card-modern mobile-lead-card" wire:key="user-card-{{ $user->id }}">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-modern avatar-mobile">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold">{{ $user->name }}</h6>
                                    <span class="text-muted small">{{ $user->email }}</span>
                                </div>
                            </div>
                            @if($user->ativo)
                                <span class="badge badge-status status-success">Ativo</span>
                            @else
                                <span class="badge badge-status status-warning">Inativo</span>
                            @endif
                        </div>

                        <div class="mobile-contact-info">
                            <div class="contact-item">
                                <i class="bi bi-diagram-3"></i>
                                {{ $user->organizacoes->count() }} Organizações vinculadas
                            </div>
                        </div>

                        <div class="mobile-card-footer">
                            <div class="action-buttons w-100 justify-content-end">
                                @can('update', $user)
                                    <x-action-button variant="outline-primary" icon="pencil" tooltip="{{ __('Editar') }}" wire:click="edit('{{ $user->id }}')" size="sm" class="btn-action-icon">
                                        <span class="d-none d-sm-inline">{{ __('Editar') }}</span>
                                    </x-action-button>
                                @endcan
                                
                                @can('delete', $user)
                                    <x-action-button variant="outline-danger" icon="trash" tooltip="{{ __('Excluir') }}" wire:click="confirmDelete('{{ $user->id }}')" size="sm" class="btn-action-icon" />
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card card-modern">
                    <div class="card-body p-4 text-center">
                        <p class="text-muted">{{ __('Nenhum usuário encontrado.') }}</p>
                    </div>
                </div>
            @endforelse
        </div>
        
        @if($usuarios->hasPages())
             <div class="mobile-pagination">
                {{ $usuarios->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    <x-dialog-modal wire:key="user-form-modal" wire:model.live="showFormModal" maxWidth="3xl">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="modal-icon modal-icon-primary">
                    <i class="bi bi-{{ $editing ? 'pencil' : 'person-plus' }}"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold">{{ $editing ? __('Editar Usuário') : __('Novo Usuário') }}</h5>
                    <p class="text-muted small mb-0">{{ $editing ? __('Atualize as informações de acesso') : __('Preencha os detalhes para criar um novo usuário') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="row g-4">
                {{-- Dados Pessoais --}}
                <div class="col-12">
                    <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary">
                        <i class="bi bi-person-badge me-2"></i>{{ __('Dados de Acesso') }}
                    </h6>
                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <label for="name" class="form-label-modern">
                                {{ __('Nome Completo') }} <span class="text-danger">*</span>
                            </label>
                            <input
                                id="name"
                                type="text"
                                class="form-control form-control-modern @error('form.name') is-invalid @enderror"
                                placeholder="Ex: João da Silva"
                                wire:model="form.name"
                                required
                            >
                            @error('form.name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-lg-6">
                            <label for="email" class="form-label-modern">
                                {{ __('E-mail') }} <span class="text-danger">*</span>
                            </label>
                            <input
                                id="email"
                                type="email"
                                class="form-control form-control-modern @error('form.email') is-invalid @enderror"
                                placeholder="joao@exemplo.com"
                                wire:model="form.email"
                                required
                            >
                            @error('form.email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-lg-6">
                            <label for="password" class="form-label-modern">
                                {{ __('Senha') }}
                                @if(!$editing) <span class="text-danger">*</span> @else <span class="text-muted fw-normal small">(Deixe em branco para manter)</span> @endif
                            </label>
                            <input
                                id="password"
                                type="password"
                                class="form-control form-control-modern @error('form.password') is-invalid @enderror"
                                placeholder="********"
                                wire:model="form.password"
                            >
                            @error('form.password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-lg-3">
                            <label for="ativo" class="form-label-modern">{{ __('Status da Conta') }}</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" id="ativo" wire:model="form.ativo">
                                <label class="form-check-label" for="ativo">
                                    {{ $form['ativo'] ? __('Ativo') : __('Inativo') }}
                                </label>
                            </div>
                        </div>

                        <div class="col-12 col-lg-3">
                            <label for="trocarsenha" class="form-label-modern">{{ __('Exigir Troca de Senha') }}</label>
                            <select id="trocarsenha" class="form-select form-select-modern" wire:model="form.trocarsenha">
                                <option value="0">Não</option>
                                <option value="1">Sim (No próximo login)</option>
                                <option value="2">Já trocou (Histórico)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Vínculos --}}
                <div class="col-12">
                    <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary">
                        <i class="bi bi-diagram-3 me-2"></i>{{ __('Vínculos Organizacionais') }}
                    </h6>
                    
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body p-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-5">
                                    <label class="form-label-modern small mb-1">{{ __('Organização') }}</label>
                                    <select class="form-select form-select-modern form-select-sm" wire:model="vinculoTemporario.org_id">
                                        <option value="">Selecione...</option>
                                        @foreach($this->organizacoesOptions as $org)
                                            <option value="{{ $org['id'] }}">{{ $org['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-5">
                                    <label class="form-label-modern small mb-1">{{ __('Perfil de Acesso') }}</label>
                                    <select class="form-select form-select-modern form-select-sm" wire:model="vinculoTemporario.perfil_id">
                                        <option value="">Selecione...</option>
                                        @foreach($this->perfisOptions as $perfil)
                                            <option value="{{ $perfil['id'] }}">{{ $perfil['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-2">
                                    <button type="button" class="btn btn-primary btn-sm w-100 gradient-theme-btn" wire:click="adicionarVinculo">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                            @error('vinculoTemporario')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="table-responsive border rounded-3 bg-white" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3">{{ __('Organização') }}</th>
                                    <th>{{ __('Perfil') }}</th>
                                    <th class="text-end pe-3" style="width: 50px;">{{ __('Ação') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($form['vinculos'] as $index => $vinculo)
                                    <tr>
                                        <td class="ps-3">{{ $vinculo['org_label'] }}</td>
                                        <td>{{ $vinculo['perfil_label'] }}</td>
                                        <td class="text-end pe-3">
                                            <button type="button" class="btn btn-link text-danger p-0" wire:click="removerVinculo({{ $index }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted small py-3">
                                            {{ __('Nenhum vínculo adicionado.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                    <x-secondary-button wire:click="closeFormModal" wire:loading.attr="disabled" class="btn-modern">
                        {{ __('Cancelar') }}
                    </x-secondary-button>

                    <x-button type="button" wire:click="save" wire:loading.attr="disabled" class="btn-save-modern">
                        <span wire:loading.remove wire:target="save">
                            <i class="bi bi-check-lg me-1"></i>{{ __('Salvar Usuário') }}
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
    <x-confirmation-modal wire:key="user-delete-modal" wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="modal-icon modal-icon-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold">{{ __('Excluir Usuário') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Esta ação é definitiva') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation">
                <p class="mb-2">
                    {{ __('Tem certeza que deseja excluir o usuário') }} <strong class="text-body-emphasis">{{ $editing?->name }}</strong>?
                </p>
                <p class="text-muted small mb-0">
                    {{ __('O usuário perderá o acesso ao sistema imediatamente.') }}
                </p>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="cancelDelete" wire:loading.attr="disabled" class="btn-modern">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-danger-button wire:click="delete" wire:loading.attr="disabled" class="btn-delete-modern">
                <span wire:loading.remove wire:target="delete">
                    <i class="bi bi-trash me-1"></i>{{ __('Excluir Permanentemente') }}
                </span>
                <span wire:loading wire:target="delete">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                    {{ __('Excluindo...') }}
                </span>
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
