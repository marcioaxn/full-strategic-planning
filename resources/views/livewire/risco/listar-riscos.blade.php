<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Gestão de Riscos</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Riscos Estratégicos</h2>
            </div>
            <div class="d-flex gap-2">
                @if($organizacaoId)
                    <a href="{{ route('riscos.matriz') }}" class="btn btn-outline-primary shadow-sm rounded-pill px-3">
                        <i class="bi bi-grid-3x3-gap me-1"></i> Ver Matriz
                    </a>
                    <button wire:click="create" class="btn btn-primary gradient-theme-btn shadow-sm rounded-pill">
                        <i class="bi bi-plus-lg me-2"></i>Identificar Risco
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    @if (session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(!$organizacaoId)
        <div class="alert alert-warning shadow-sm border-0 d-flex align-items-center p-4" role="alert">
            <i class="bi bi-building-exclamation fs-2 me-4"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Selecione uma Organização</h5>
                <p class="mb-0">A gestão de riscos é contextualizada por unidade organizacional.</p>
            </div>
        </div>
    @else
        <!-- Filtros -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 bg-light rounded-3">
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" wire:model.live.debounce="search" class="form-control border-start-0 ps-0" placeholder="Buscar por título do risco...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="filtroCategoria" class="form-select">
                            <option value="">Todas as Categorias</option>
                            @foreach($categoriasOptions as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="filtroNivel" class="form-select">
                            <option value="">Todos Níveis</option>
                            <option value="Critico">Críticos (≥ 16)</option>
                            <option value="Baixo">Baixos (< 5)</option>
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <div wire:loading class="spinner-border text-primary spinner-border-sm" role="status"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela -->
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">ID / Título do Risco</th>
                            <th>Categoria</th>
                            <th class="text-center">Matriz (P x I)</th>
                            <th>Nível / Exposição</th>
                            <th>Responsável</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riscos as $risco)
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="text-muted small fw-mono">R-{{ str_pad($risco->num_codigo_risco, 3, '0', STR_PAD_LEFT) }}</span>
                                    <span class="fw-bold text-dark d-block mb-1">{{ $risco->dsc_titulo }}</span>
                                    <small class="text-muted d-block">{{ $risco->dsc_status }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-3 rounded-pill">{{ $risco->dsc_categoria }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <small class="text-muted" style="font-size: 0.65rem;">{{ $risco->num_probabilidade }} x {{ $risco->num_impacto }}</small>
                                        <span class="fw-bold fs-5">{{ $risco->num_nivel_risco }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $risco->getNivelRiscoBadgeClass() }} px-3 py-2 rounded-pill shadow-sm" style="min-width: 80px;">
                                        {{ $risco->getNivelRiscoLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-2 bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 24px; height: 24px; font-size: 0.7rem;">
                                            {{ substr($risco->responsavel->name ?? '?', 0, 1) }}
                                        </div>
                                        <small class="text-dark">{{ Str::limit($risco->responsavel->name ?? 'Não atribuído', 20) }}</small>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <li><h6 class="dropdown-header small text-uppercase">Gestão</h6></li>
                                            <li><a class="dropdown-item" href="{{ route('riscos.mitigacao', $risco->cod_risco) }}"><i class="bi bi-shield-check me-2 text-success"></i> Planos de Mitigação</a></li>
                                            <li><a class="dropdown-item" href="{{ route('riscos.ocorrencias', $risco->cod_risco) }}"><i class="bi bi-exclamation-octagon me-2 text-danger"></i> Registrar Ocorrência</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><button class="dropdown-item" wire:click="edit('{{ $risco->cod_risco }}')"><i class="bi bi-pencil me-2"></i> Editar</button></li>
                                            <li><button class="dropdown-item text-danger" wire:click="confirmDelete('{{ $risco->cod_risco }}')"><i class="bi bi-trash me-2"></i> Excluir</button></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-shield-exclamation fs-1 opacity-25 mb-3 d-block"></i>
                                    Nenhum risco identificado para esta organização.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3">
                {{ $riscos->links() }}
            </div>
        </div>
    @endif

    <!-- Modal Identificar/Editar Risco -->
    <div class="modal fade @if($showModal) show @endif" tabindex="-1" style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header gradient-theme text-white border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-shield-plus me-2"></i> {{ $riscoId ? 'Configurar Risco Estratégico' : 'Identificar Novo Risco' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4 bg-light bg-opacity-50">
                        <div class="row g-4">
                            <!-- Bloco 1: Identificação -->
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Identificação do Risco</h6>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Título do Risco</label>
                                            <input type="text" wire:model="form.dsc_titulo" class="form-control @error('form.dsc_titulo') is-invalid @enderror" placeholder="Ex: Perda de pessoal qualificado">
                                            @error('form.dsc_titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Descrição Detalhada</label>
                                            <textarea wire:model="form.txt_descricao" class="form-control" rows="3" placeholder="Descreva o evento de risco..."></textarea>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Categoria</label>
                                                <select wire:model="form.dsc_categoria" class="form-select">
                                                    @foreach($categoriasOptions as $cat)
                                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Status Atual</label>
                                                <select wire:model="form.dsc_status" class="form-select">
                                                    @foreach($statusOptions as $st)
                                                        <option value="{{ $st }}">{{ $st }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Causas e Consequências</h6>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Causas (O que gera este risco?)</label>
                                            <textarea wire:model="form.txt_causas" class="form-control" rows="2" placeholder="Ex: Salários abaixo do mercado, falta de plano de carreira..."></textarea>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Consequências (O que acontece se o risco materializar?)</label>
                                            <textarea wire:model="form.txt_consequencias" class="form-control" rows="2" placeholder="Ex: Atraso nos projetos estratégicos, redução da qualidade..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bloco 2: Avaliação e Vínculos -->
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Avaliação (Matriz 5x5)</h6>
                                        
                                        <div class="mb-4">
                                            <label class="form-label text-muted small text-uppercase fw-bold d-block">Probabilidade (1 a 5)</label>
                                            <input type="range" class="form-range" min="1" max="5" step="1" wire:model.live="form.num_probabilidade">
                                            <div class="d-flex justify-content-between px-1">
                                                @foreach(['1','2','3','4','5'] as $v) <small class="text-muted" style="font-size: 0.6rem;">{{$v}}</small> @endforeach
                                            </div>
                                            <div class="text-center fw-bold text-primary">{{ $form['num_probabilidade'] }}</div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label text-muted small text-uppercase fw-bold d-block">Impacto (1 a 5)</label>
                                            <input type="range" class="form-range" min="1" max="5" step="1" wire:model.live="form.num_impacto">
                                            <div class="d-flex justify-content-between px-1">
                                                @foreach(['1','2','3','4','5'] as $v) <small class="text-muted" style="font-size: 0.6rem;">{{$v}}</small> @endforeach
                                            </div>
                                            <div class="text-center fw-bold text-primary">{{ $form['num_impacto'] }}</div>
                                        </div>

                                        <div class="p-3 bg-light rounded-3 text-center border">
                                            <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.6rem;">Nível de Risco Calculado</small>
                                            <h2 class="fw-bold mb-0 {{ ($form['num_probabilidade'] * $form['num_impacto']) >= 16 ? 'text-danger' : 'text-dark' }}">
                                                {{ $form['num_probabilidade'] * $form['num_impacto'] }}
                                            </h2>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Responsável</h6>
                                        <div class="mb-0">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Monitorado por:</label>
                                            <select wire:model="form.cod_responsavel_monitoramento" class="form-select @error('form.cod_responsavel_monitoramento') is-invalid @enderror">
                                                <option value="">Selecione...</option>
                                                @foreach($usuarios as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('form.cod_responsavel_monitoramento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Vínculo Estratégico</h6>
                                        <div class="mb-0 overflow-auto" style="max-height: 200px;">
                                            @foreach($objetivos as $obj)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="{{ $obj->cod_objetivo }}" 
                                                           wire:model="form.objetivos_vinculados" id="obj_{{ $obj->cod_objetivo }}">
                                                    <label class="form-check-label small" for="obj_{{ $obj->cod_objetivo }}">
                                                        {{ Str::limit($obj->nom_objetivo, 50) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0 bg-light bg-opacity-50">
                        <button type="button" class="btn btn-light px-4" wire:click="$set('showModal', false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-4 py-2 fw-bold">
                            <i class="bi bi-save me-2"></i>Salvar Risco
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Exclusão -->
    <div class="modal fade @if($showDeleteModal) show @endif" tabindex="-1" style="@if($showDeleteModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showDeleteModal', false)"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-danger"><i class="bi bi-exclamation-triangle fs-1"></i></div>
                    <p class="fs-5 mb-0">Excluir este risco?</p>
                    <p class="text-muted small">A matriz de riscos será atualizada após esta ação.</p>
                </div>
                <div class="modal-footer border-0 p-4 justify-content-center">
                    <button type="button" class="btn btn-light px-4" wire:click="$set('showDeleteModal', false)">Cancelar</button>
                    <button type="button" class="btn btn-danger px-4" wire:click="delete">Sim, Excluir</button>
                </div>
            </div>
        </div>
    </div>
</div>
