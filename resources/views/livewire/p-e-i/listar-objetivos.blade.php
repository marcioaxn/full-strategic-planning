<div>
    {{-- Page Header --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="header-icon gradient-theme-icon">
                    <i class="bi bi-bullseye"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ __('Objetivos') }}</h1>
            </div>
            <p class="text-muted mb-0">
                @if($peiAtivo)
                    {{ __('Objetivos definidos para o ciclo:') }} <strong>{{ $peiAtivo->dsc_pei }}</strong>
                @else
                    <span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i> {{ __('Nenhum Ciclo PEI Ativo encontrado.') }}</span>
                @endif
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($peiAtivo)
                <x-action-button
                    variant="primary"
                    icon="plus-lg"
                    wire:click="create"
                    class="btn-action-primary gradient-theme-btn px-4"
                >
                    {{ __('Novo Objetivo') }}
                </x-action-button>
            @endif
        </div>
    </div>

    {{-- Mentor de IA --}}
    @if($peiAtivo && $perspectivas->isNotEmpty() && $aiEnabled)
        <div class="card card-modern border-0 shadow-sm pei-help-card-gradient mb-4">
            <div class="card-body p-4 text-white">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white bg-opacity-25 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-robot fs-4 text-white"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-white">{{ __('Mentor de IA') }}</h5>
                            <p class="mb-0 text-white-50 small">
                                {{ __('Posso sugerir objetivos estratégicos baseados na missão e visão da sua unidade.') }}
                            </p>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="col-auto">
                            <select wire:model.live="cod_perspectiva" class="form-select form-select-sm border-0 shadow-sm" style="min-width: 200px;">
                                <option value="">{{ __('Sugestão para qual perspectiva?') }}</option>
                                @foreach($perspectivas as $p)
                                    <option value="{{ $p->cod_perspectiva }}">{{ $p->dsc_perspectiva }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button wire:click="pedirAjudaIA" wire:loading.attr="disabled" class="btn btn-light text-primary fw-bold shadow-sm px-4 py-1 rounded-pill btn-sm">
                            <span wire:loading.remove wire:target="pedirAjudaIA">
                                <i class="bi bi-magic me-2"></i>{{ __('Gerar Objetivos') }}
                            </span>
                            <span wire:loading wire:target="pedirAjudaIA">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                            </span>
                        </button>
                    </div>
                </div>

            @if($aiSuggestion)
                <div class="ai-insight-card animate-fade-in">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-robot text-primary"></i>
                            <h6 class="fw-bold mb-0">{{ __('Objetivos Recomendados pelo Mentor IA') }}</h6>
                        </div>
                        <button type="button" class="btn-close small" style="font-size: 0.7rem;" wire:click="$set('aiSuggestion', '')"></button>
                    </div>
                    <div class="card-body">
                        @if(is_array($aiSuggestion))
                            <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                                @foreach($aiSuggestion as $obj)
                                    <div class="list-group-item d-flex align-items-start justify-content-between p-3 bg-light bg-opacity-25 hover-bg-white transition-all gap-3">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark">{{ $obj['nome'] }}</div>
                                            <p class="small text-muted mb-0 mt-1 lh-sm">{{ $obj['descricao'] }}</p>
                                        </div>
                                        <button wire:click="aplicarSugestao('{{ $obj['nome'] }}', '{{ $obj['descricao'] }}', {{ $obj['ordem'] ?? 1 }})" 
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

    @forelse($perspectivas as $perspectiva)
        <div class="card card-modern mb-4 border-0 shadow-sm">
            <div class="card-header border-bottom-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary small">
                            {{ $perspectiva->num_nivel_hierarquico_apresentacao }}
                        </div>
                        <h5 class="mb-0 fw-bold text-dark">{{ $perspectiva->dsc_perspectiva }}</h5>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">
                        {{ $perspectiva->objetivos->count() }} {{ __('objetivo(s)') }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-25 small text-muted text-uppercase fw-bold">
                            <tr>
                                <th class="ps-4" style="width: 80px;">{{ __('Ordem') }}</th>
                                <th>{{ __('Objetivo') }}</th>
                                <th>{{ __('Descrição') }}</th>
                                <th class="text-end pe-4">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($perspectiva->objetivos as $objetivo)
                                <tr>
                                    <td class="ps-4 fw-semibold text-primary">
                                        {{ $objetivo->num_nivel_hierarquico_apresentacao }}
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $objetivo->nom_objetivo }}</div>
                                    </td>
                                    <td>
                                        <div class="text-muted small text-truncate" style="max-width: 400px;" title="{{ $objetivo->dsc_objetivo }}">
                                            {{ $objetivo->dsc_objetivo ?: __('Sem descrição') }}
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1">
                                            <button wire:click="edit('{{ $objetivo->cod_objetivo }}')" class="btn btn-sm btn-icon btn-ghost-primary rounded-circle" title="{{ __('Editar') }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button wire:click="confirmDelete('{{ $objetivo->cod_objetivo }}')" class="btn btn-sm btn-icon btn-ghost-danger rounded-circle" title="{{ __('Excluir') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small italic">
                                        <i class="bi bi-inbox me-1"></i> {{ __('Nenhum objetivo cadastrado nesta perspectiva.') }}
                                        <button wire:click="create('{{ $perspectiva->cod_perspectiva }}')" class="btn btn-link btn-sm p-0 text-primary fw-bold ms-1">{{ __('Adicionar o primeiro') }}</button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="card card-modern border-dashed">
            <div class="card-body p-5 text-center">
                <div class="empty-state">
                    <div class="empty-state-icon mb-3 text-muted">
                        <i class="bi bi-layers fs-1"></i>
                    </div>
                    <h5 class="empty-state-title">{{ __('Nenhuma perspectiva encontrada') }}</h5>
                    <p class="empty-state-text">
                        {{ __('Antes de criar objetivos, você precisa cadastrar as Perspectivas do BSC para este ciclo.') }}
                    </p>
                    <a href="{{ route('pei.perspectivas') }}" class="btn btn-primary mt-3" wire:navigate>
                        <i class="bi bi-layers me-1"></i> {{ __('Gerenciar Perspectivas') }}
                    </a>
                </div>
            </div>
        </div>
    @endforelse

    {{-- Modal de Cadastro/Edição --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header gradient-theme-header text-white border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-{{ $objetivoId ? 'pencil' : 'plus-circle' }} me-2"></i>
                            {{ $objetivoId ? __('Editar Objetivo') : __('Novo Objetivo') }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-end mb-1">
                                        <label class="form-label fw-bold small text-muted text-uppercase mb-0">{{ __('Título do Objetivo') }} <span class="text-danger">*</span></label>
                                        <div wire:loading wire:target="nom_objetivo" class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    </div>
                                    <input type="text" wire:model.live.debounce.2000ms="nom_objetivo" class="form-control @error('nom_objetivo') is-invalid @enderror" placeholder="{{ __('Ex: Aumentar a eficiência operacional') }}">
                                    @error('nom_objetivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    
                                    @if($smartFeedback)
                                        <div class="mt-2 p-2 rounded bg-primary bg-opacity-10 border-start border-3 border-primary animate-fade-in position-relative">
                                            <button type="button" class="btn-close small position-absolute top-0 end-0 m-1" style="font-size: 0.5rem;" wire:click="$set('smartFeedback', '')"></button>
                                            <small class="text-primary fw-bold d-block mb-1"><i class="bi bi-info-circle me-1"></i>Feedback do Mentor SMART:</small>
                                            <small class="text-dark d-block pe-3">{{ $smartFeedback }}</small>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label fw-bold small text-muted text-uppercase">{{ __('Perspectiva BSC') }} <span class="text-danger">*</span></label>
                                    <select wire:model.live="cod_perspectiva" class="form-select @error('cod_perspectiva') is-invalid @enderror">
                                        <option value="">{{ __('Selecione uma perspectiva...') }}</option>
                                        @foreach($perspectivas as $p)
                                            <option value="{{ $p->cod_perspectiva }}">{{ $p->dsc_perspectiva }}</option>
                                        @endforeach
                                    </select>
                                    @error('cod_perspectiva') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted text-uppercase">{{ __('Ordem de Apresentação') }} <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="num_nivel_hierarquico_apresentacao" class="form-control @error('num_nivel_hierarquico_apresentacao') is-invalid @enderror" min="1">
                                    @error('num_nivel_hierarquico_apresentacao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold small text-muted text-uppercase">{{ __('Descrição detalhada') }}</label>
                                    <textarea wire:model="dsc_objetivo" class="form-control @error('dsc_objetivo') is-invalid @enderror" rows="4" placeholder="{{ __('Explique o que se pretende alcançar com este objetivo...') }}"></textarea>
                                    @error('dsc_objetivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0 p-3">
                            <button type="button" class="btn btn-secondary px-4" wire:click="$set('showModal', false)">{{ __('Cancelar') }}</button>
                            <button type="submit" class="btn btn-primary px-4 gradient-theme-btn">
                                <span wire:loading.remove wire:target="save">
                                    <i class="bi bi-check-lg me-1"></i> {{ $objetivoId ? __('Atualizar') : __('Salvar') }}
                                </span>
                                <span wire:loading wire:target="save">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                    {{ __('Processando...') }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Exclusão --}}
    <x-confirmation-modal wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="modal-icon modal-icon-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold text-dark">{{ __('Excluir Objetivo') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Esta ação é irreversível') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation">
                <p class="mb-2 text-dark">
                    {{ __('Tem certeza que deseja excluir este objetivo estratégico?') }}
                </p>
                <div class="alert alert-warning bg-warning-subtle border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção:</strong> Todos os indicadores e planos de ação vinculados a este objetivo serão removidos.
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDeleteModal', false)" wire:loading.attr="disabled" class="btn-modern">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-danger-button wire:click="delete" wire:loading.attr="disabled" class="btn-delete-modern ms-2">
                <span wire:loading.remove wire:target="delete">
                    <i class="bi bi-trash me-1"></i>{{ __('Excluir Agora') }}
                </span>
                <span wire:loading wire:target="delete">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                    {{ __('Excluindo...') }}
                </span>
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
