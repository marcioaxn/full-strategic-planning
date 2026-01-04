<div>
    {{-- Page Header --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="header-icon gradient-theme-icon">
                    <i class="bi bi-palette-fill"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ __('Graus de Satisfação') }}</h1>
            </div>
            <p class="text-muted mb-0">
                {{ __('Defina as faixas de atingimento e cores do farol de desempenho.') }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-primary gradient-theme-btn px-4 shadow-sm" wire:click="openModal">
                <i class="bi bi-plus-lg me-1"></i> {{ __('Novo Grau') }}
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-modern alert-success alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert">
            <div class="alert-icon"><i class="bi bi-check-circle-fill"></i></div>
            <span class="flex-grow-1">{{ session('message') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Mentor de IA --}}
    @if($aiEnabled)
        <div class="ai-mentor-wrapper animate-fade-in">
            <button wire:click="pedirAjudaIA" wire:loading.attr="disabled" class="ai-magic-button shadow-sm">
                <span wire:loading.remove wire:target="pedirAjudaIA">
                    <i class="bi bi-robot"></i> {{ __('Sugerir Escala de Satisfação com IA') }}
                </span>
                <span wire:loading wire:target="pedirAjudaIA">
                    <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Calculando faixas ideais...') }}
                </span>
            </button>

            @if($aiSuggestion)
                <div class="ai-insight-card animate-fade-in">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-chat-left-dots-fill text-primary"></i>
                            <h6 class="fw-bold mb-0">{{ __('Escala Sugerida pelo Mentor IA') }}</h6>
                        </div>
                        <button type="button" class="btn-close small" style="font-size: 0.7rem;" wire:click="$set('aiSuggestion', '')"></button>
                    </div>
                    <div class="card-body">
                        @if(is_array($aiSuggestion))
                            <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                                @foreach($aiSuggestion as $sug)
                                    <div class="list-group-item d-flex align-items-center justify-content-between p-3 bg-light bg-opacity-25 hover-bg-white transition-all">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle border shadow-sm" style="background-color: {{ $sug['cor'] }}; width: 24px; height: 24px;"></div>
                                            <div>
                                                <span class="fw-bold text-dark">{{ $sug['nome'] }}</span>
                                                <small class="text-muted ms-2">({{ $sug['min'] }}% a {{ $sug['max'] }}%)</small>
                                            </div>
                                        </div>
                                        <button wire:click="aplicarSugestao('{{ $sug['nome'] }}', '{{ $sug['cor'] }}', {{ $sug['min'] }}, {{ $sug['max'] }})" 
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                                            <i class="bi bi-plus-lg me-1"></i> {{ __('Adicionar') }}
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="container-fluid px-0">
        <!-- Card Principal -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="bi bi-palette me-2"></i>Configuracao de Graus de Satisfacao
                        </h5>
                        <small class="text-muted">Defina os intervalos percentuais e cores para classificar o desempenho</small>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text"
                                   class="form-control border-start-0"
                                   placeholder="Buscar por descricao ou cor..."
                                   wire:model.live.debounce.300ms="search">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <!-- Preview de Cores -->
                @if($graus->count() > 0)
                    <div class="bg-light p-3 border-bottom">
                        <small class="text-muted fw-bold text-uppercase mb-2 d-block">Preview da Legenda:</small>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($graus as $grau)
                                <div class="d-flex align-items-center">
                                    <span class="rounded-circle me-2" style="width: 16px; height: 16px; background-color: {{ $grau->cor }};"></span>
                                    <small>{{ $grau->dsc_grau_satisfcao }} (@brazil_number($grau->vlr_minimo, 2)-@brazil_percent($grau->vlr_maximo, 2))</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th class="px-4" style="width: 1%;">Cor</th>
                                <th>Descricao</th>
                                <th class="text-center">Código da Cor</th>
                                <th class="text-center">Min (%)</th>
                                <th class="text-center">Max (%)</th>
                                <th class="text-center" style="width: 1%;">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($graus as $grau)
                                <tr>
                                    <td class="px-4">
                                        <span class="d-inline-block rounded-circle border shadow-sm"
                                              style="width: 32px; height: 32px; background-color: {{ $grau->cor }};"
                                              data-bs-toggle="tooltip"
                                              title="{{ $grau->cor }}"></span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $grau->dsc_grau_satisfcao }}</span>
                                    </td>
                                    <td class="text-center">
                                        <code class="bg-light px-2 py-1 rounded">{{ $grau->cor }}</code>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-secondary">@brazil_percent($grau->vlr_minimo, 2)</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary">@brazil_percent($grau->vlr_maximo, 2)</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" wire:click="edit('{{ $grau->cod_grau_satisfcao }}')" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" wire:click="confirmDelete('{{ $grau->cod_grau_satisfcao }}')" title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-palette fs-1 d-block mb-3 opacity-50"></i>
                                            <p class="mb-2">Nenhum grau de satisfacao cadastrado</p>
                                            <button class="btn btn-primary btn-sm" wire:click="openModal">
                                                <i class="bi bi-plus-circle me-1"></i> Cadastrar Primeiro Grau
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($graus->hasPages())
                <div class="card-footer bg-white">
                    {{ $graus->links() }}
                </div>
            @endif
        </div>

        <!-- Dica de Configuracao (Novo Padrão) -->
        <div class="card card-modern border-0 shadow-sm educational-card-gradient mt-4 overflow-hidden" x-data="{ open: false }">
            <div class="card-body p-0">
                <div class="p-3 px-4 d-flex align-items-center justify-content-between cursor-pointer" @click="open = !open">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white bg-opacity-20 p-2">
                            <i class="bi bi-lightbulb-fill text-white"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-white">{{ __('O que são os Graus de Satisfação?') }}</h6>
                    </div>
                    <i class="bi bi-chevron-down text-white transition-all" :class="open ? 'rotate-180' : ''"></i>
                </div>
                
                <div x-show="open" x-collapse x-cloak class="p-4 bg-body text-body text-start">
                    <p class="mb-3">
                        O <strong>Grau de Satisfação</strong> é o motor que gera a sinalização visual (o famoso "farol") em todo o sistema. Ele traduz percentuais numéricos em status compreensíveis para a alta gestão.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 border bg-light h-100">
                                <h6 class="fw-bold text-primary"><i class="bi bi-bullseye me-2"></i>Padronização</h6>
                                <p class="small text-muted mb-0">Define uma régua única para que todos os departamentos falem a mesma língua ao medir o sucesso.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 border bg-light h-100">
                                <h6 class="fw-bold text-success"><i class="bi bi-lightning-charge me-2"></i>Agilidade</h6>
                                <p class="small text-muted mb-0">Permite que o CEO identifique em segundos quais áreas estão críticas através das cores automáticas.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 border bg-light h-100">
                                <h6 class="fw-bold text-info"><i class="bi bi-check-all me-2"></i>Consistência</h6>
                                <p class="small text-muted mb-0">Garante que o cálculo de atingimento dos objetivos e do mapa estratégico seja visualmente coerente.</p>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info border-0 bg-info-subtle mt-3 mb-0 small">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Dica Técnica:</strong> Certifique-se de que os intervalos cubram de 0% a 100% sem lacunas. Use cores de alto contraste (ex: Vermelho para Crítico, Verde para Excelente) para facilitar a leitura rápida.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Criacao/Edicao -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-{{ $isEditing ? 'pencil' : 'plus-circle' }} me-2"></i>
                            {{ $isEditing ? 'Editar' : 'Novo' }} Grau de Satisfacao
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <!-- Descricao -->
                            <div class="mb-3">
                                <label for="dsc_grau_satisfcao" class="form-label fw-semibold">
                                    Descricao <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('dsc_grau_satisfcao') is-invalid @enderror"
                                       id="dsc_grau_satisfcao"
                                       wire:model="dsc_grau_satisfcao"
                                       placeholder="Ex: Excelente, Bom, Regular, Critico...">
                                @error('dsc_grau_satisfcao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cor -->
                            <div class="mb-3">
                                <label for="cor" class="form-label fw-semibold">
                                    Cor <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="color"
                                           class="form-control form-control-color @error('cor') is-invalid @enderror"
                                           id="cor"
                                           wire:model.live="cor"
                                           value="{{ $cor ?: '#FF0000' }}"
                                           title="Selecione uma cor">
                                    <input type="text"
                                           class="form-control @error('cor') is-invalid @enderror"
                                           wire:model.live="cor"
                                           placeholder="#FF0000"
                                           style="max-width: 120px;">
                                    @error('cor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Clique no seletor para escolher uma cor ou digite o codigo hexadecimal</small>
                            </div>

                            <div class="row">
                                <!-- Valor Minimo -->
                                <div class="col-md-6 mb-3">
                                    <label for="vlr_minimo" class="form-label fw-semibold">
                                        Percentual Minimo <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               max="999.99"
                                               class="form-control @error('vlr_minimo') is-invalid @enderror"
                                               id="vlr_minimo"
                                               wire:model="vlr_minimo"
                                               placeholder="0.00">
                                        <span class="input-group-text">%</span>
                                        @error('vlr_minimo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Valor Maximo -->
                                <div class="col-md-6 mb-3">
                                    <label for="vlr_maximo" class="form-label fw-semibold">
                                        Percentual Maximo <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               max="999.99"
                                               class="form-control @error('vlr_maximo') is-invalid @enderror"
                                               id="vlr_maximo"
                                               wire:model="vlr_maximo"
                                               placeholder="100.00">
                                        <span class="input-group-text">%</span>
                                        @error('vlr_maximo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-secondary px-4" wire:click="closeModal">
                                <i class="bi bi-x-circle me-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary gradient-theme-btn px-4">
                                <i class="bi bi-check-circle me-1"></i> {{ $isEditing ? 'Atualizar' : 'Salvar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Exclusão --}}
    <x-confirmation-modal wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="modal-icon modal-icon-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold text-dark">{{ __('Excluir Grau de Satisfação') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Esta ação é irreversível') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation text-start text-dark">
                <p class="mb-2">
                    {{ __('Tem certeza que deseja excluir este grau de satisfação?') }}
                </p>
                <div class="alert alert-warning bg-warning-subtle border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção:</strong> Isso afetará a sinalização (farol) de todos os indicadores que dependem desta faixa de atingimento.
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDeleteModal', false)" wire:loading.attr="disabled" class="btn-modern">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-danger-button wire:click="delete" wire:loading.attr="disabled" class="btn-delete-modern ms-2">
                <span wire:loading.remove wire:target="delete">
                    <i class="bi bi-trash me-1"></i>{{ __('Excluir Agora') }}
                </span>
                <span wire:loading wire:target="delete">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                </span>
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
    <style>
        .rotate-180 { transform: rotate(180deg); }
        .transition-all { transition: all 0.3s ease; }
        .animate-fade-in { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</div>
