<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none" wire:navigate>Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Graus de Satisfacao</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Graus de Satisfacao</h2>
            </div>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <!-- Header com Botão Novo Grau -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="text-primary fw-bold mb-0">
                    <i class="bi bi-speedometer2 me-2"></i>Gerenciar Graus de Satisfação
                </h5>
            </div>
            <button class="btn btn-primary" wire:click="openModal">
                <i class="bi bi-plus-circle me-1"></i> Novo Grau
            </button>
        </div>
        <!-- Mensagens Flash -->
        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Card Principal -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="bi bi-palette me-2"></i>Configuracao de Graus de Satisfacao
                        </h5>
                        <small class="text-muted">Defina os intervalos percentuais e cores para classificar o desempenho</small>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text"
                                   class="form-control border-start-0"
                                   placeholder="Buscar por descricao ou cor..."
                                   wire:model.live.debounce.300ms="search">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <!-- Preview de Cores -->
                @if($graus->count() > 0)
                    <div class="bg-light p-3 border-bottom">
                        <small class="text-muted fw-bold text-uppercase mb-2 d-block">Preview da Legenda:</small>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($graus as $grau)
                                <div class="d-flex align-items-center">
                                    <span class="rounded-circle me-2" style="width: 16px; height: 16px; background-color: {{ $grau->cor }};"></span>
                                    <small>{{ $grau->dsc_grau_satisfcao }} ({{ number_format($grau->vlr_minimo, 2, ',', '.') }}-{{ number_format($grau->vlr_maximo, 2, ',', '.') }}%)</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th class="px-4" style="width: 1%;">Cor</th>
                                <th>Descricao</th>
                                <th class="text-center">Código da Cor</th>
                                <th class="text-center">Min (%)</th>
                                <th class="text-center">Max (%)</th>
                                <th class="text-center" style="width: 1%;">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($graus as $grau)
                                <tr>
                                    <td class="px-4">
                                        <span class="d-inline-block rounded-circle border shadow-sm"
                                              style="width: 32px; height: 32px; background-color: {{ $grau->cor }};"
                                              data-bs-toggle="tooltip"
                                              title="{{ $grau->cor }}"></span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $grau->dsc_grau_satisfcao }}</span>
                                    </td>
                                    <td class="text-center">
                                        <code class="bg-light px-2 py-1 rounded">{{ $grau->cor }}</code>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-secondary">{{ number_format($grau->vlr_minimo, 2, ',', '.') }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary">{{ number_format($grau->vlr_maximo, 2, ',', '.') }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" wire:click="edit('{{ $grau->cod_grau_satisfcao }}')" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" wire:click="confirmDelete('{{ $grau->cod_grau_satisfcao }}')" title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-palette fs-1 d-block mb-3 opacity-50"></i>
                                            <p class="mb-2">Nenhum grau de satisfacao cadastrado</p>
                                            <button class="btn btn-primary btn-sm" wire:click="openModal">
                                                <i class="bi bi-plus-circle me-1"></i> Cadastrar Primeiro Grau
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($graus->hasPages())
                <div class="card-footer bg-white">
                    {{ $graus->links() }}
                </div>
            @endif
        </div>

        <!-- Dica de Configuracao -->
        <div class="alert alert-info mt-4 border-0 shadow-sm">
            <div class="d-flex align-items-start">
                <i class="bi bi-info-circle fs-4 me-3"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Dica de Configuracao</h6>
                    <p class="mb-0 small">
                        Configure os graus de satisfacao para que os intervalos cubram de 0% a 100% (ou mais) sem sobreposicao.
                        As cores podem ser nomes em ingles (ex: <code>red</code>, <code>green</code>, <code>blue</code>) ou codigos hexadecimais (ex: <code>#28a745</code>).
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Criacao/Edicao -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-{{ $isEditing ? 'pencil' : 'plus-circle' }} me-2"></i>
                            {{ $isEditing ? 'Editar' : 'Novo' }} Grau de Satisfacao
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <!-- Descricao -->
                            <div class="mb-3">
                                <label for="dsc_grau_satisfcao" class="form-label fw-semibold">
                                    Descricao <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('dsc_grau_satisfcao') is-invalid @enderror"
                                       id="dsc_grau_satisfcao"
                                       wire:model="dsc_grau_satisfcao"
                                       placeholder="Ex: Excelente, Bom, Regular, Critico...">
                                @error('dsc_grau_satisfcao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cor -->
                            <div class="mb-3">
                                <label for="cor" class="form-label fw-semibold">
                                    Cor <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="color"
                                           class="form-control form-control-color @error('cor') is-invalid @enderror"
                                           id="cor"
                                           wire:model.live="cor"
                                           value="{{ $cor ?: '#FF0000' }}"
                                           title="Selecione uma cor">
                                    <input type="text"
                                           class="form-control @error('cor') is-invalid @enderror"
                                           wire:model.live="cor"
                                           placeholder="#FF0000"
                                           style="max-width: 120px;">
                                    @error('cor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Clique no seletor para escolher uma cor ou digite o codigo hexadecimal</small>
                            </div>

                            <div class="row">
                                <!-- Valor Minimo -->
                                <div class="col-md-6 mb-3">
                                    <label for="vlr_minimo" class="form-label fw-semibold">
                                        Percentual Minimo <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               max="999.99"
                                               class="form-control @error('vlr_minimo') is-invalid @enderror"
                                               id="vlr_minimo"
                                               wire:model="vlr_minimo"
                                               placeholder="0.00">
                                        <span class="input-group-text">%</span>
                                        @error('vlr_minimo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Valor Maximo -->
                                <div class="col-md-6 mb-3">
                                    <label for="vlr_maximo" class="form-label fw-semibold">
                                        Percentual Maximo <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               max="999.99"
                                               class="form-control @error('vlr_maximo') is-invalid @enderror"
                                               id="vlr_maximo"
                                               wire:model="vlr_maximo"
                                               placeholder="100.00">
                                        <span class="input-group-text">%</span>
                                        @error('vlr_maximo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">
                                <i class="bi bi-x-circle me-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> {{ $isEditing ? 'Atualizar' : 'Salvar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Confirmacao de Exclusao -->
    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Exclusao
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cancelDelete"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <i class="bi bi-trash fs-1 text-danger mb-3 d-block"></i>
                        <p class="mb-0">Deseja realmente excluir este grau de satisfacao?</p>
                        <small class="text-muted">Esta acao nao pode ser desfeita.</small>
                    </div>
                    <div class="modal-footer justify-content-center bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="delete">
                            <i class="bi bi-trash me-1"></i> Sim, Excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
