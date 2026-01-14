<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pei.index') }}" class="text-decoration-none">PEI</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Valores</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Valores Organizacionais</h2>
            </div>
            @if($organizacaoId)
                <button wire:click="create" class="btn btn-primary gradient-theme-btn">
                    <i class="bi bi-plus-lg me-2"></i>Novo Valor
                </button>
            @endif
        </div>
    </x-slot>

    @if (session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$organizacaoId)
        <div class="alert alert-warning shadow-sm border-0 d-flex align-items-center p-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-2 me-4"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Nenhuma Organização Selecionada</h5>
                <p class="mb-0">Por favor, selecione uma organização no menu superior para gerenciar seus valores organizacionais.</p>
            </div>
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            @forelse($valores as $valor)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="icon-circle bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-gem fs-4"></i>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                        <li><button class="dropdown-item" wire:click="edit('{{ $valor->cod_valor }}')"><i class="bi bi-pencil me-2"></i>Editar</button></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><button class="dropdown-item text-danger" wire:click="delete('{{ $valor->cod_valor }}')" onclick="return confirm('Tem certeza?')"><i class="bi bi-trash me-2"></i>Excluir</button></li>
                                    </ul>
                                </div>
                            </div>
                            <h5 class="fw-bold mb-2">{{ $valor->nom_valor }}</h5>
                            <p class="text-muted mb-0 small" style="line-height: 1.5;">
                                {{ $valor->dsc_valor ?: 'Sem descrição informada.' }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 w-100 text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-clipboard-x fs-1 text-muted opacity-25"></i>
                    </div>
                    <h5 class="text-muted">Nenhum valor cadastrado.</h5>
                    <p class="text-muted small">Clique em "Novo Valor" para começar.</p>
                </div>
            @endforelse
        </div>

        <!-- Modal -->
        <div class="modal fade @if($showModal) show @endif" 
             id="valorModal" 
             tabindex="-1" 
             style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header gradient-theme text-white border-0">
                        <h5 class="modal-title fw-bold">
                            {{ $valorId ? 'Editar Valor' : 'Novo Valor' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label for="nom_valor" class="form-label text-muted small text-uppercase fw-bold">Nome do Valor</label>
                                <input type="text" id="nom_valor" wire:model="nom_valor" class="form-control @error('nom_valor') is-invalid @enderror" placeholder="Ex: Transparência">
                                @error('nom_valor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="dsc_valor" class="form-label text-muted small text-uppercase fw-bold">Descrição</label>
                                <textarea id="dsc_valor" wire:model="dsc_valor" class="form-control @error('dsc_valor') is-invalid @enderror" rows="4" placeholder="Descreva o significado deste valor..."></textarea>
                                @error('dsc_valor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="button" class="btn btn-light px-4" wire:click="$set('showModal', false)">Cancelar</button>
                            <button type="submit" class="btn btn-primary gradient-theme-btn px-4">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <style>
        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }
        .transition-all {
            transition: all 0.3s ease;
        }
    </style>
</div>
