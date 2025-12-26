<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('objetivos.index') }}" class="text-decoration-none">Objetivos</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Futuro Almejado</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Futuro Almejado</h2>
                <p class="text-muted small mb-0">{{ $objetivo->nom_objetivo_estrategico }}</p>
            </div>
            <button wire:click="create" class="btn btn-primary gradient-theme-btn">
                <i class="bi bi-plus-lg me-2"></i>Adicionar Item
            </button>
        </div>
    </x-slot>

    @if (session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm overflow-hidden mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-rocket-takeoff me-2 text-primary"></i>Onde queremos chegar?</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Descrição do Futuro Almejado</th>
                            <th class="text-end pe-4">Ações</th>
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
                                    <button wire:click="edit('{{ $futuro->cod_futuro_almejado }}')" class="btn btn-sm btn-outline-secondary border-0">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button wire:click="delete('{{ $futuro->cod_futuro_almejado }}')" 
                                            class="btn btn-sm btn-outline-danger border-0"
                                            onclick="return confirm('Tem certeza?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center py-5 text-muted">
                                    Nenhum item cadastrado para este objetivo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade @if($showModal) show @endif" 
         tabindex="-1" 
         style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header gradient-theme text-white border-0">
                    <h5 class="modal-title fw-bold">
                        {{ $futuroId ? 'Editar Item' : 'Novo Item' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Descrição</label>
                            <textarea wire:model="dsc_futuro_almejado" class="form-control @error('dsc_futuro_almejado') is-invalid @enderror" rows="5" placeholder="Descreva um aspecto do futuro almejado para este objetivo..."></textarea>
                            @error('dsc_futuro_almejado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" wire:click="$set('showModal', false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-4">Salvar Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
