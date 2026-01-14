<div>
    {{-- Page Header --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="icon-circle-header gradient-theme-icon">
                    <i class="bi bi-palette-fill"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ __('Graus de Satisfação') }}</h1>
                <span class="badge-modern badge-count">
                    {{ $graus->count() }}
                </span>
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
            <div class="icon-circle-mini"><i class="bi bi-check-circle-fill"></i></div>
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
                                    <small>{{ $grau->dsc_grau_satisfacao }} (@brazil_number($grau->vlr_minimo, 2)-@brazil_percent($grau->vlr_maximo, 2))</small>
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
                                <th>Ciclo / Ano</th>
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
                                        <a href="{{ route('graus-satisfacao.detalhes', $grau->cod_grau_satisfacao) }}" wire:navigate class="fw-semibold text-dark text-decoration-none hover-primary">
                                            {{ $grau->dsc_grau_satisfacao }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($grau->cod_pei)
                                            <small class="d-block fw-bold text-primary">{{ $grau->pei->dsc_pei ?? 'PEI' }}</small>
                                            <small class="text-muted">{{ $grau->num_ano ? "Ano: {$grau->num_ano}" : 'Todo o ciclo' }}</small>
                                        @else
                                            <span class="badge bg-light text-muted border">Global</span>
                                        @endif
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
                                            <a href="{{ route('graus-satisfacao.detalhes', $grau->cod_grau_satisfacao) }}" wire:navigate class="btn btn-outline-info" title="Detalhar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button class="btn btn-outline-primary" wire:click="edit('{{ $grau->cod_grau_satisfacao }}')" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" wire:click="confirmDelete('{{ $grau->cod_grau_satisfacao }}')" title="Excluir">
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

        {{-- Satisfaction Degrees Help Section (Educational Pattern) --}}
        <div class="card card-modern mt-4 border-0 shadow-sm educational-card-gradient">
            <div class="card-body p-4 text-white">
                <div class="row g-4">
                    {{-- Main Explanation --}}
                    <div class="col-12">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="flex-shrink-0">
                                <div class="icon-circle bg-white bg-opacity-25">
                                    <i class="bi bi-lightbulb-fill fs-3 text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-2 text-white">{{ __('O que é o Grau de Satisfação?') }}</h5>
                                <p class="mb-0 text-white-50" style="line-height: 1.6;">
                                    O <strong>Grau de Satisfação</strong> é o motor de sinalização visual do sistema (o famoso "Farol"). Ele traduz o percentual de atingimento dos indicadores em status compreensíveis, permitindo que a alta gestão identifique instantaneamente a saúde da estratégia através de cores e conceitos.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Maturity Thresholds Detail --}}
                    <div class="col-12">
                        <div class="bg-white bg-opacity-10 rounded-3 p-4">
                            <h5 class="fw-bold mb-4 text-white text-center">
                                <i class="bi bi-graph-up-arrow me-2"></i>
                                Método: Maturity Thresholds (Limiares de Maturidade)
                            </h5>

                            <div class="row g-3">
                                {{-- Nível 1: Evolução --}}
                                <div class="col-12 col-md-4">
                                    <div class="bg-body rounded-3 p-3 h-100 text-body shadow-sm">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary text-white fw-bold px-2 py-1">1</span>
                                            <h6 class="fw-bold mb-0 small">{{ __('Evolução por Ano') }}</h6>
                                        </div>
                                        <p class="small mb-0 opacity-90">Permite que a organização aumente o rigor das metas conforme amadurece. Ex: O "Verde" pode começar em 80% no primeiro ano e subir para 95% no último ano do PEI.</p>
                                    </div>
                                </div>

                                {{-- Nível 2: Consistência --}}
                                <div class="col-12 col-md-4">
                                    <div class="bg-body rounded-3 p-3 h-100 text-body shadow-sm">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary text-white fw-bold px-2 py-1">2</span>
                                            <h6 class="fw-bold mb-0 small">{{ __('Vínculo ao Ciclo (PEI)') }}</h6>
                                        </div>
                                        <p class="small mb-0 opacity-90">Garante a integridade histórica. As regras de satisfação ficam "congeladas" para cada PEI, impedindo que mudanças futuras distorçam os resultados visuais do passado.</p>
                                    </div>
                                </div>

                                {{-- Nível 3: Lógica de Cascata --}}
                                <div class="col-12 col-md-4">
                                    <div class="bg-body rounded-3 p-3 h-100 text-body shadow-sm">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary text-white fw-bold px-2 py-1">3</span>
                                            <h6 class="fw-bold mb-0 small">{{ __('Lógica de Fallback') }}</h6>
                                        </div>
                                        <p class="small mb-0 opacity-90">O sistema busca a régua mais específica (Ano), se não houver, usa a do Ciclo, e por fim a Global. Isso garante que o farol sempre funcione, mesmo sem configuração manual.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Why it matters --}}
                    <div class="col-12">
                        <div class="bg-body rounded-3 p-3 text-body shadow-sm">
                            <div class="d-flex align-items-start gap-2 mb-2">
                                <i class="bi bi-info-circle-fill mt-1 text-primary"></i>
                                <div>
                                    <strong class="small d-block mb-1">Impacto na Alta Gestão:</strong>
                                    <p class="mb-0 small opacity-90">
                                        Uma escala bem definida evita o "efeito melancia" (verde por fora, vermelho por dentro). Com o <strong>Maturity Thresholds</strong>, o CEO tem a segurança de que o desempenho apresentado reflete a exigência real de cada etapa do plano estratégico.
                                    </p>
                                </div>
                            </div>
                        </div>
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
                        <div class="modal-body bg-body p-4">
                            <!-- Contexto: PEI e Ano (Thresholds de Maturidade) -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Ciclo PEI</label>
                                    <select wire:model="cod_pei" class="form-select border-primary border-opacity-25">
                                        <option value="">{{ __('Escala Global (Padrão)') }}</option>
                                        @foreach($availablePeis as $p)
                                            <option value="{{ $p->cod_pei }}">{{ $p->dsc_pei }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Ano (Maturidade)</label>
                                    <select wire:model="num_ano" class="form-select">
                                        <option value="">{{ __('Todo o Ciclo') }}</option>
                                        @if($cod_pei)
                                            @php $selectedPei = $availablePeis->firstWhere('cod_pei', $cod_pei); @endphp
                                            @if($selectedPei)
                                                @foreach(range($selectedPei->num_ano_inicio_pei, $selectedPei->num_ano_fim_pei) as $ano)
                                                    <option value="{{ $ano }}">{{ $ano }}</option>
                                                @endforeach
                                            @endif
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <!-- Descricao -->
                            <div class="mb-3">
                                <label for="dsc_grau_satisfacao" class="form-label fw-semibold">
                                    Descricao <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('dsc_grau_satisfacao') is-invalid @enderror"
                                       id="dsc_grau_satisfacao"
                                       wire:model="dsc_grau_satisfacao"
                                       placeholder="Ex: Excelente, Bom, Regular, Critico...">
                                @error('dsc_grau_satisfacao')
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
                <div class="icon-circle-mini modal-icon-danger">
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
</div>
