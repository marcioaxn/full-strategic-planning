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
                    class="btn-action-primary gradient-theme-btn"
                >
                    {{ __('Novo Objetivo') }}
                </x-action-button>
            @endif
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
                                            <button wire:click="delete('{{ $objetivo->cod_objetivo }}')" wire:confirm="{{ __('Tem certeza que deseja excluir este objetivo?') }}" class="btn btn-sm btn-icon btn-ghost-danger rounded-circle" title="{{ __('Excluir') }}">
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
                                    <label class="form-label fw-bold small text-muted text-uppercase">{{ __('Título do Objetivo') }} <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="nom_objetivo" class="form-control @error('nom_objetivo') is-invalid @enderror" placeholder="{{ __('Ex: Aumentar a eficiência operacional') }}">
                                    @error('nom_objetivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
</div>
