<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Planos de Ação</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Planos de Ação</h2>
            </div>
            @if($organizacaoId)
                <button wire:click="create" class="btn btn-primary gradient-theme-btn">
                    <i class="bi bi-plus-lg me-2"></i>Novo Plano
                </button>
            @endif
        </div>
    </x-slot>

    @if (session()->has('message'))
        <div class="alert alert-{{ session('style') }} alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$organizacaoId && !$filtroObjetivo)
        <div class="alert alert-warning shadow-sm border-0 d-flex align-items-center p-4" role="alert">
            <i class="bi bi-building-exclamation fs-2 me-4"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Selecione uma Organização</h5>
                <p class="mb-0">Selecione uma organização no menu superior para gerenciar os planos de ação.</p>
            </div>
        </div>
    @else
        @if($filtroObjetivo)
            @php
                $objetivoFiltrado = \App\Models\PEI\ObjetivoEstrategico::find($filtroObjetivo);
            @endphp
            <div class="alert alert-info shadow-sm border-0 d-flex align-items-center justify-content-between p-3 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-funnel-fill fs-4 me-3"></i>
                    <div>
                        <strong>Filtro ativo:</strong> Planos de Ação do objetivo
                        <span class="badge bg-primary ms-2">{{ $objetivoFiltrado?->nom_objetivo_estrategico ?? 'Objetivo' }}</span>
                    </div>
                </div>
                <a href="{{ route('planos.index') }}" class="btn btn-outline-info btn-sm" wire:navigate>
                    <i class="bi bi-x-circle me-1"></i> Limpar filtro
                </a>
            </div>
        @endif
        <!-- Filtros -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 bg-light rounded-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar planos...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="filtroStatus" class="form-select">
                            <option value="">Todos Status</option>
                            @foreach($statusOptions as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="filtroTipo" class="form-select">
                            <option value="">Todos Tipos</option>
                            @foreach($tiposExecucao as $tipo)
                                <option value="{{ $tipo->cod_tipo_execucao }}">{{ $tipo->dsc_tipo_execucao }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="filtroAno" class="form-select">
                            <option value="">Ano</option>
                            @for($i = now()->year - 2; $i <= now()->year + 5; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <div wire:loading class="spinner-border text-primary spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Planos -->
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Descrição do Plano</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Período</th>
                            <th>Orçamento</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($planos as $plano)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark mb-1">{{ Str::limit($plano->dsc_plano_de_acao, 60) }}</div>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-bullseye me-1"></i>
                                        {{ Str::limit($plano->objetivoEstrategico->nom_objetivo_estrategico ?? 'Sem Objetivo', 50) }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $plano->tipoExecucao->dsc_tipo_execucao ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($plano->bln_status) {
                                            'Concluído' => 'success',
                                            'Em Andamento' => 'primary',
                                            'Atrasado' => 'danger',
                                            'Cancelado' => 'secondary',
                                            'Suspenso' => 'warning',
                                            default => 'secondary'
                                        };
                                        // Verificar atraso via método do model
                                        if ($plano->isAtrasado()) {
                                            $statusLabel = 'Atrasado';
                                            $statusClass = 'danger';
                                        } else {
                                            $statusLabel = $plano->bln_status;
                                        }
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }} border border-{{ $statusClass }}-subtle rounded-pill">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td>
                                    <small class="d-block text-nowrap">
                                        {{ $plano->dte_inicio?->format('d/m/Y') }} a
                                    </small>
                                    <small class="d-block text-nowrap fw-bold {{ $plano->isAtrasado() ? 'text-danger' : 'text-dark' }}">
                                        {{ $plano->dte_fim?->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td>
                                    <small class="fw-mono text-muted">
                                        R$ {{ number_format($plano->vlr_orcamento_previsto, 2, ',', '.') }}
                                    </small>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <li>
                                                <h6 class="dropdown-header small text-uppercase">Gestão</h6>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('planos.detalhes', $plano->cod_plano_de_acao) }}">
                                                    <i class="bi bi-eye me-2 text-primary"></i> Detalhes
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('planos.entregas', $plano->cod_plano_de_acao) }}">
                                                    <i class="bi bi-list-check me-2 text-info"></i> Entregas
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('planos.responsaveis', $plano->cod_plano_de_acao) }}">
                                                    <i class="bi bi-people me-2 text-warning"></i> Responsáveis
                                                </a>
                                            </li>
                                            <li>
                                                <button class="dropdown-item" wire:click="edit('{{ $plano->cod_plano_de_acao }}')">
                                                    <i class="bi bi-pencil me-2 text-secondary"></i> Editar
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger" wire:click="confirmDelete('{{ $plano->cod_plano_de_acao }}')">
                                                    <i class="bi bi-trash me-2"></i> Excluir
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="bi bi-clipboard-x fs-1 text-muted opacity-25"></i>
                                    </div>
                                    <h5 class="text-muted">Nenhum plano encontrado.</h5>
                                    <p class="text-muted small">Tente ajustar os filtros ou crie um novo plano.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3">
                {{ $planos->links() }}
            </div>
        </div>
    @endif

    <!-- Modal Criar/Editar -->
    <div class="modal fade @if($showModal) show @endif" tabindex="-1" style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header gradient-theme text-white border-0">
                    <h5 class="modal-title fw-bold">
                        {{ $planoId ? 'Editar Plano de Ação' : 'Novo Plano de Ação' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <!-- Linha 1: Descrição -->
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Descrição do Plano</label>
                            <textarea wire:model="dsc_plano_de_acao" class="form-control @error('dsc_plano_de_acao') is-invalid @enderror" rows="2" placeholder="Descreva o plano de ação..."></textarea>
                            @error('dsc_plano_de_acao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Linha 2: Objetivo e Tipo -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label text-muted small text-uppercase fw-bold">Objetivo Estratégico</label>
                                <select wire:model="cod_objetivo_estrategico" class="form-select @error('cod_objetivo_estrategico') is-invalid @enderror">
                                    <option value="">Selecione...</option>
                                    @foreach($objetivos as $obj)
                                        <option value="{{ $obj->cod_objetivo_estrategico }}">
                                            {{ $obj->perspectiva->dsc_perspectiva }} > {{ Str::limit($obj->nom_objetivo_estrategico, 80) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cod_objetivo_estrategico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Tipo de Execução</label>
                                <select wire:model="cod_tipo_execucao" class="form-select @error('cod_tipo_execucao') is-invalid @enderror">
                                    <option value="">Selecione...</option>
                                    @foreach($tiposExecucao as $tipo)
                                        <option value="{{ $tipo->cod_tipo_execucao }}">{{ $tipo->dsc_tipo_execucao }}</option>
                                    @endforeach
                                </select>
                                @error('cod_tipo_execucao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Linha 3: Datas e Status -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Data Início</label>
                                <input type="date" wire:model="dte_inicio" class="form-control @error('dte_inicio') is-invalid @enderror">
                                @error('dte_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Data Fim</label>
                                <input type="date" wire:model="dte_fim" class="form-control @error('dte_fim') is-invalid @enderror">
                                @error('dte_fim') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Status</label>
                                <select wire:model="bln_status" class="form-select @error('bln_status') is-invalid @enderror">
                                    @foreach($statusOptions as $st)
                                        <option value="{{ $st }}">{{ $st }}</option>
                                    @endforeach
                                </select>
                                @error('bln_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Linha 4: Orçamento e Códigos -->
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Orçamento Previsto (R$)</label>
                                <input type="number" step="0.01" wire:model="vlr_orcamento_previsto" class="form-control @error('vlr_orcamento_previsto') is-invalid @enderror">
                                @error('vlr_orcamento_previsto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Cód. PPA</label>
                                <input type="text" wire:model="cod_ppa" class="form-control @error('cod_ppa') is-invalid @enderror" placeholder="Opcional">
                                @error('cod_ppa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Cód. LOA</label>
                                <input type="text" wire:model="cod_loa" class="form-control @error('cod_loa') is-invalid @enderror" placeholder="Opcional">
                                @error('cod_loa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" wire:click="$set('showModal', false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-4">Salvar Plano</button>
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
                    <div class="mb-3">
                        <i class="bi bi-exclamation-circle fs-1 text-danger"></i>
                    </div>
                    <p class="mb-0 fs-5">Tem certeza que deseja excluir este plano?</p>
                    <p class="text-muted small mt-2">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer border-0 p-4 justify-content-center">
                    <button type="button" class="btn btn-light px-4" wire:click="$set('showDeleteModal', false)">Cancelar</button>
                    <button type="button" class="btn btn-danger px-4" wire:click="delete">Sim, Excluir</button>
                </div>
            </div>
        </div>
    </div>
</div>