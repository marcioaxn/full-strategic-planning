<div>
    {{-- Page Header --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="header-icon gradient-theme-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ __('Leads') }}</h1>
                <span class="badge-modern badge-count">
                    {{ $leads->total() }}
                </span>
            </div>
            <p class="text-muted mb-0">
                {{ __('Manage your leads and track their progress through the sales pipeline.') }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div wire:loading.delay.short wire:target="search,status,resetFilters,save,delete,exportCsv,create,edit" class="text-primary">
                <span class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">{{ __('Loading...') }}</span>
                </span>
            </div>

            <x-action-button
                variant="outline-secondary"
                icon="download"
                tooltip="{{ __('Export CSV') }}"
                wire:click="exportCsv"
                wire:loading.attr="disabled"
                class="btn-action-modern"
            >
                <span class="d-none d-md-inline">{{ __('Export') }}</span>
            </x-action-button>

            <x-action-button
                variant="primary"
                icon="plus-lg"
                tooltip="{{ __('Add new lead') }}"
                wire:click="create"
                class="btn-action-primary gradient-theme-btn"
            >
                {{ __('New Lead') }}
            </x-action-button>
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
            <button type="button" class="btn-close btn-close-modern" aria-label="{{ __('Close') }}" wire:click="$set('flashMessage', null)"></button>
        </div>
    @endif

    {{-- Filters Card --}}
    <div class="card card-modern filters-card mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-6 col-lg-5">
                    <label for="lead-search" class="form-label-modern">
                        <i class="bi bi-search me-2"></i>{{ __('Search') }}
                    </label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            id="lead-search"
                            type="search"
                            class="form-control"
                            placeholder="{{ __('Search by name, email, phone...') }}"
                            wire:model.live.debounce.250ms="search"
                        >
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <label for="lead-status" class="form-label-modern">
                        <i class="bi bi-funnel me-2"></i>{{ __('Status') }}
                    </label>
                    <select id="lead-status" class="form-select form-select-modern" wire:model.live="status">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    @if ($search !== '' || $status !== 'all')
                        <button type="button" class="btn btn-outline-secondary btn-modern w-100" wire:click="resetFilters">
                            <i class="bi bi-x-lg me-2"></i>{{ __('Clear') }}
                        </button>
                    @endif
                </div>
            </div>

            @if ($search !== '' || $status !== 'all')
                <div class="active-filters">
                    <span class="text-muted small fw-semibold">{{ __('Active filters:') }}</span>

                    @if ($search !== '')
                        <button type="button" class="filter-tag filter-tag-primary" wire:click="$set('search', '')">
                            <i class="bi bi-search"></i>
                            <span>"{{ Str::limit($search, 15) }}"</span>
                            <i class="bi bi-x"></i>
                        </button>
                    @endif

                    @if ($status !== 'all')
                        <button type="button" class="filter-tag filter-tag-secondary" wire:click="$set('status', 'all')">
                            <i class="bi bi-funnel"></i>
                            <span>{{ $statuses[$status] ?? $status }}</span>
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
                        <th scope="col" class="ps-4">{{ __('Lead') }}</th>
                        <th scope="col">{{ __('Team') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Updated') }}</th>
                        <th scope="col" class="text-end pe-4">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody wire:loading.class="loading-opacity" wire:target="search,status,resetFilters">
                    @forelse ($leads as $lead)
                        <tr class="table-row-hover" wire:key="lead-row-{{ $lead->id }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-modern">
                                        {{ strtoupper(substr($lead->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-body-emphasis">{{ $lead->name }}</div>
                                        @if ($lead->email)
                                            <div class="text-muted small">
                                                <i class="bi bi-envelope me-1"></i>{{ $lead->email }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($lead->entity)
                                    <span class="badge-modern badge-secondary">
                                        <i class="bi bi-people me-1"></i>{{ $lead->entity }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-status {{ $lead->status_badge_class }}">
                                    {{ $lead->status_label }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                <i class="bi bi-clock me-1"></i>{{ $lead->updated_at?->diffForHumans() }}
                            </td>
                            <td class="text-end pe-4">
                                <div class="action-buttons">
                                    <x-action-button variant="outline-primary" icon="pencil" tooltip="{{ __('Edit') }}" wire:click="edit({{ $lead->id }})" class="btn-action-icon" />
                                    <x-action-button variant="outline-danger" icon="trash" tooltip="{{ __('Delete') }}" wire:click="confirmDelete({{ $lead->id }})" class="btn-action-icon" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-5">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-inbox"></i>
                                    </div>
                                    <h5 class="empty-state-title">{{ __('No leads found') }}</h5>
                                    <p class="empty-state-text">
                                        @if ($search !== '' || $status !== 'all')
                                            {{ __('Try adjusting your filters or search terms.') }}
                                        @else
                                            {{ __('Get started by creating your first lead.') }}
                                        @endif
                                    </p>
                                    @if ($search !== '' || $status !== 'all')
                                        <button type="button" class="btn btn-outline-secondary btn-modern" wire:click="resetFilters">
                                            <i class="bi bi-x-lg me-2"></i>{{ __('Clear filters') }}
                                        </button>
                                    @else
                                        <x-action-button variant="primary" icon="plus-lg" wire:click="create" class="btn-action-primary gradient-theme-btn">
                                            {{ __('Create Lead') }}
                                        </x-action-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
            <div class="card-footer pagination-footer">
                <span class="pagination-info">
                    {{ __('Showing') }} <span class="fw-semibold">{{ $leads->firstItem() }}</span> {{ __('to') }} <span class="fw-semibold">{{ $leads->lastItem() }}</span> {{ __('of') }} <span class="fw-semibold">{{ $leads->total() }}</span> {{ __('results') }}
                </span>
                {{ $leads->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    {{-- Mobile Cards --}}
    <div class="d-md-none">
        <div class="mobile-cards-container" wire:loading.class="loading-opacity" wire:target="search,status,resetFilters">
            @forelse ($leads as $lead)
                <div class="card card-modern mobile-lead-card" wire:key="lead-card-{{ $lead->id }}">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-modern avatar-mobile">
                                    {{ strtoupper(substr($lead->name, 0, 2)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold">{{ $lead->name }}</h6>
                                    @if($lead->entity)
                                        <span class="text-muted small">{{ $lead->entity }}</span>
                                    @endif
                                </div>
                            </div>
                            <span class="badge-status {{ $lead->status_badge_class }}">
                                {{ $lead->status_label }}
                            </span>
                        </div>

                        @if($lead->email || $lead->phone)
                            <div class="mobile-contact-info">
                                @if ($lead->email)
                                    <div class="contact-item">
                                        <i class="bi bi-envelope"></i>{{ $lead->email }}
                                    </div>
                                @endif
                                @if ($lead->phone)
                                    <div class="contact-item">
                                        <i class="bi bi-telephone"></i>{{ $lead->phone }}
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="mobile-card-footer">
                            <span class="text-muted small">
                                <i class="bi bi-clock me-1"></i>{{ $lead->updated_at?->diffForHumans() }}
                            </span>
                            <div class="action-buttons">
                                <x-action-button variant="outline-primary" icon="pencil" tooltip="{{ __('Edit') }}" wire:click="edit({{ $lead->id }})" size="sm" class="btn-action-icon">
                                    <span class="d-none d-sm-inline">{{ __('Edit') }}</span>
                                </x-action-button>
                                <x-action-button variant="outline-danger" icon="trash" tooltip="{{ __('Delete') }}" wire:click="confirmDelete({{ $lead->id }})" size="sm" class="btn-action-icon" />
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card card-modern">
                    <div class="card-body p-4">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bi bi-inbox"></i>
                            </div>
                            <h5 class="empty-state-title">{{ __('No leads found') }}</h5>
                            <p class="empty-state-text">
                                @if ($search !== '' || $status !== 'all')
                                    {{ __('Try adjusting your filters.') }}
                                @else
                                    {{ __('Create your first lead to get started.') }}
                                @endif
                            </p>
                            @if ($search !== '' || $status !== 'all')
                                <button type="button" class="btn btn-outline-secondary btn-modern" wire:click="resetFilters">
                                    <i class="bi bi-x-lg me-2"></i>{{ __('Clear filters') }}
                                </button>
                            @else
                                <x-action-button variant="primary" icon="plus-lg" wire:click="create" class="btn-action-primary gradient-theme-btn">
                                    {{ __('Create Lead') }}
                                </x-action-button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if($leads->hasPages())
            <div class="mobile-pagination">
                <span class="pagination-info small">
                    {{ $leads->firstItem() }}-{{ $leads->lastItem() }} {{ __('of') }} {{ $leads->total() }}
                </span>
                {{ $leads->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    <x-dialog-modal wire:key="leads-form-modal" wire:model.live="showFormModal" maxWidth="2xl">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="modal-icon modal-icon-primary">
                    <i class="bi bi-{{ $editing ? 'pencil' : 'plus-lg' }}"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold">{{ $editing ? __('Edit Lead') : __('New Lead') }}</h5>
                    <p class="text-muted small mb-0">{{ $editing ? __('Update the lead information') : __('Fill in the details to create a new lead') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <label for="lead-name" class="form-label-modern">
                        {{ __('Name') }} <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input
                            id="lead-name"
                            type="text"
                            class="form-control @error('form.name') is-invalid @enderror"
                            placeholder="{{ __('Enter lead name') }}"
                            wire:model.defer="form.name"
                            required
                        >
                    </div>
                    @error('form.name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-lg-6">
                    <label for="lead-entity" class="form-label-modern">{{ __('Team') }}</label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text"><i class="bi bi-people"></i></span>
                        <input
                            id="lead-entity"
                            type="text"
                            class="form-control @error('form.entity') is-invalid @enderror"
                            placeholder="{{ __('e.g., Sales, Marketing') }}"
                            wire:model.defer="form.entity"
                        >
                    </div>
                    @error('form.entity')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-lg-4">
                    <label for="lead-status-form" class="form-label-modern">
                        {{ __('Status') }} <span class="text-danger">*</span>
                    </label>
                    <select
                        id="lead-status-form"
                        class="form-select form-select-modern @error('form.status') is-invalid @enderror"
                        wire:model.defer="form.status"
                    >
                        @foreach ($this->statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('form.status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-lg-4">
                    <label for="lead-email" class="form-label-modern">{{ __('Email') }}</label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input
                            id="lead-email"
                            type="email"
                            class="form-control @error('form.email') is-invalid @enderror"
                            placeholder="{{ __('email@example.com') }}"
                            wire:model.defer="form.email"
                        >
                    </div>
                    @error('form.email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-lg-4">
                    <label for="lead-phone" class="form-label-modern">{{ __('Phone') }}</label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                        <input
                            id="lead-phone"
                            type="text"
                            class="form-control @error('form.phone') is-invalid @enderror"
                            placeholder="{{ __('+1 (555) 123-4567') }}"
                            wire:model.defer="form.phone"
                        >
                    </div>
                    @error('form.phone')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="lead-notes" class="form-label-modern">{{ __('Notes') }}</label>
                    <textarea
                        id="lead-notes"
                        rows="3"
                        class="form-control form-control-modern @error('form.notes') is-invalid @enderror"
                        placeholder="{{ __('Add any additional notes or context...') }}"
                        wire:model.defer="form.notes"
                    ></textarea>
                    @error('form.notes')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <div class="modal-footer-modern">
                <span class="text-muted small">
                    <span class="text-danger">*</span> {{ __('Required fields') }}
                </span>
                <div class="d-flex gap-2">
                    <x-secondary-button wire:click="closeFormModal" wire:loading.attr="disabled" class="btn-modern">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-button type="button" wire:click="save" wire:loading.attr="disabled" class="btn-save-modern">
                        <span wire:loading.remove wire:target="save">
                            <i class="bi bi-check-lg me-1"></i>{{ __('Save') }}
                        </span>
                        <span wire:loading wire:target="save">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                            {{ __('Saving...') }}
                        </span>
                    </x-button>
                </div>
            </div>
        </x-slot>
    </x-dialog-modal>

    {{-- Delete Confirmation Modal --}}
    <x-confirmation-modal wire:key="leads-delete-modal" wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="modal-icon modal-icon-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold">{{ __('Delete Lead') }}</h5>
                    <p class="text-muted small mb-0">{{ __('This action cannot be undone') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation">
                <p class="mb-2">
                    {{ __('Are you sure you want to delete') }} <strong class="text-body-emphasis">{{ $editing?->name }}</strong>?
                </p>
                <p class="text-muted small mb-0">
                    {{ __('All data associated with this lead will be permanently removed.') }}
                </p>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="cancelDelete" wire:loading.attr="disabled" class="btn-modern">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button wire:click="delete" wire:loading.attr="disabled" class="btn-delete-modern">
                <span wire:loading.remove wire:target="delete">
                    <i class="bi bi-trash me-1"></i>{{ __('Delete') }}
                </span>
                <span wire:loading wire:target="delete">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                    {{ __('Deleting...') }}
                </span>
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    <style>
    /* ========== Header ========== */
    .leads-header .header-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        /* Uses global .gradient-theme-icon class */
    }

    .badge-modern.badge-count {
        background: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    [data-bs-theme="dark"] .badge-modern.badge-count {
        background: rgba(var(--bs-primary-rgb), 0.2);
        color: #6ea8fe;
    }

    /* ========== Buttons ========== */
    .btn-action-modern {
        border-radius: 10px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-action-primary {
        border-radius: 10px;
        font-weight: 600;
        /* Uses global .gradient-theme-btn class */
    }

    /* ========== Alerts ========== */
    .alert-modern {
        border-radius: 12px;
        border: none;
        padding: 1rem 1.25rem;
    }

    .alert-modern .alert-icon {
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    [data-bs-theme="dark"] .alert-modern {
        background: rgba(var(--bs-success-rgb), 0.15);
        border: 1px solid rgba(var(--bs-success-rgb), 0.3);
        color: #75b798;
    }

    /* ========== Cards ========== */
    .card-modern {
        border-radius: 16px;
        border: 1px solid var(--bs-border-color);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }

    [data-bs-theme="dark"] .card-modern {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .filters-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    [data-bs-theme="dark"] .filters-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
    }

    /* ========== Form Elements ========== */
    .form-label-modern {
        font-weight: 600;
        color: var(--bs-body-color);
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .input-group-modern {
        border-radius: 10px;
        overflow: hidden;
        display: flex;
    }

    .input-group-modern .input-group-text {
        background: transparent;
        border: 2px solid var(--bs-border-color);
        border-right: none;
        color: var(--bs-secondary);
        border-radius: 10px 0 0 10px;
    }

    .input-group-modern .form-control,
    .input-group-modern .form-select {
        border: 2px solid var(--bs-border-color);
        border-left: none;
        border-radius: 0 10px 10px 0;
    }

    .input-group-modern .form-control:focus,
    .input-group-modern .form-select:focus {
        border-color: var(--bs-primary);
        box-shadow: none;
    }

    .input-group-modern .form-control:focus ~ .input-group-text,
    .input-group-modern .form-control:focus + .input-group-text {
        border-color: var(--bs-primary);
    }

    .input-group-modern:focus-within .input-group-text {
        border-color: var(--bs-primary);
    }

    .form-select-modern,
    .form-control-modern {
        border-radius: 10px;
        border: 2px solid var(--bs-border-color);
        transition: all 0.2s ease;
    }

    .form-select-modern:focus,
    .form-control-modern:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.1);
    }

    [data-bs-theme="dark"] .input-group-modern .input-group-text,
    [data-bs-theme="dark"] .input-group-modern .form-control,
    [data-bs-theme="dark"] .input-group-modern .form-select,
    [data-bs-theme="dark"] .form-select-modern,
    [data-bs-theme="dark"] .form-control-modern {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.9);
    }

    [data-bs-theme="dark"] .input-group-modern .input-group-text {
        color: rgba(255, 255, 255, 0.5);
    }

    /* ========== Filter Tags ========== */
    .active-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--bs-border-color);
    }

    [data-bs-theme="dark"] .active-filters {
        border-color: rgba(255, 255, 255, 0.1);
    }

    .filter-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.75rem;
        border-radius: 50px;
        border: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .filter-tag-primary {
        background: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
    }

    .filter-tag-primary:hover {
        background: rgba(var(--bs-primary-rgb), 0.2);
    }

    .filter-tag-secondary {
        background: rgba(var(--bs-secondary-rgb), 0.1);
        color: var(--bs-secondary);
    }

    .filter-tag-secondary:hover {
        background: rgba(var(--bs-secondary-rgb), 0.2);
    }

    [data-bs-theme="dark"] .filter-tag-primary {
        background: rgba(var(--bs-primary-rgb), 0.2);
        color: #6ea8fe;
    }

    [data-bs-theme="dark"] .filter-tag-secondary {
        background: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.7);
    }

    /* ========== Table ========== */
    .table-card {
        overflow: hidden;
    }

    .table-modern {
        margin: 0;
    }

    .table-modern thead {
        background: rgba(var(--bs-secondary-rgb), 0.05);
    }

    .table-modern thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: var(--bs-secondary);
        padding: 1rem;
        border: none;
    }

    .table-modern tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-color: var(--bs-border-color);
    }

    [data-bs-theme="dark"] .table-modern thead {
        background: rgba(255, 255, 255, 0.03);
    }

    [data-bs-theme="dark"] .table-modern tbody td {
        border-color: rgba(255, 255, 255, 0.05);
    }

    .table-row-hover {
        transition: all 0.2s ease;
    }

    .table-row-hover:hover {
        background: rgba(var(--bs-primary-rgb), 0.03);
    }

    [data-bs-theme="dark"] .table-row-hover:hover {
        background: rgba(255, 255, 255, 0.03);
    }

    /* ========== Avatars ========== */
    .avatar-modern {
        width: 40px;
        height: 40px;
        background: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        flex-shrink: 0;
    }

    .avatar-mobile {
        width: 48px;
        height: 48px;
        font-size: 1rem;
    }

    [data-bs-theme="dark"] .avatar-modern {
        background: rgba(var(--bs-primary-rgb), 0.2);
        color: #6ea8fe;
    }

    /* ========== Badges ========== */
    .badge-modern {
        padding: 0.375rem 0.75rem;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.8125rem;
    }

    .badge-secondary {
        background: rgba(var(--bs-secondary-rgb), 0.1);
        color: var(--bs-secondary);
    }

    [data-bs-theme="dark"] .badge-secondary {
        background: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.7);
    }

    .badge-status {
        padding: 0.375rem 0.75rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    /* ========== Action Buttons ========== */
    .action-buttons {
        display: inline-flex;
        gap: 0.5rem;
    }

    .btn-action-icon {
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn-action-icon:hover {
        transform: translateY(-2px);
    }

    /* ========== Empty State ========== */
    .empty-state {
        text-align: center;
        padding: 2rem;
    }

    .empty-state-icon {
        font-size: 4rem;
        color: var(--bs-secondary);
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state-title {
        font-weight: 700;
        color: var(--bs-body-color);
        margin-bottom: 0.5rem;
    }

    .empty-state-text {
        color: var(--bs-secondary);
        font-size: 0.9375rem;
        margin-bottom: 1.5rem;
    }

    /* ========== Pagination ========== */
    .pagination-footer {
        background: transparent;
        border-top: 1px solid var(--bs-border-color);
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    [data-bs-theme="dark"] .pagination-footer {
        border-color: rgba(255, 255, 255, 0.05);
    }

    .pagination-info {
        color: var(--bs-secondary);
        font-size: 0.875rem;
    }

    /* ========== Mobile Cards ========== */
    .mobile-cards-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .mobile-lead-card {
        transition: all 0.2s ease;
    }

    .mobile-lead-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    [data-bs-theme="dark"] .mobile-lead-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .mobile-contact-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding: 0.75rem;
        background: rgba(var(--bs-secondary-rgb), 0.05);
        border-radius: 8px;
    }

    [data-bs-theme="dark"] .mobile-contact-info {
        background: rgba(255, 255, 255, 0.03);
    }

    .contact-item {
        font-size: 0.875rem;
        color: var(--bs-secondary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .mobile-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 0.75rem;
        border-top: 1px solid var(--bs-border-color);
    }

    [data-bs-theme="dark"] .mobile-card-footer {
        border-color: rgba(255, 255, 255, 0.05);
    }

    .mobile-pagination {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    /* ========== Modal ========== */
    .modal-header-modern {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .modal-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .modal-icon-primary {
        background: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
    }

    .modal-icon-danger {
        background: rgba(var(--bs-danger-rgb), 0.1);
        color: var(--bs-danger);
    }

    [data-bs-theme="dark"] .modal-icon-primary {
        background: rgba(var(--bs-primary-rgb), 0.2);
        color: #6ea8fe;
    }

    [data-bs-theme="dark"] .modal-icon-danger {
        background: rgba(var(--bs-danger-rgb), 0.2);
        color: #ea868f;
    }

    .modal-footer-modern {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }

    .delete-confirmation {
        text-align: center;
        padding: 1.5rem 1rem;
    }

    .btn-modern {
        border-radius: 10px;
        font-weight: 500;
    }

    .btn-save-modern,
    .btn-delete-modern {
        border-radius: 10px;
        font-weight: 600;
    }

    /* ========== Loading State ========== */
    .loading-opacity {
        opacity: 0.5;
        pointer-events: none;
    }

    /* ========== Animations ========== */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .mobile-lead-card,
    .table-row-hover {
        animation: fadeIn 0.3s ease;
    }
    </style>
</div>
