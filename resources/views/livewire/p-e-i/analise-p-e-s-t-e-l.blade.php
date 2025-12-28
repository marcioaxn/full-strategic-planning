<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-globe2 me-2"></i>Análise PESTEL
            </h4>
            <p class="text-muted mb-0">
                Análise do Macroambiente - Fatores Externos
                @if($organizacaoNome)
                    - <strong>{{ $organizacaoNome }}</strong>
                @endif
            </p>
        </div>
    </div>

    @if(!$peiAtivo)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Nenhum PEI ativo encontrado. Configure um PEI antes de realizar a análise PESTEL.
        </div>
    @elseif(!$organizacaoId)
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Selecione uma organização no menu superior para visualizar ou cadastrar a análise PESTEL.
        </div>
    @else
        <!-- Grid PESTEL 3x2 -->
        <div class="row g-3">
            <!-- P - Político -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100" style="border-color: #6f42c1;">
                    <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #6f42c1;">
                        <span>
                            <i class="bi bi-bank me-2"></i>
                            <strong>POLÍTICO</strong>
                        </span>
                        <button type="button" class="btn btn-sm btn-light" wire:click="create('Político')">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div class="card-body p-2">
                        <p class="text-muted small mb-2">Políticas governamentais, regulamentações, estabilidade política</p>
                        @forelse($politicos as $item)
                            <div class="card mb-2" style="border-color: rgba(111, 66, 193, 0.3);">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="mb-1 small">{{ $item['dsc_item'] }}</p>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge" style="background-color: rgba(111, 66, 193, 0.2); color: #6f42c1;">
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
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                <small>Nenhum item</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- E - Econômico -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100" style="border-color: #198754;">
                    <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #198754;">
                        <span>
                            <i class="bi bi-currency-dollar me-2"></i>
                            <strong>ECONÔMICO</strong>
                        </span>
                        <button type="button" class="btn btn-sm btn-light" wire:click="create('Econômico')">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div class="card-body p-2">
                        <p class="text-muted small mb-2">Crescimento econômico, taxas de juros, inflação, câmbio</p>
                        @forelse($economicos as $item)
                            <div class="card mb-2 border-success-subtle">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="mb-1 small">{{ $item['dsc_item'] }}</p>
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
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                <small>Nenhum item</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- S - Social -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100" style="border-color: #0d6efd;">
                    <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #0d6efd;">
                        <span>
                            <i class="bi bi-people me-2"></i>
                            <strong>SOCIAL</strong>
                        </span>
                        <button type="button" class="btn btn-sm btn-light" wire:click="create('Social')">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div class="card-body p-2">
                        <p class="text-muted small mb-2">Demografia, cultura, educação, saúde, tendências sociais</p>
                        @forelse($sociais as $item)
                            <div class="card mb-2 border-primary-subtle">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="mb-1 small">{{ $item['dsc_item'] }}</p>
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
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                <small>Nenhum item</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- T - Tecnológico -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100" style="border-color: #fd7e14;">
                    <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #fd7e14;">
                        <span>
                            <i class="bi bi-cpu me-2"></i>
                            <strong>TECNOLÓGICO</strong>
                        </span>
                        <button type="button" class="btn btn-sm btn-dark" wire:click="create('Tecnológico')">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div class="card-body p-2">
                        <p class="text-muted small mb-2">Inovação, automação, P&D, infraestrutura tecnológica</p>
                        @forelse($tecnologicos as $item)
                            <div class="card mb-2" style="border-color: rgba(253, 126, 20, 0.3);">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="mb-1 small">{{ $item['dsc_item'] }}</p>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge" style="background-color: rgba(253, 126, 20, 0.2); color: #fd7e14;">
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
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                <small>Nenhum item</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- E - Ambiental (Environmental) -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100" style="border-color: #20c997;">
                    <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #20c997;">
                        <span>
                            <i class="bi bi-tree me-2"></i>
                            <strong>AMBIENTAL</strong>
                        </span>
                        <button type="button" class="btn btn-sm btn-dark" wire:click="create('Ambiental')">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div class="card-body p-2">
                        <p class="text-muted small mb-2">Sustentabilidade, clima, recursos naturais, legislação ambiental</p>
                        @forelse($ambientais as $item)
                            <div class="card mb-2" style="border-color: rgba(32, 201, 151, 0.3);">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="mb-1 small">{{ $item['dsc_item'] }}</p>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge" style="background-color: rgba(32, 201, 151, 0.2); color: #20c997;">
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
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                <small>Nenhum item</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- L - Legal -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100" style="border-color: #dc3545;">
                    <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #dc3545;">
                        <span>
                            <i class="bi bi-journal-bookmark me-2"></i>
                            <strong>LEGAL</strong>
                        </span>
                        <button type="button" class="btn btn-sm btn-light" wire:click="create('Legal')">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div class="card-body p-2">
                        <p class="text-muted small mb-2">Leis trabalhistas, tributárias, regulamentações setoriais</p>
                        @forelse($legais as $item)
                            <div class="card mb-2 border-danger-subtle">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="mb-1 small">{{ $item['dsc_item'] }}</p>
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
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                <small>Nenhum item</small>
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
                            {{ $itemId ? 'Editar' : 'Adicionar' }} Fator {{ $dsc_categoria }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="dsc_item" class="form-label">Descrição <span class="text-danger">*</span></label>
                                <textarea wire:model="dsc_item" id="dsc_item" class="form-control @error('dsc_item') is-invalid @enderror" rows="3" placeholder="Descreva o fator..." required></textarea>
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
</div>
