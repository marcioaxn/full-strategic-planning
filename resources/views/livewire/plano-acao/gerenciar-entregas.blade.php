<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('planos.index') }}" class="text-decoration-none">Planos de Ação</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Gerenciar Entregas</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Entregas do Plano</h2>
                <p class="text-muted small mb-0">
                    <span class="badge bg-light text-dark border me-2">{{ $plano->tipoExecucao->dsc_tipo_execucao }}</span>
                    {{ $plano->dsc_plano_de_acao }}
                </p>
            </div>
            @can('update', $plano)
                <button wire:click="create" class="btn btn-primary gradient-theme-btn">
                    <i class="bi bi-plus-lg me-2"></i>Nova Entrega
                </button>
            @endcan
        </div>
    </x-slot>

    @if (session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Barra de Progresso do Plano -->
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0">Progresso Consolidado do Plano</h6>
                <span class="fw-bold text-primary fs-5">{{ number_format($progresso, 1) }}%</span>
            </div>
            <div class="progress rounded-pill" style="height: 12px;">
                <div class="progress-bar gradient-theme progress-bar-striped progress-bar-animated" 
                     role="progressbar" 
                     style="width: {{ $progresso }}%" 
                     aria-valuenow="{{ $progresso }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100"></div>
            </div>
            <small class="text-muted mt-2 d-block text-center italic">
                O progresso é calculado com base no número de entregas marcadas como <strong>Concluído</strong>.
            </small>
        </div>
    </div>

    <!-- Lista de Entregas -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 60px;">Nível</th>
                        <th>Descrição da Entrega</th>
                        <th>Período de Medição</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entregas as $entrega)
                        <tr class="{{ $entrega->isConcluida() ? 'bg-success bg-opacity-5' : '' }}">
                            <td class="ps-4">
                                <span class="badge bg-light text-primary border rounded-circle" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                    {{ $entrega->num_nivel_hierarquico_apresentacao }}
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="fw-semibold {{ $entrega->isConcluida() ? 'text-decoration-line-through text-muted' : 'text-dark' }}">
                                    {{ $entrega->dsc_entrega }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ $entrega->dsc_periodo_medicao ?: 'Não informado' }}
                                </small>
                            </td>
                            <td>
                                @php
                                    $statusClass = match($entrega->bln_status) {
                                        'Concluído' => 'success',
                                        'Em Andamento' => 'primary',
                                        'Suspenso' => 'warning',
                                        'Cancelado' => 'secondary',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }} border border-{{ $statusClass }}-subtle rounded-pill">
                                    {{ $entrega->bln_status }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @can('update', $plano)
                                    <button wire:click="edit('{{ $entrega->cod_entrega }}')" class="btn btn-sm btn-outline-secondary border-0">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button wire:click="delete('{{ $entrega->cod_entrega }}')" 
                                            class="btn btn-sm btn-outline-danger border-0"
                                            onclick="return confirm('Excluir entrega?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="mb-3 text-muted">
                                    <i class="bi bi-box fs-1 opacity-25"></i>
                                </div>
                                <h5 class="text-muted">Nenhuma entrega cadastrada.</h5>
                                <p class="text-muted small">Defina os marcos e entregas para este plano de ação.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Criar/Editar -->
    <div class="modal fade @if($showModal) show @endif" tabindex="-1" style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header gradient-theme text-white border-0">
                    <h5 class="modal-title fw-bold">
                        {{ $entregaId ? 'Editar Entrega' : 'Nova Entrega' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Descrição da Entrega</label>
                            <textarea wire:model="dsc_entrega" class="form-control @error('dsc_entrega') is-invalid @enderror" rows="3" placeholder="Ex: Relatório final de diagnóstico..."></textarea>
                            @error('dsc_entrega') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Período de Medição / Data</label>
                            <input type="text" wire:model="dsc_periodo_medicao" class="form-control @error('dsc_periodo_medicao') is-invalid @enderror" placeholder="Ex: Março/2025 ou 30/03/2025">
                            @error('dsc_periodo_medicao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label text-muted small text-uppercase fw-bold">Status</label>
                                <select wire:model="bln_status" class="form-select @error('bln_status') is-invalid @enderror">
                                    @foreach($statusOptions as $st)
                                        <option value="{{ $st }}">{{ $st }}</option>
                                    @endforeach
                                </select>
                                @error('bln_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Ordem/Nível</label>
                                <input type="number" wire:model="num_nivel_hierarquico_apresentacao" class="form-control @error('num_nivel_hierarquico_apresentacao') is-invalid @enderror">
                                @error('num_nivel_hierarquico_apresentacao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" wire:click="$set('showModal', false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-4">Salvar Entrega</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>