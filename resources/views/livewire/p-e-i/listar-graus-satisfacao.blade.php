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

    {{-- Seção Educativa: O que são Graus de Satisfação --}}
    <div class="card border-0 shadow-sm mb-4 educational-card-gradient" x-data="{ expanded: false }">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle bg-white bg-opacity-25">
                        <i class="bi bi-book-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">
                            <i class="bi bi-mortarboard me-2"></i>{{ __('O que são Graus de Satisfação?') }}
                        </h5>
                        <p class="mb-0 text-white-50 small">
                            {{ __('Entenda como funcionam os semáforos de desempenho') }}
                        </p>
                    </div>
                </div>
                <button @click="expanded = !expanded" class="btn btn-link text-white text-decoration-none p-0" type="button">
                    <i class="bi fs-4" :class="expanded ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
            </div>
        </div>

        <div x-show="expanded" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
            <div class="card-body p-4 bg-white border-top">
                {{-- Introdução --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-info-circle me-2"></i>{{ __('O que são Graus de Satisfação?') }}
                    </h6>
                    <p class="text-muted mb-3">
                        <strong>Graus de Satisfação</strong> são faixas de desempenho que classificam o <strong>percentual de atingimento</strong> de metas de indicadores.
                        Funcionam como um <strong>semáforo visual</strong> (farol de desempenho) que facilita a interpretação rápida dos resultados:
                        verde indica bom desempenho, amarelo alerta para atenção, e vermelho sinaliza problemas críticos.
                    </p>
                    <p class="text-muted mb-0">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        <strong>Por que usar?</strong> Ao invés de analisar números brutos (ex: "atingimos 73,5%"),
                        o gestor visualiza cores e entende imediatamente se o resultado é satisfatório ou requer intervenção.
                    </p>
                </div>

                {{-- Como Funciona o Farol --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-traffic-light me-2"></i>{{ __('Como Funciona o Farol de Desempenho') }}
                    </h6>
                    <p class="small text-muted mb-3">
                        Cada grau de satisfação define um <strong>intervalo percentual</strong> (ex: 0% a 50%, 51% a 80%, 81% a 100%)
                        e uma <strong>cor associada</strong>. O sistema compara o percentual atingido com essas faixas e exibe a cor correspondente.
                    </p>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body p-3">
                            <p class="fw-bold small mb-2 text-dark">
                                <i class="bi bi-calculator me-1"></i>Fórmula do Percentual de Atingimento:
                            </p>
                            <div class="alert alert-info mb-0 py-2 px-3">
                                <p class="mb-0 small">
                                    <strong>% Atingimento = (Valor Realizado / Meta) × 100</strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- Exemplo: Crítico --}}
                        <div class="col-md-4">
                            <div class="card border-2 border-danger h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="rounded-circle border" style="background-color: #dc3545; width: 24px; height: 24px;"></div>
                                        <h6 class="fw-bold mb-0 text-danger">Crítico</h6>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        <strong>Faixa:</strong> 0% a 50%
                                    </p>
                                    <p class="x-small text-muted mb-0">
                                        Resultado muito abaixo da meta. Requer ação imediata e plano de recuperação.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Exemplo: Insatisfatório --}}
                        <div class="col-md-4">
                            <div class="card border-2 border-warning h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="rounded-circle border" style="background-color: #ffc107; width: 24px; height: 24px;"></div>
                                        <h6 class="fw-bold mb-0 text-warning">Insatisfatório</h6>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        <strong>Faixa:</strong> 51% a 80%
                                    </p>
                                    <p class="x-small text-muted mb-0">
                                        Resultado abaixo do esperado. Atenção necessária para evitar piora.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Exemplo: Satisfatório --}}
                        <div class="col-md-4">
                            <div class="card border-2 border-success h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="rounded-circle border" style="background-color: #28a745; width: 24px; height: 24px;"></div>
                                        <h6 class="fw-bold mb-0 text-success">Satisfatório</h6>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        <strong>Faixa:</strong> 81% a 100%
                                    </p>
                                    <p class="x-small text-muted mb-0">
                                        Meta atingida ou próxima do esperado. Manter os esforços atuais.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Exemplos de Escalas Populares --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-palette me-2"></i>{{ __('Escalas de Desempenho Mais Usadas') }}
                    </h6>

                    <div class="row g-3">
                        {{-- Escala 3 Níveis (Clássica) --}}
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-traffic-light me-2"></i>Escala 3 Níveis (Clássica)
                                    </h6>
                                    <div class="d-flex flex-column gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle" style="background-color: #dc3545; width: 16px; height: 16px;"></div>
                                            <span class="small">Crítico: 0% a 70%</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle" style="background-color: #ffc107; width: 16px; height: 16px;"></div>
                                            <span class="small">Atenção: 71% a 89%</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle" style="background-color: #28a745; width: 16px; height: 16px;"></div>
                                            <span class="small">Adequado: 90% a 100%</span>
                                        </div>
                                    </div>
                                    <p class="x-small text-muted mt-2 mb-0">
                                        <strong>Uso:</strong> Ideal para gestão pública. Exige 90%+ para considerar satisfatório.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Escala 5 Níveis (Detalhada) --}}
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-speedometer2 me-2"></i>Escala 5 Níveis (Detalhada)
                                    </h6>
                                    <div class="d-flex flex-column gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle" style="background-color: #8B0000; width: 16px; height: 16px;"></div>
                                            <span class="small">Péssimo: 0% a 40%</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle" style="background-color: #dc3545; width: 16px; height: 16px;"></div>
                                            <span class="small">Ruim: 41% a 60%</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle" style="background-color: #ffc107; width: 16px; height: 16px;"></div>
                                            <span class="small">Regular: 61% a 80%</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle" style="background-color: #17a2b8; width: 16px; height: 16px;"></div>
                                            <span class="small">Bom: 81% a 95%</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle" style="background-color: #28a745; width: 16px; height: 16px;"></div>
                                            <span class="small">Excelente: 96% a 100%</span>
                                        </div>
                                    </div>
                                    <p class="x-small text-muted mt-2 mb-0">
                                        <strong>Uso:</strong> Para análises mais granulares e reconhecimento de alta performance.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Exemplo Prático --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-star me-2"></i>{{ __('Exemplo Prático de Aplicação') }}
                    </h6>

                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <p class="small text-muted mb-3">
                                <strong>Indicador:</strong> "Reduzir tempo médio de atendimento"<br>
                                <strong>Meta:</strong> 15 minutos<br>
                                <strong>Valor realizado:</strong> 18 minutos<br>
                                <strong>Polaridade:</strong> Menor é melhor
                            </p>

                            <div class="alert alert-info mb-3 py-2">
                                <p class="small mb-0">
                                    <strong>Cálculo:</strong> % Atingimento = (Meta / Realizado) × 100 = (15 / 18) × 100 = <strong>83,3%</strong>
                                </p>
                            </div>

                            <table class="table table-sm table-borderless mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small fw-bold">Grau</th>
                                        <th class="small fw-bold">Faixa</th>
                                        <th class="small fw-bold">Resultado</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle" style="background-color: #dc3545; width: 12px; height: 12px;"></div>
                                                Crítico
                                            </div>
                                        </td>
                                        <td>0% a 70%</td>
                                        <td></td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle" style="background-color: #ffc107; width: 12px; height: 12px;"></div>
                                                Atenção
                                            </div>
                                        </td>
                                        <td>71% a 89%</td>
                                        <td><strong>83,3% ← Aqui!</strong></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle" style="background-color: #28a745; width: 12px; height: 12px;"></div>
                                                Adequado
                                            </div>
                                        </td>
                                        <td>90% a 100%</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>

                            <p class="small text-muted mt-3 mb-0">
                                <i class="bi bi-arrow-right-circle text-warning me-1"></i>
                                <strong>Interpretação:</strong> Indicador em <strong class="text-warning">Atenção</strong>.
                                O tempo de atendimento está próximo da meta, mas ainda precisa melhorar para atingir 90%+.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Dicas Profissionais --}}
                <div>
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-trophy me-2"></i>{{ __('Dicas para Definir Graus de Satisfação') }}
                    </h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Evite sobreposição de faixas</p>
                                    <p class="x-small text-muted mb-0">As faixas devem ser contíguas e exclusivas. Ex: 0-70%, 71-89%, 90-100%</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Use cores intuitivas</p>
                                    <p class="x-small text-muted mb-0">Vermelho = problema, Amarelo = alerta, Verde = sucesso. Evite cores confusas como roxo ou marrom</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Seja rigoroso com o "verde"</p>
                                    <p class="x-small text-muted mb-0">No setor público, 90%+ para "Adequado" é comum. Evite aprovar resultados abaixo de 80%</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Padronize para toda a organização</p>
                                    <p class="x-small text-muted mb-0">Use a mesma escala em todos os indicadores para facilitar comparações</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <p class="fw-bold small mb-0">Considere indicadores de polaridade reversa</p>
                                    <p class="x-small text-muted mb-0">Quando "menor é melhor" (ex: tempo de espera), o sistema inverte o cálculo automaticamente: (Meta/Realizado)×100</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
    </div>

    <!-- Modal de Criacao/Edicao Premium -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5); z-index: 1055;" wire:click.self="closeModal">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    
                    {{-- Header Premium --}}
                    <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle-mini bg-white bg-opacity-25 text-white">
                                <i class="bi bi-{{ $isEditing ? 'pencil-square' : 'plus-circle' }}"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">{{ $isEditing ? 'Editar Grau de Satisfação' : 'Novo Grau de Satisfação' }}</h5>
                                <p class="mb-0 small text-white-50">Configuração de faixas de atingimento do farol</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>

                    <form wire:submit="save">
                        <div class="modal-body p-4 bg-white">
                            <div class="row g-4">
                                
                                {{-- Coluna Principal: Definição --}}
                                <div class="col-lg-7">
                                    <div class="card border-0 bg-light rounded-4 h-100">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Definição do Grau</h6>
                                            
                                            {{-- Descrição --}}
                                            <div class="mb-4">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Descrição do Grau <span class="text-danger">*</span></label>
                                                <input type="text"
                                                       class="form-control form-control-lg bg-white border-0 shadow-sm @error('dsc_grau_satisfacao') is-invalid @enderror"
                                                       wire:model="dsc_grau_satisfacao"
                                                       placeholder="Ex: Excelente, Bom, Regular, Crítico...">
                                                @error('dsc_grau_satisfacao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="row g-3">
                                                {{-- Cor --}}
                                                <div class="col-md-12">
                                                    <label class="form-label text-muted small text-uppercase fw-bold">Cor Representativa <span class="text-danger">*</span></label>
                                                    <div class="input-group shadow-sm">
                                                        <span class="input-group-text bg-white border-0">
                                                            <input type="color" class="form-control form-control-color border-0 p-0" style="width: 30px; height: 30px;" wire:model.live="cor">
                                                        </span>
                                                        <input type="text" class="form-control bg-white border-0 fw-bold @error('cor') is-invalid @enderror" wire:model.live="cor" placeholder="#000000">
                                                    </div>
                                                    <small class="text-muted x-small mt-1 d-block">A cor que aparecerá no farol de desempenho</small>
                                                    @error('cor') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                                                </div>

                                                {{-- Contexto PEI e Ano --}}
                                                <div class="col-md-7">
                                                    <label class="form-label text-muted small text-uppercase fw-bold">Ciclo PEI</label>
                                                    <select wire:model="cod_pei" class="form-select bg-white border-0 shadow-sm fw-bold">
                                                        <option value="">Escala Global (Padrão)</option>
                                                        @foreach($availablePeis as $p)
                                                            <option value="{{ $p->cod_pei }}">{{ $p->dsc_pei }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label text-muted small text-uppercase fw-bold">Ano Específico</label>
                                                    <select wire:model="num_ano" class="form-select bg-white border-0 shadow-sm fw-bold">
                                                        <option value="">Todo o Ciclo</option>
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
                                        </div>
                                    </div>
                                </div>

                                {{-- Coluna Lateral: Intervalos --}}
                                <div class="col-lg-5">
                                    <div class="card border-0 bg-light rounded-4 h-100">
                                        <div class="card-body p-4 text-center">
                                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-4">Intervalo de Atingimento</h6>
                                            
                                            <div class="mb-4" x-data="{ 
                                                display: '',
                                                value: @entangle('vlr_minimo'),
                                                mask() {
                                                    let val = this.display.replace(/\D/g, '');
                                                    if (val === '') {
                                                        this.value = '';
                                                        return;
                                                    }
                                                    let floatVal = (parseFloat(val) / 100);
                                                    this.value = floatVal.toFixed(2);
                                                    this.display = floatVal.toLocaleString('pt-BR', {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2
                                                    });
                                                },
                                                init() {
                                                    if (this.value) {
                                                        this.display = parseFloat(this.value).toLocaleString('pt-BR', {
                                                            minimumFractionDigits: 2,
                                                            maximumFractionDigits: 2
                                                        });
                                                    }
                                                }
                                            }">
                                                <label class="form-label small text-muted fw-bold text-uppercase">Percentual Mínimo <span class="text-danger">*</span></label>
                                                <div class="input-group input-group-lg shadow-sm">
                                                    <span class="input-group-text bg-white border-0 text-primary"><i class="bi bi-chevron-bar-down"></i></span>
                                                    <input type="text" x-model="display" @input="mask()" class="form-control bg-white border-0 fw-bold text-center @error('vlr_minimo') is-invalid @enderror" placeholder="0,00">
                                                    <span class="input-group-text bg-white border-0 fw-bold">%</span>
                                                </div>
                                                @error('vlr_minimo') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="mb-4" x-data="{ 
                                                display: '',
                                                value: @entangle('vlr_maximo'),
                                                mask() {
                                                    let val = this.display.replace(/\D/g, '');
                                                    if (val === '') {
                                                        this.value = '';
                                                        return;
                                                    }
                                                    let floatVal = (parseFloat(val) / 100);
                                                    this.value = floatVal.toFixed(2);
                                                    this.display = floatVal.toLocaleString('pt-BR', {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2
                                                    });
                                                },
                                                init() {
                                                    if (this.value) {
                                                        this.display = parseFloat(this.value).toLocaleString('pt-BR', {
                                                            minimumFractionDigits: 2,
                                                            maximumFractionDigits: 2
                                                        });
                                                    }
                                                }
                                            }">
                                                <label class="form-label small text-muted fw-bold text-uppercase">Percentual Máximo <span class="text-danger">*</span></label>
                                                <div class="input-group input-group-lg shadow-sm">
                                                    <span class="input-group-text bg-white border-0 text-primary"><i class="bi bi-chevron-bar-up"></i></span>
                                                    <input type="text" x-model="display" @input="mask()" class="form-control bg-white border-0 fw-bold text-center @error('vlr_maximo') is-invalid @enderror" placeholder="100,00">
                                                    <span class="input-group-text bg-white border-0 fw-bold">%</span>
                                                </div>
                                                @error('vlr_maximo') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                                            </div>

                                            {{-- Dica Visual --}}
                                            <div class="p-3 bg-white rounded-3 border shadow-sm mt-auto">
                                                <p class="small text-muted mb-2">Representação no Mapa:</p>
                                                <div class="d-flex align-items-center justify-content-center gap-2 py-2 rounded" style="background-color: {{ $cor ?: '#e9ecef' }}; color: {{ $cor ? '#fff' : '#6c757d' }}; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                                    <i class="bi bi-circle-fill"></i>
                                                    <span class="fw-bold">{{ $dsc_grau_satisfacao ?: 'Aguardando...' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer Premium --}}
                        <div class="modal-footer border-0 p-4 bg-white rounded-bottom-4 shadow-top-sm">
                            <button type="button" class="btn btn-light px-4 rounded-pill fw-bold text-muted" wire:click="closeModal">Cancelar</button>
                            <button type="submit" class="btn btn-primary gradient-theme-btn px-5 rounded-pill shadow-sm hover-scale">
                                <i class="bi bi-check-lg me-2"></i>{{ $isEditing ? 'Atualizar Grau' : 'Salvar Grau' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Success Modal Premium --}}
    @if($showSuccessModal)
    <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.6); z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-body p-5 text-center bg-white">
                    <div class="mb-4">
                        <div class="icon-circle mx-auto bg-primary text-white shadow-lg scale-in-center" style="width: 80px; height: 80px; font-size: 2.5rem; background: linear-gradient(135deg, #1B408E 0%, #4361EE 100%) !important;">
                            <i class="bi bi-check-lg"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-3">Operação Concluída!</h3>
                    <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.6;">
                        <strong class="text-primary d-block mb-2">"{{ $createdGrauName }}"</strong>
                        {{ $successMessage }}
                    </p>
                    <button wire:click="closeSuccessModal" class="btn btn-primary gradient-theme-btn px-5 rounded-pill shadow hover-scale">
                        <i class="bi bi-check2-circle me-2"></i>Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Error Modal Premium --}}
    @if($showErrorModal)
    <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.6); z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-body p-5 text-center bg-white">
                    <div class="mb-4">
                        <div class="icon-circle mx-auto bg-danger text-white shadow-lg scale-in-center" style="width: 80px; height: 80px; font-size: 2.5rem; background: linear-gradient(135deg, #e63946 0%, #d62828 100%) !important;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-3">Não foi possível salvar</h3>
                    <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.6;">
                        {{ $errorMessage }}
                    </p>
                    <button wire:click="closeErrorModal" class="btn btn-danger px-5 rounded-pill shadow hover-scale">
                        <i class="bi bi-arrow-clockwise me-2"></i>Tentar Novamente
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        .scale-in-center { animation: scale-in-center 0.5s cubic-bezier(0.250, 0.460, 0.450, 0.940) both; }
        @keyframes scale-in-center { 0% { transform: scale(0); opacity: 1; } 100% { transform: scale(1); opacity: 1; } }
    </style>

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
