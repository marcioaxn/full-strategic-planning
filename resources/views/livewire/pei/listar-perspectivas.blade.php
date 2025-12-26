<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pei.index') }}" class="text-decoration-none">PEI</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Perspectivas BSC</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Perspectivas do BSC</h2>
            </div>
        </div>
    </x-slot>

    @if (session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$peiAtivo)
        <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center p-4" role="alert">
            <i class="bi bi-exclamation-octagon-fill fs-2 me-4"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Nenhum PEI Ativo Encontrado</h5>
                <p class="mb-0">É necessário cadastrar um PEI (Plano Estratégico Institucional) com período vigente para gerenciar perspectivas.</p>
            </div>
        </div>
    @else
        <div class="alert alert-info bg-info bg-opacity-10 border-0 shadow-sm mb-4">
            <i class="bi bi-info-circle-fill me-2"></i>
            As perspectivas definem as camadas do seu <strong>Mapa Estratégico</strong>. A ordem (nível) determina a posição visual de cima para baixo.
        </div>

        <div class="perspective-stack">
            @forelse($perspectivas as $index => $p)
                <div class="perspective-item mb-3">
                    <div class="card border-0 shadow-sm overflow-hidden hover-shadow transition-all">
                        <div class="card-body p-0">
                            <div class="d-flex align-items-stretch">
                                <div class="perspective-number gradient-theme text-white d-flex align-items-center justify-content-center fw-bold" style="width: 60px;">
                                    {{ $p->num_nivel_hierarquico_apresentacao }}
                                </div>
                                <div class="flex-grow-1 p-3 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="mb-0 fw-bold">{{ $p->dsc_perspectiva }}</h5>
                                        <small class="text-muted">Perspectiva Estratégica</small>
                                    </div>
                                    <div class="actions">
                                        <button wire:click="edit('{{ $p->cod_perspectiva }}')" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                            <i class="bi bi-pencil me-1"></i> Editar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if(!$loop->last)
                    <div class="text-center my-1 text-muted opacity-25">
                        <i class="bi bi-arrow-down"></i>
                    </div>
                @endif
            @empty
                <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                    <i class="bi bi-layers fs-1 text-muted opacity-25 mb-3 d-block"></i>
                    <p class="text-muted">Nenhuma perspectiva cadastrada para o PEI atual.</p>
                </div>
            @endforelse
        </div>

        <!-- Modal -->
        <div class="modal fade @if($showModal) show @endif" 
             tabindex="-1" 
             style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header gradient-theme text-white border-0">
                        <h5 class="modal-title fw-bold">Editar Perspectiva</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label text-muted small text-uppercase fw-bold">Descrição da Perspectiva</label>
                                <input type="text" wire:model="dsc_perspectiva" class="form-control @error('dsc_perspectiva') is-invalid @enderror">
                                @error('dsc_perspectiva') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small text-uppercase fw-bold">Nível (Ordem de Apresentação)</label>
                                <input type="number" wire:model="num_nivel_hierarquico_apresentacao" class="form-control @error('num_nivel_hierarquico_apresentacao') is-invalid @enderror">
                                <div class="form-text small">1 = Topo do mapa, números maiores = camadas inferiores.</div>
                                @error('num_nivel_hierarquico_apresentacao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="button" class="btn btn-light px-4" wire:click="$set('showModal', false)">Cancelar</button>
                            <button type="submit" class="btn btn-primary gradient-theme-btn px-4">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <style>
        .hover-shadow:hover {
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }
        .transition-all {
            transition: all 0.3s ease;
        }
        .perspective-number {
            font-size: 1.5rem;
        }
    </style>
</div>
