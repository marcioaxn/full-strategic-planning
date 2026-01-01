<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">PEI</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Identidade Estratégica</h2>
            </div>
            @if($organizacaoId && !$isEditing)
                <div class="d-flex gap-2">
                    <a href="{{ route('relatorios.identidade', $organizacaoId) }}" class="btn btn-outline-danger shadow-sm rounded-pill px-3">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Exportar PDF
                    </a>
                    <button wire:click="habilitarEdicao" class="btn btn-primary gradient-theme-btn">
                        <i class="bi bi-pencil-square me-2"></i>Editar Identidade
                    </button>
                </div>
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
                <p class="mb-0">Por favor, selecione uma organização no menu superior para visualizar ou gerenciar sua identidade estratégica.</p>
            </div>
        </div>
    @else
        <div class="row g-4">
            <!-- Card Missão -->
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-white py-3 border-0 d-flex align-items-center">
                        <div class="icon-shape gradient-theme-icon rounded-3 p-2 me-3 shadow-sm">
                            <i class="bi bi-bullseye fs-4"></i>
                        </div>
                        <h4 class="mb-0 fw-bold">Missão</h4>
                    </div>
                    <div class="card-body p-4 pt-2">
                        @if($isEditing)
                            <div class="mb-3">
                                <label for="missao" class="form-label text-muted small text-uppercase fw-bold">Descrição da Missão</label>
                                <textarea id="missao" 
                                          wire:model="missao" 
                                          class="form-control @error('missao') is-invalid @enderror" 
                                          rows="8" 
                                          placeholder="A razão de ser da organização..."></textarea>
                                @error('missao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @else
                            <div class="mission-text bg-light p-4 rounded-4 border-start border-primary border-4 min-vh-25">
                                @if($missao)
                                    <p class="fs-5 text-dark mb-0 italic" style="font-style: italic; line-height: 1.6;">
                                        "{{ $missao }}"
                                    </p>
                                @else
                                    <p class="text-muted italic mb-0">Missão ainda não definida para esta organização.</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card Visão -->
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-white py-3 border-0 d-flex align-items-center">
                        <div class="icon-shape bg-info bg-gradient rounded-3 p-2 me-3 shadow-sm text-white">
                            <i class="bi bi-eye fs-4"></i>
                        </div>
                        <h4 class="mb-0 fw-bold">Visão</h4>
                    </div>
                    <div class="card-body p-4 pt-2">
                        @if($isEditing)
                            <div class="mb-3">
                                <label for="visao" class="form-label text-muted small text-uppercase fw-bold">Descrição da Visão</label>
                                <textarea id="visao" 
                                          wire:model="visao" 
                                          class="form-control @error('visao') is-invalid @enderror" 
                                          rows="8" 
                                          placeholder="Onde a organização quer chegar..."></textarea>
                                @error('visao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @else
                            <div class="vision-text bg-light p-4 rounded-4 border-start border-info border-4 min-vh-25">
                                @if($visao)
                                    <p class="fs-5 text-dark mb-0 italic" style="font-style: italic; line-height: 1.6;">
                                        "{{ $visao }}"
                                    </p>
                                @else
                                    <p class="text-muted italic mb-0">Visão ainda não definida para esta organização.</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card Valores (Placeholder para próxima tarefa) -->
            <div class="col-12 mt-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="icon-shape bg-warning bg-gradient rounded-3 p-2 me-3 shadow-sm text-dark">
                                <i class="bi bi-gem fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold">Valores Organizacionais</h4>
                        </div>
                        <a href="{{ route('pei.valores') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            Gerenciar <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="row row-cols-1 row-cols-md-3 g-3">
                            <div class="col">
                                <div class="p-3 bg-light rounded-3 text-center text-muted border border-dashed">
                                    <small>Os valores serão implementados na próxima etapa.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($isEditing)
                <div class="col-12 text-center mt-4">
                    <button wire:click="salvar" class="btn btn-success px-5 py-2 fw-bold shadow-sm rounded-pill me-2">
                        <i class="bi bi-save me-2"></i>Salvar Identidade
                    </button>
                    <button wire:click="cancelar" class="btn btn-outline-secondary px-5 py-2 fw-bold shadow-sm rounded-pill">
                        Cancelar
                    </button>
                </div>
            @endif
        </div>
        
        <div class="mt-4 text-center text-muted small">
            <i class="bi bi-building me-1"></i> Organização: <strong>{{ $organizacaoNome }}</strong>
        </div>
    @endif

    <style>
        .icon-shape {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .min-vh-25 {
            min-height: 200px;
        }
        .italic {
            font-style: italic;
        }
    </style>
</div>
