<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-grid-3x3-gap me-2"></i>Análise SWOT
            </h4>
            <p class="text-muted mb-0">
                Forças, Fraquezas, Oportunidades e Ameaças
                @if($organizacaoNome)
                    - <strong>{{ $organizacaoNome }}</strong>
                @endif
            </p>
        </div>
        <div>
            @if($organizacaoId && $peiAtivo)
                <button wire:click="toggleModoVisualizacao" class="btn {{ $modoVisualizacao ? 'btn-outline-primary' : 'btn-primary' }}">
                    <i class="bi {{ $modoVisualizacao ? 'bi-pencil-square' : 'bi-eye' }} me-1"></i>
                    {{ $modoVisualizacao ? 'Modo Edição' : 'Modo Apresentação' }}
                </button>
            @endif
        </div>
    </div>

    @if(!$peiAtivo)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Nenhum PEI ativo encontrado. Configure um PEI antes de realizar a análise SWOT.
        </div>
    @elseif(!$organizacaoId)
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Selecione uma organização no menu superior para visualizar ou cadastrar a análise SWOT.
        </div>
    @else
        
        @if($modoVisualizacao)
            <!-- Matriz SWOT 2x2 - Modo Apresentação -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="row g-0">
                        <!-- Cabeçalhos -->
                        <div class="col-6 text-center pb-3 border-end">
                            <h5 class="fw-bold text-success mb-0">POSITIVOS</h5>
                            <small class="text-muted">Ajudam a atingir objetivos</small>
                        </div>
                        <div class="col-6 text-center pb-3">
                            <h5 class="fw-bold text-danger mb-0">NEGATIVOS</h5>
                            <small class="text-muted">Atrapalham a atingir objetivos</small>
                        </div>
                        
                        <div class="col-12"><hr class="my-0"></div>

                        <!-- Linha 1: Interno -->
                        <div class="col-12 py-2 bg-light text-center border-bottom">
                            <span class="badge bg-secondary">AMBIENTE INTERNO</span>
                        </div>

                        <!-- S - Forças -->
                        <div class="col-md-6 border-end border-bottom p-3 bg-success-subtle bg-opacity-10">
                            <h6 class="fw-bold text-success mb-3"><i class="bi bi-plus-circle me-1"></i> FORÇAS (Strengths)</h6>
                            <ul class="list-unstyled mb-0">
                                @forelse($forcas as $item)
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="bi bi-check-circle-fill text-success me-2 mt-1 small"></i>
                                        <span>{{ $item['dsc_item'] }}</span>
                                    </li>
                                @empty
                                    <li class="text-muted fst-italic small">Nenhuma força registrada.</li>
                                @endforelse
                            </ul>
                        </div>

                        <!-- W - Fraquezas -->
                        <div class="col-md-6 border-bottom p-3 bg-danger-subtle bg-opacity-10">
                            <h6 class="fw-bold text-danger mb-3"><i class="bi bi-dash-circle me-1"></i> FRAQUEZAS (Weaknesses)</h6>
                            <ul class="list-unstyled mb-0">
                                @forelse($fraquezas as $item)
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="bi bi-x-circle-fill text-danger me-2 mt-1 small"></i>
                                        <span>{{ $item['dsc_item'] }}</span>
                                    </li>
                                @empty
                                    <li class="text-muted fst-italic small">Nenhuma fraqueza registrada.</li>
                                @endforelse
                            </ul>
                        </div>

                        <!-- Linha 2: Externo -->
                        <div class="col-12 py-2 bg-light text-center border-bottom">
                            <span class="badge bg-secondary">AMBIENTE EXTERNO</span>
                        </div>

                        <!-- O - Oportunidades -->
                        <div class="col-md-6 border-end p-3 bg-primary-subtle bg-opacity-10">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-arrow-up-circle me-1"></i> OPORTUNIDADES (Opportunities)</h6>
                            <ul class="list-unstyled mb-0">
                                @forelse($oportunidades as $item)
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="bi bi-lightbulb-fill text-primary me-2 mt-1 small"></i>
                                        <span>{{ $item['dsc_item'] }}</span>
                                    </li>
                                @empty
                                    <li class="text-muted fst-italic small">Nenhuma oportunidade registrada.</li>
                                @endforelse
                            </ul>
                        </div>

                        <!-- T - Ameaças -->
                        <div class="col-md-6 p-3 bg-warning-subtle bg-opacity-10">
                            <h6 class="fw-bold text-warning-emphasis mb-3"><i class="bi bi-exclamation-triangle-fill me-1"></i> AMEAÇAS (Threats)</h6>
                            <ul class="list-unstyled mb-0">
                                @forelse($ameacas as $item)
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="bi bi-shield-exclamation text-warning-emphasis me-2 mt-1 small"></i>
                                        <span>{{ $item['dsc_item'] }}</span>
                                    </li>
                                @empty
                                    <li class="text-muted fst-italic small">Nenhuma ameaça registrada.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Matriz SWOT 2x2 - Modo Edição -->
            <div class="row g-3">
                <!-- Linha 1: Ambiente Interno -->
                <div class="col-12">
                    <div class="text-center mb-2">
                        <span class="badge bg-secondary px-3 py-2">
                            <i class="bi bi-building me-1"></i> AMBIENTE INTERNO
                        </span>
                    </div>
                </div>

                <!-- Forças (S - Strengths) -->
                <div class="col-md-6">
                    <div class="card border-success h-100">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-plus-circle me-2"></i>
                                <strong>FORÇAS</strong> (Strengths)
                            </span>
                            <button type="button" class="btn btn-sm btn-light" wire:click="create('Força')">
                                <i class="bi bi-plus-lg"></i> Adicionar
                            </button>
                        </div>
                        <div class="card-body p-2">
                            <p class="text-muted small mb-2">Pontos fortes internos que favorecem a organização</p>
                            @forelse($forcas as $item)
                                <div class="card mb-2 border-success-subtle">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <p class="mb-1">{{ $item['dsc_item'] }}</p>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-success-subtle text-success">
                                                        Impacto: {{ $item['num_impacto'] }}/5
                                                    </span>
                                                    @if($item['txt_observacao'])
                                                        <span class="text-muted small" title="{{ $item['txt_observacao'] }}">
                                                            <i class="bi bi-chat-text"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary btn-sm" wire:click="edit('{{ $item['cod_analise'] }}')" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm" wire:click="delete('{{ $item['cod_analise'] }}')" wire:confirm="Tem certeza que deseja excluir este item?" title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    <small>Nenhuma força cadastrada</small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Fraquezas (W - Weaknesses) -->
                <div class="col-md-6">
                    <div class="card border-danger h-100">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-dash-circle me-2"></i>
                                <strong>FRAQUEZAS</strong> (Weaknesses)
                            </span>
                            <button type="button" class="btn btn-sm btn-light" wire:click="create('Fraqueza')">
                                <i class="bi bi-plus-lg"></i> Adicionar
                            </button>
                        </div>
                        <div class="card-body p-2">
                            <p class="text-muted small mb-2">Pontos fracos internos que prejudicam a organização</p>
                            @forelse($fraquezas as $item)
                                <div class="card mb-2 border-danger-subtle">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <p class="mb-1">{{ $item['dsc_item'] }}</p>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-danger-subtle text-danger">
                                                        Impacto: {{ $item['num_impacto'] }}/5
                                                    </span>
                                                    @if($item['txt_observacao'])
                                                        <span class="text-muted small" title="{{ $item['txt_observacao'] }}">
                                                            <i class="bi bi-chat-text"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary btn-sm" wire:click="edit('{{ $item['cod_analise'] }}')" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm" wire:click="delete('{{ $item['cod_analise'] }}')" wire:confirm="Tem certeza que deseja excluir este item?" title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    <small>Nenhuma fraqueza cadastrada</small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Divisor -->
                <div class="col-12">
                    <hr class="my-2">
                    <div class="text-center mb-2">
                        <span class="badge bg-secondary px-3 py-2">
                            <i class="bi bi-globe me-1"></i> AMBIENTE EXTERNO
                        </span>
                    </div>
                </div>

                <!-- Oportunidades (O - Opportunities) -->
                <div class="col-md-6">
                    <div class="card border-primary h-100">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-arrow-up-circle me-2"></i>
                                <strong>OPORTUNIDADES</strong> (Opportunities)
                            </span>
                            <button type="button" class="btn btn-sm btn-light" wire:click="create('Oportunidade')">
                                <i class="bi bi-plus-lg"></i> Adicionar
                            </button>
                        </div>
                        <div class="card-body p-2">
                            <p class="text-muted small mb-2">Fatores externos favoráveis à organização</p>
                            @forelse($oportunidades as $item)
                                <div class="card mb-2 border-primary-subtle">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <p class="mb-1">{{ $item['dsc_item'] }}</p>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-primary-subtle text-primary">
                                                        Impacto: {{ $item['num_impacto'] }}/5
                                                    </span>
                                                    @if($item['txt_observacao'])
                                                        <span class="text-muted small" title="{{ $item['txt_observacao'] }}">
                                                            <i class="bi bi-chat-text"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary btn-sm" wire:click="edit('{{ $item['cod_analise'] }}')" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm" wire:click="delete('{{ $item['cod_analise'] }}')" wire:confirm="Tem certeza que deseja excluir este item?" title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    <small>Nenhuma oportunidade cadastrada</small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Ameaças (T - Threats) -->
                <div class="col-md-6">
                    <div class="card border-warning h-100">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>AMEAÇAS</strong> (Threats)
                            </span>
                            <button type="button" class="btn btn-sm btn-dark" wire:click="create('Ameaça')">
                                <i class="bi bi-plus-lg"></i> Adicionar
                            </button>
                        </div>
                        <div class="card-body p-2">
                            <p class="text-muted small mb-2">Fatores externos desfavoráveis à organização</p>
                            @forelse($ameacas as $item)
                                <div class="card mb-2 border-warning-subtle">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <p class="mb-1">{{ $item['dsc_item'] }}</p>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-warning-subtle text-warning-emphasis">
                                                        Impacto: {{ $item['num_impacto'] }}/5
                                                    </span>
                                                    @if($item['txt_observacao'])
                                                        <span class="text-muted small" title="{{ $item['txt_observacao'] }}">
                                                            <i class="bi bi-chat-text"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary btn-sm" wire:click="edit('{{ $item['cod_analise'] }}')" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm" wire:click="delete('{{ $item['cod_analise'] }}')" wire:confirm="Tem certeza que deseja excluir este item?" title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    <small>Nenhuma ameaça cadastrada</small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Criação/Edição -->
            @if($showModal)
            <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-{{ $itemId ? 'pencil' : 'plus-circle' }} me-2"></i>
                                {{ $itemId ? 'Editar' : 'Adicionar' }} {{ $dsc_categoria }}
                            </h5>
                            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                        </div>
                        <form wire:submit="save">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="dsc_item" class="form-label">Descrição <span class="text-danger">*</span></label>
                                    <textarea wire:model="dsc_item" id="dsc_item" class="form-control @error('dsc_item') is-invalid @enderror" rows="3" placeholder="Descreva o item..." required></textarea>
                                    @error('dsc_item')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="num_impacto" class="form-label">Nível de Impacto <span class="text-danger">*</span></label>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="range" wire:model.live="num_impacto" id="num_impacto" class="form-range flex-grow-1" min="1" max="5" step="1">
                                        <span class="badge bg-secondary fs-6" style="min-width: 40px;">{{ $num_impacto }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted small mt-1">
                                        <span>1 - Muito Baixo</span>
                                        <span>5 - Muito Alto</span>
                                    </div>
                                    @error('num_impacto')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="txt_observacao" class="form-label">Observações</label>
                                    <textarea wire:model="txt_observacao" id="txt_observacao" class="form-control @error('txt_observacao') is-invalid @enderror" rows="2" placeholder="Observações adicionais (opcional)"></textarea>
                                    @error('txt_observacao')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">
                                    <i class="bi bi-x-lg me-1"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> {{ $itemId ? 'Atualizar' : 'Salvar' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        @endif
    @endif
</div>