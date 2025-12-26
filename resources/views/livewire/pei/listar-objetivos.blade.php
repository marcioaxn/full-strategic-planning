<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pei.index') }}" class="text-decoration-none">PEI</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Objetivos Estratégicos</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Objetivos Estratégicos</h2>
            </div>
            <div class="d-flex gap-2">
                @if($peiAtivo)
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary rounded-pill px-3 shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-download me-1"></i> Exportar
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                            <li><a class="dropdown-item" href="{{ route('relatorios.objetivos.pdf') }}"><i class="bi bi-file-earmark-pdf text-danger me-2"></i> PDF</a></li>
                            <li><a class="dropdown-item" href="{{ route('relatorios.objetivos.excel') }}"><i class="bi bi-file-earmark-excel text-success me-2"></i> Excel</a></li>
                        </ul>
                    </div>
                @endif
                @if($peiAtivo && $perspectivas->isNotEmpty())
                    <button wire:click="create" class="btn btn-primary gradient-theme-btn">
                        <i class="bi bi-plus-lg me-2"></i>Novo Objetivo
                    </button>
                @endif
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
                <p class="mb-0">É necessário um PEI vigente para gerenciar objetivos estratégicos.</p>
            </div>
        </div>
    @elseif($perspectivas->isEmpty())
        <div class="alert alert-warning shadow-sm border-0 d-flex align-items-center p-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-2 me-4"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Nenhuma Perspectiva Cadastrada</h5>
                <p class="mb-0">Cadastre primeiro as perspectivas do BSC em <a href="{{ route('pei.perspectivas') }}" class="alert-link">Gerenciar Perspectivas</a>.</p>
            </div>
        </div>
    @else
        <div class="accordion" id="accordionPerspectivas">
            @foreach($perspectivas as $p)
                <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                    <h2 class="accordion-header">
                        <button class="accordion-button @if(!$loop->first) collapsed @endif d-flex align-items-center py-3 bg-white" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse-{{ $p->cod_perspectiva }}">
                            <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-layers-half"></i>
                            </div>
                            <div class="flex-grow-1">
                                <span class="fw-bold text-dark">{{ $p->dsc_perspectiva }}</span>
                                <span class="badge bg-light text-muted border ms-2">{{ $p->objetivos->count() }} Objetivos</span>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse-{{ $p->cod_perspectiva }}" 
                         class="accordion-collapse collapse @if($loop->first) show @endif" 
                         data-bs-parent="#accordionPerspectivas">
                        <div class="accordion-body p-4 bg-light bg-opacity-50">
                            <div class="row g-3">
                                @forelse($p->objetivos as $obj)
                                    <div class="col-12">
                                        <div class="card border-0 shadow-sm hover-shadow transition-all">
                                            <div class="card-body p-3 d-flex align-items-center">
                                                <div class="num-order me-3 bg-white border rounded-circle d-flex align-items-center justify-content-center fw-bold text-primary shadow-sm" style="width: 32px; height: 32px; flex-shrink: 0;">
                                                    {{ $obj->num_nivel_hierarquico_apresentacao }}
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-bold">{{ $obj->nom_objetivo_estrategico }}</h6>
                                                    <p class="text-muted small mb-0 text-truncate" style="max-width: 600px;">
                                                        {{ $obj->dsc_objetivo_estrategico ?: 'Sem descrição.' }}
                                                    </p>
                                                </div>
                                                <div class="ms-auto d-flex gap-2">
                                                    <a href="{{ route('objetivos.futuro', $obj->cod_objetivo_estrategico) }}" 
                                                       class="btn btn-sm btn-outline-info border-0"
                                                       title="Futuro Almejado">
                                                        <i class="bi bi-rocket-takeoff"></i>
                                                    </a>
                                                    <button wire:click="edit('{{ $obj->cod_objetivo_estrategico }}')" class="btn btn-sm btn-outline-secondary border-0">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button wire:click="delete('{{ $obj->cod_objetivo_estrategico }}')" 
                                                            class="btn btn-sm btn-outline-danger border-0"
                                                            onclick="return confirm('Tem certeza?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-4 text-muted small italic">
                                        Nenhum objetivo nesta perspectiva. 
                                        <button wire:click="create('{{ $p->cod_perspectiva }}')" class="btn btn-link btn-sm p-0">Cadastrar agora</button>
                                    </div>
                                @endforelse
                                <div class="col-12 text-center mt-2">
                                    <button wire:click="create('{{ $p->cod_perspectiva }}')" class="btn btn-sm btn-outline-primary rounded-pill px-3 border-dashed">
                                        <i class="bi bi-plus-lg me-1"></i>Adicionar Objetivo em {{ $p->dsc_perspectiva }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Modal -->
        <div class="modal fade @if($showModal) show @endif" 
             tabindex="-1" 
             style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header gradient-theme text-white border-0">
                        <h5 class="modal-title fw-bold">
                            {{ $objetivoId ? 'Editar Objetivo Estratégico' : 'Novo Objetivo Estratégico' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Título do Objetivo</label>
                                    <input type="text" wire:model="nom_objetivo_estrategico" class="form-control @error('nom_objetivo_estrategico') is-invalid @enderror" placeholder="Ex: Aumentar a eficiência operacional">
                                    @error('nom_objetivo_estrategico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Perspectiva BSC</label>
                                    <select wire:model="cod_perspectiva" class="form-select @error('cod_perspectiva') is-invalid @enderror">
                                        <option value="">Selecione...</option>
                                        @foreach($perspectivas as $p)
                                            <option value="{{ $p->cod_perspectiva }}">{{ $p->dsc_perspectiva }}</option>
                                        @endforeach
                                    </select>
                                    @error('cod_perspectiva') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Nível/Ordem</label>
                                    <input type="number" wire:model="num_nivel_hierarquico_apresentacao" class="form-control @error('num_nivel_hierarquico_apresentacao') is-invalid @enderror">
                                    @error('num_nivel_hierarquico_apresentacao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Descrição Detalhada</label>
                                    <textarea wire:model="dsc_objetivo_estrategico" class="form-control @error('dsc_objetivo_estrategico') is-invalid @enderror" rows="4" placeholder="Descreva o que se pretende alcançar com este objetivo..."></textarea>
                                    @error('dsc_objetivo_estrategico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="button" class="btn btn-light px-4" wire:click="$set('showModal', false)">Cancelar</button>
                            <button type="submit" class="btn btn-primary gradient-theme-btn px-4">Salvar Objetivo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <style>
        .hover-shadow:hover {
            box-shadow: 0 .25rem .5rem rgba(0,0,0,.1)!important;
            transform: translateX(5px);
        }
        .transition-all {
            transition: all 0.2s ease;
        }
        .border-dashed {
            border-style: dashed !important;
        }
        .accordion-button:not(.collapsed) {
            color: var(--bs-primary);
            box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
        }
    </style>
</div>
