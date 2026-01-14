<div>
    <style>
        @media print {
            body { background: white !important; }
            .navbar, .sidebar, .leads-header, .alert, .btn, .breadcrumb, footer { display: none !important; }
            .card { border: none !important; shadow: none !important; break-inside: avoid; }
            .container-fluid { padding: 0 !important; width: 100% !important; max-width: 100% !important; }
            .main-content { margin: 0 !important; padding: 0 !important; }
            .pestel-print-header { display: block !important; margin-bottom: 20px; text-align: center; }
        }
        .pestel-print-header { display: none; }
    </style>

    <div class="pestel-print-header">
        <h2>Análise PESTEL</h2>
        <p>{{ $organizacaoNome }} - {{ $peiAtivo->dsc_pei ?? '' }}</p>
    </div>

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
        <div class="d-flex gap-2">
            @if($organizacaoId && $peiAtivo && $aiEnabled)
                <button wire:click="pedirAjudaIA" wire:loading.attr="disabled" class="btn btn-outline-primary shadow-sm rounded-pill">
                    <span wire:loading.remove wire:target="pedirAjudaIA">
                        <i class="bi bi-robot"></i> Sugerir com IA
                    </span>
                    <span wire:loading wire:target="pedirAjudaIA">
                        <span class="spinner-border spinner-border-sm me-1"></span>
                    </span>
                </button>
            @endif
            @if($organizacaoId && $peiAtivo)
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i> Imprimir
                </button>
            @endif
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
        @if($aiSuggestion)
            <div class="card border-0 shadow-sm mb-4 animate-fade-in" style="background: linear-gradient(135deg, #fdf8ff 0%, #ffffff 100%);">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-robot me-2"></i>Sugestões do Mentor IA (PESTEL)</h6>
                    <button type="button" class="btn-close" style="font-size: 0.7rem;" wire:click="$set('aiSuggestion', '')"></button>
                </div>
                <div class="card-body">
                    @if(is_array($aiSuggestion))
                        <div class="row g-3">
                            @foreach([
                                'Político' => 'politico', 
                                'Econômico' => 'economico', 
                                'Social' => 'social', 
                                'Tecnológico' => 'tecnologico', 
                                'Ecológico' => 'ecologico', 
                                'Legal' => 'legal'
                            ] as $label => $key)
                                @if(isset($aiSuggestion[$key]) && count($aiSuggestion[$key]) > 0)
                                    <div class="col-md-2">
                                        <div class="small fw-bold text-muted text-uppercase mb-2" style="font-size: 0.65rem;">{{ $label }}</div>
                                        <div class="list-group list-group-flush border rounded">
                                            @foreach($aiSuggestion[$key] as $item)
                                                <button type="button" wire:click="adicionarSugerido('{{ $label }}', '{{ $item }}')" class="list-group-item list-group-item-action py-2 px-2 x-small d-flex justify-content-between align-items-center">
                                                    <span class="text-truncate me-1">{{ $item }}</span>
                                                    <i class="bi bi-plus-circle text-primary"></i>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <span class="spinner-border spinner-border-sm text-primary me-2"></span>
                            <span class="text-muted">Analisando tendências globais...</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
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

        {{-- PESTEL Help Section --}}
        <div class="card card-modern mt-4 border-0 shadow-sm educational-card-gradient animate-fade-in">
            <div class="card-body p-4 text-white">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="flex-shrink-0">
                                <div class="icon-circle bg-white bg-opacity-25">
                                    <i class="bi bi-compass-fill fs-3 text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-2 text-white">{{ __('O que é a Análise PESTEL?') }}</h5>
                                <p class="mb-0 text-white-50" style="line-height: 1.6;">
                                    A análise <strong>PESTEL</strong> foca exclusivamente no ambiente externo. Ela ajuda a mapear as grandes tendências que a organização não controla, mas que podem impactar drasticamente o planejamento a longo prazo.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3">
                            <div class="row g-3">
                                <div class="col-md-4 small"><strong>P (Político):</strong> Estabilidade, impostos, comércio exterior.</div>
                                <div class="col-md-4 small"><strong>E (Econômico):</strong> Inflação, juros, crescimento do PIB.</div>
                                <div class="col-md-4 small"><strong>S (Social):</strong> Demografia, cultura, hábitos de consumo.</div>
                                <div class="col-md-4 small"><strong>T (Tecnológico):</strong> Automação, inovação, novos softwares.</div>
                                <div class="col-md-4 small"><strong>E (Ecológico):</strong> Clima, reciclagem, sustentabilidade.</div>
                                <div class="col-md-4 small"><strong>L (Legal):</strong> Leis trabalhistas, saúde e segurança.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
