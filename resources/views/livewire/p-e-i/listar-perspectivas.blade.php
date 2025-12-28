<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-layers me-2"></i>Perspectivas Estratégicas
            </h4>
            <p class="text-muted mb-0">
                Gerenciar as perspectivas do Balanced Scorecard
                @if($peiAtivo)
                    - <strong>{{ $peiAtivo->dsc_nome }}</strong>
                @endif
            </p>
        </div>
        @if($peiAtivo)
            <button type="button" class="btn btn-primary" wire:click="create">
                <i class="bi bi-plus-lg me-1"></i> Nova Perspectiva
            </button>
        @endif
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$peiAtivo)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Nenhum PEI ativo encontrado. Configure um PEI antes de gerenciar as perspectivas.
        </div>
    @else
        <!-- Lista de Perspectivas -->
        <div class="card">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-ul me-2"></i>Perspectivas Cadastradas</span>
                    <span class="badge bg-primary">{{ count($perspectivas) }} perspectiva(s)</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if(count($perspectivas) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;" class="text-center">Ordem</th>
                                    <th>Perspectiva</th>
                                    <th style="width: 150px;" class="text-center">Objetivos</th>
                                    <th style="width: 120px;" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($perspectivas as $perspectiva)
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge bg-secondary rounded-pill">
                                                {{ $perspectiva->num_nivel_hierarquico_apresentacao }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle gradient-theme-icon d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-layers text-white"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $perspectiva->dsc_perspectiva }}</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">
                                                {{ $perspectiva->objetivos_count ?? $perspectiva->objetivos()->count() }} objetivo(s)
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" wire:click="edit('{{ $perspectiva->cod_perspectiva }}')" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" wire:click="delete('{{ $perspectiva->cod_perspectiva }}')" wire:confirm="Tem certeza que deseja excluir esta perspectiva? Esta ação não pode ser desfeita." title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                        <h5 class="text-muted">Nenhuma perspectiva cadastrada</h5>
                        <p class="text-muted mb-3">Comece adicionando as perspectivas do seu Balanced Scorecard</p>
                        <button type="button" class="btn btn-primary" wire:click="create">
                            <i class="bi bi-plus-lg me-1"></i> Adicionar Perspectiva
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Dica sobre BSC -->
        <div class="card mt-4 border-info">
            <div class="card-body">
                <h6 class="card-title text-info">
                    <i class="bi bi-lightbulb me-2"></i>Sobre as Perspectivas do BSC
                </h6>
                <p class="card-text text-muted small mb-0">
                    As perspectivas tradicionais do Balanced Scorecard são: <strong>Financeira</strong>,
                    <strong>Clientes</strong>, <strong>Processos Internos</strong> e <strong>Aprendizado e Crescimento</strong>.
                    Você pode personalizar conforme a necessidade da sua organização. A ordem define a hierarquia de apresentação no mapa estratégico.
                </p>
            </div>
        </div>

        <!-- Modal de Criação/Edição -->
        @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-{{ $perspectivaId ? 'pencil' : 'plus-circle' }} me-2"></i>
                            {{ $perspectivaId ? 'Editar' : 'Nova' }} Perspectiva
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="dsc_perspectiva" class="form-label">Nome da Perspectiva <span class="text-danger">*</span></label>
                                <input type="text" wire:model="dsc_perspectiva" id="dsc_perspectiva" class="form-control @error('dsc_perspectiva') is-invalid @enderror" placeholder="Ex: Financeira, Clientes, Processos Internos..." required>
                                @error('dsc_perspectiva')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="num_nivel_hierarquico_apresentacao" class="form-label">Ordem de Apresentação <span class="text-danger">*</span></label>
                                <input type="number" wire:model="num_nivel_hierarquico_apresentacao" id="num_nivel_hierarquico_apresentacao" class="form-control @error('num_nivel_hierarquico_apresentacao') is-invalid @enderror" min="1" required>
                                <div class="form-text">Define a posição desta perspectiva no mapa estratégico (1 = topo)</div>
                                @error('num_nivel_hierarquico_apresentacao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">
                                <i class="bi bi-x-lg me-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> {{ $perspectivaId ? 'Atualizar' : 'Salvar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endif
</div>
