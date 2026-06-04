<div class="container-fluid py-4">

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">
                        <a href="{{ route('objetivos.index') }}" wire:navigate class="text-decoration-none">Objetivos Estratégicos</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('objetivos.detalhes', $objetivo->cod_objetivo) }}" wire:navigate class="text-decoration-none">{{ Str::limit($objetivo->nom_objetivo, 40) }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Futuro Almejado</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-gray-800 mb-0">
                <i class="bi bi-stars me-2 text-primary"></i>Futuro Almejado
            </h2>
            <p class="text-muted mb-0">{{ $objetivo->nom_objetivo }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('objetivos.detalhes', $objetivo->cod_objetivo) }}" wire:navigate class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar ao Objetivo
            </a>
            <button wire:click="create" class="btn btn-primary gradient-theme">
                <i class="bi bi-plus-lg me-1"></i> Adicionar Item
            </button>
        </div>
    </div>

    {{-- Flash de sucesso --}}
    @if (session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    {{-- Tabela de Futuros Almejados --}}
    <div class="card border-0 shadow-sm overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-rocket-takeoff me-2 text-primary"></i>Onde queremos chegar?
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Descrição do Futuro Almejado</th>
                            <th class="text-end pe-4" style="width: 100px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($futuros as $futuro)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-dot fs-3 text-primary me-2 mt-n2"></i>
                                        <span>{{ $futuro->dsc_futuro_almejado }}</span>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <button wire:click="edit('{{ $futuro->cod_futuro_almejado }}')"
                                            class="btn btn-sm btn-outline-secondary border-0"
                                            title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button wire:click="delete('{{ $futuro->cod_futuro_almejado }}')"
                                            class="btn btn-sm btn-outline-danger border-0"
                                            title="Excluir"
                                            onclick="return confirm('Confirma a exclusão deste item?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center py-5 text-muted">
                                    <i class="bi bi-stars fs-2 d-block mb-2 text-muted opacity-25"></i>
                                    Nenhum futuro almejado cadastrado para este objetivo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal de cadastro/edição --}}
    <div class="modal fade @if($showModal) show @endif"
         tabindex="-1"
         style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header gradient-theme text-white border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-stars me-2"></i>{{ $futuroId ? 'Editar Item' : 'Novo Futuro Almejado' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descrição <span class="text-danger">*</span></label>
                            <textarea wire:model="dsc_futuro_almejado"
                                      class="form-control @error('dsc_futuro_almejado') is-invalid @enderror"
                                      rows="5"
                                      placeholder="Descreva o estado futuro almejado para este objetivo..."></textarea>
                            @error('dsc_futuro_almejado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" wire:click="$set('showModal', false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme px-4">
                            <i class="bi bi-check-lg me-1"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
