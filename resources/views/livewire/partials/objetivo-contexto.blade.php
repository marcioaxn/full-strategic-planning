{{--
    Partial: Contexto do Objetivo Estrategico

    Exibe informacoes contextuais completas para facilitar a compreensao do usuario:
    - PEI ativo
    - Perspectiva BSC
    - Objetivo Estrategico com metadados
    - KPIs resumidos
    - Detalhamento do calculo (transparencia)

    Uso: @include('livewire.partials.objetivo-contexto', ['objetivo' => $objetivoFiltrado])
--}}

@if($objetivo)
    @php
        $perspectiva = $objetivo->perspectiva;
        $pei = $perspectiva?->pei;

        // Usar o novo metodo de resumo consolidado
        $resumo = $objetivo->getResumoDesempenho();

        $totalIndicadores = $resumo['total_indicadores'];
        $totalPlanos = $resumo['total_planos'];
        $planosConcluidos = $resumo['planos_concluidos'];
        $planosEmAndamento = $resumo['planos_em_andamento'];
        $planosAtrasados = $resumo['planos_atrasados'];
        $mediaAtingimento = $resumo['percentual_atingimento'];
        $corFarolHex = $resumo['cor_farol'];

        // Buscar detalhes dos indicadores para transparencia no calculo
        $indicadoresDiretos = $objetivo->indicadores()->with(['evolucoes', 'metasPorAno'])->get();
        $indicadoresPlanos = \App\Models\PerformanceIndicators\Indicador::whereHas('planoDeAcao', function ($q) use ($objetivo) {
            $q->where('cod_objetivo', $objetivo->cod_objetivo);
        })->with(['evolucoes', 'metasPorAno', 'planoDeAcao'])->get();
        $todosIndicadores = $indicadoresDiretos->merge($indicadoresPlanos)->unique('cod_indicador');

        // Calcular detalhes de cada indicador
        $detalhesIndicadores = [];
        $somaPesos = 0;
        foreach ($todosIndicadores as $ind) {
            $peso = $ind->num_peso ?? 1;
            $atingimento = $ind->calcularAtingimento();

            // Buscar valores do ano atual
            $evolucoes = $ind->evolucoes->where('num_ano', now()->year);
            $totalPrevisto = $ind->bln_acumulado === 'Sim'
                ? $evolucoes->sum('vlr_previsto')
                : ($evolucoes->last()->vlr_previsto ?? 0);
            $totalRealizado = $ind->bln_acumulado === 'Sim'
                ? $evolucoes->sum('vlr_realizado')
                : ($evolucoes->last()->vlr_realizado ?? 0);

            $detalhesIndicadores[] = [
                'nome' => $ind->nom_indicador,
                'tipo' => $ind->dsc_tipo,
                'acumulado' => $ind->bln_acumulado,
                'unidade' => $ind->dsc_unidade_medida,
                'peso' => $peso,
                'previsto' => $totalPrevisto,
                'realizado' => $totalRealizado,
                'atingimento' => round($atingimento, 1),
                'contribuicao' => round($atingimento * $peso, 1),
                'vinculo' => $ind->cod_objetivo ? 'Objetivo' : 'Plano de Acao',
            ];
            $somaPesos += $peso;
        }

        // Cores da perspectiva
        $coresPerspectiva = [
            1 => ['bg' => 'bg-secondary', 'text' => 'text-white'],
            2 => ['bg' => 'bg-success', 'text' => 'text-white'],
            3 => ['bg' => 'bg-info', 'text' => 'text-white'],
            4 => ['bg' => 'bg-warning', 'text' => 'text-dark'],
            5 => ['bg' => 'bg-primary', 'text' => 'text-white'],
        ];
        $cores = $coresPerspectiva[$perspectiva?->num_nivel_hierarquico_apresentacao ?? 1] ?? $coresPerspectiva[1];

        // Cor do farol baseado no percentual
        $corFarol = match(true) {
            $mediaAtingimento >= 100 => 'primary',
            $mediaAtingimento >= 70 => 'success',
            $mediaAtingimento >= 50 => 'warning',
            default => 'danger'
        };

        // Buscar graus de satisfacao para legenda
        $grausSatisfacao = \App\Models\StrategicPlanning\GrauSatisfacao::orderBy('vlr_minimo')->get();
    @endphp

    <!-- Card de Contexto Hierarquico -->
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <!-- Header com Perspectiva -->
        <div class="card-header {{ $cores['bg'] }} {{ $cores['text'] }} py-2">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="opacity-75">
                        <i class="bi bi-diagram-3 me-1"></i>
                        {{ $pei?->dsc_pei ?? 'PEI' }} | Perspectiva {{ $perspectiva?->num_nivel_hierarquico_apresentacao ?? '' }}
                    </small>
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-layers me-2"></i>{{ $perspectiva?->dsc_perspectiva ?? 'Perspectiva' }}
                    </h6>
                </div>
                <a href="{{ route('pei.mapa') }}" class="btn btn-sm btn-light" wire:navigate title="Voltar ao Mapa Estrategico">
                    <i class="bi bi-map me-1"></i> Mapa
                </a>
            </div>
        </div>

        <!-- Corpo com Objetivo -->
        <div class="card-body">
            <!-- Objetivo Estrategico -->
            <div class="mb-3">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle {{ $cores['bg'] }} {{ $cores['text'] }} d-flex align-items-center justify-content-center"
                             style="width: 48px; height: 48px; font-weight: bold;">
                            {{ $perspectiva?->num_nivel_hierarquico_apresentacao ?? '?' }}.{{ $objetivo->num_nivel_hierarquico_apresentacao ?? '?' }}
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">{{ $objetivo->nom_objetivo }}</h5>
                        @if($objetivo->dsc_objetivo)
                            <p class="text-muted mb-0 small">{{ $objetivo->dsc_objetivo }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- KPIs em Cards -->
            <div class="row g-2">
                <!-- Total Indicadores -->
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 bg-light">
                        <div class="card-body py-2 px-3 text-center">
                            <div class="fs-4 fw-bold text-primary">{{ $totalIndicadores }}</div>
                            <small class="text-muted">
                                <i class="bi bi-graph-up me-1"></i>Indicadores
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Media Atingimento com Tooltip -->
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 bg-light position-relative"
                         data-bs-toggle="tooltip"
                         data-bs-placement="top"
                         data-bs-html="true"
                         title="<strong>Media Ponderada</strong><br>Soma(Atingimento x Peso) / Soma(Pesos)<br><small class='text-muted'>Clique em 'Ver calculo' para detalhes</small>">
                        <div class="card-body py-2 px-3 text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <div class="fs-4 fw-bold text-{{ $corFarol }}">@brazil_percent($mediaAtingimento, 1)</div>
                                <i class="bi bi-info-circle text-muted small" style="cursor: help;"></i>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-speedometer2 me-1"></i>Atingimento
                            </small>
                        </div>
                        @if($corFarolHex)
                            <div class="position-absolute bottom-0 start-0 end-0" style="height: 3px; background-color: {{ $corFarolHex }};"></div>
                        @endif
                    </div>
                </div>

                <!-- Total Planos -->
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 bg-light">
                        <div class="card-body py-2 px-3 text-center">
                            <div class="fs-4 fw-bold text-info">{{ $totalPlanos }}</div>
                            <small class="text-muted">
                                <i class="bi bi-list-check me-1"></i>Planos
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Status dos Planos -->
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 bg-light"
                         data-bs-toggle="tooltip"
                         data-bs-placement="top"
                         data-bs-html="true"
                         title="<span class='text-success'>Verde</span>: Concluidos<br><span class='text-primary'>Azul</span>: Em Andamento<br><span class='text-danger'>Vermelho</span>: Atrasados">
                        <div class="card-body py-2 px-3 text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <span class="badge bg-success" title="Concluidos">{{ $planosConcluidos }}</span>
                                <span class="badge bg-primary" title="Em Andamento">{{ $planosEmAndamento }}</span>
                                @if($planosAtrasados > 0)
                                    <span class="badge bg-danger" title="Atrasados">{{ $planosAtrasados }}</span>
                                @endif
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-clipboard-check me-1"></i>Status
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botao para expandir detalhes do calculo -->
            <div class="mt-3 text-center">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#detalhesCalculo{{ $objetivo->cod_objetivo }}" aria-expanded="false">
                    <i class="bi bi-calculator me-1"></i>Ver como e calculado
                    <i class="bi bi-chevron-down ms-1"></i>
                </button>
            </div>

            <!-- Secao Colapsavel: Detalhes do Calculo -->
            <div class="collapse mt-3" id="detalhesCalculo{{ $objetivo->cod_objetivo }}">
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <!-- Legenda de Cores do Farol -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">
                                <i class="bi bi-palette me-2"></i>Legenda do Farol de Desempenho
                            </h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($grausSatisfacao as $grau)
                                    <div class="d-flex align-items-center px-2 py-1 bg-white rounded border">
                                        <span class="rounded-circle me-2" style="width: 12px; height: 12px; background-color: {{ $grau->cor }}; display: inline-block;"></span>
                                        <small>
                                            @brazil_percent($grau->vlr_minimo, 0) - @brazil_percent($grau->vlr_maximo, 0)
                                            @if($grau->dsc_grau_satisfacao)
                                                <span class="text-muted">({{ $grau->dsc_grau_satisfacao }})</span>
                                            @endif
                                        </small>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Metodologia de Calculo -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">
                                <i class="bi bi-book me-2"></i>Metodologia de Calculo
                            </h6>
                            <div class="bg-white p-3 rounded border">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Formula do Atingimento Individual:</strong></p>
                                        <code class="d-block bg-dark text-light p-2 rounded mb-2">
                                            Atingimento = (Realizado / Previsto) x 100
                                        </code>
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Para indicadores <strong>acumulados</strong>, soma-se todos os valores do periodo.
                                            Para <strong>nao acumulados</strong>, usa-se o ultimo valor disponivel.
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Formula do Atingimento Consolidado:</strong></p>
                                        <code class="d-block bg-dark text-light p-2 rounded mb-2">
                                            Media = Soma(Ating. x Peso) / Soma(Pesos)
                                        </code>
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            A media ponderada considera o peso de cada indicador, permitindo priorizar indicadores mais importantes.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabela Detalhada dos Indicadores -->
                        @if(count($detalhesIndicadores) > 0)
                            <div>
                                <h6 class="fw-bold mb-2">
                                    <i class="bi bi-table me-2"></i>Detalhamento por Indicador ({{ now()->year }})
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover bg-white mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Indicador</th>
                                                <th class="text-center" title="Tipo de acumulacao">Tipo</th>
                                                <th class="text-end">Previsto</th>
                                                <th class="text-end">Realizado</th>
                                                <th class="text-center">Peso</th>
                                                <th class="text-end">Atingimento</th>
                                                <th class="text-end" title="Atingimento x Peso">Contribuicao</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($detalhesIndicadores as $det)
                                                @php
                                                    $corLinha = match(true) {
                                                        $det['atingimento'] >= 100 => 'table-primary',
                                                        $det['atingimento'] >= 70 => 'table-success',
                                                        $det['atingimento'] >= 50 => 'table-warning',
                                                        default => 'table-danger'
                                                    };
                                                @endphp
                                                <tr class="{{ $corLinha }}">
                                                    <td>
                                                        <small class="fw-semibold">{{ Str::limit($det['nome'], 40) }}</small>
                                                        <br>
                                                        <span class="badge bg-secondary bg-opacity-25 text-secondary" style="font-size: 0.65rem;">
                                                            {{ $det['vinculo'] }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge {{ $det['acumulado'] === 'Sim' ? 'bg-info' : 'bg-secondary' }} bg-opacity-25 {{ $det['acumulado'] === 'Sim' ? 'text-info' : 'text-secondary' }}" title="{{ $det['acumulado'] === 'Sim' ? 'Soma todos os valores do periodo' : 'Usa o ultimo valor disponivel' }}">
                                                            {{ $det['acumulado'] === 'Sim' ? 'Acum.' : 'Pontual' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <small>@brazil_number($det['previsto'], 2)</small>
                                                        @if($det['unidade'])
                                                            <small class="text-muted">{{ $det['unidade'] }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <small>@brazil_number($det['realizado'], 2)</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-dark bg-opacity-10 text-dark">{{ $det['peso'] }}</span>
                                                    </td>
                                                    <td class="text-end fw-bold">
                                                        @brazil_percent($det['atingimento'], 1)
                                                    </td>
                                                    <td class="text-end">
                                                        <small class="text-muted">{{ $det['contribuicao'] }}</small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="4" class="text-end">Total:</td>
                                                <td class="text-center">{{ $somaPesos }}</td>
                                                <td colspan="2" class="text-end">
                                                    <span class="text-{{ $corFarol }}">@brazil_percent($mediaAtingimento, 1)</span>
                                                    <small class="text-muted fw-normal d-block">
                                                        ({{ array_sum(array_column($detalhesIndicadores, 'contribuicao')) }} / {{ $somaPesos }})
                                                    </small>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Nenhum indicador cadastrado para este objetivo.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer com navegacao rapida -->
        <div class="card-footer bg-light py-2">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>
                    Vigencia: {{ $pei?->num_ano_inicio_pei ?? '?' }} - {{ $pei?->num_ano_fim_pei ?? '?' }}
                    <span class="mx-2">|</span>
                    <i class="bi bi-calendar me-1"></i>
                    Ano de referencia: {{ now()->year }}
                </small>
                <div class="d-flex gap-2">
                    <a href="{{ route('indicadores.index', ['filtroObjetivo' => $objetivo->cod_objetivo]) }}"
                       class="btn btn-sm btn-outline-primary {{ request()->routeIs('indicadores.*') ? 'active' : '' }}"
                       wire:navigate>
                        <i class="bi bi-graph-up me-1"></i>Indicadores
                    </a>
                    <a href="{{ route('planos.index', ['filtroObjetivo' => $objetivo->cod_objetivo]) }}"
                       class="btn btn-sm btn-outline-info {{ request()->routeIs('planos.*') ? 'active' : '' }}"
                       wire:navigate>
                        <i class="bi bi-list-check me-1"></i>Planos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Inicializar Tooltips -->
    <script>
        (function() {
            function initTooltips() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                    // Destruir tooltip existente se houver
                    var existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                    if (existingTooltip) {
                        existingTooltip.dispose();
                    }
                    new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
            // Inicializar na carga e na navegacao Livewire
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initTooltips);
            } else {
                initTooltips();
            }
            document.addEventListener('livewire:navigated', initTooltips);
        })();
    </script>
@endif