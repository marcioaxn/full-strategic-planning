<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Executivo Consolidado - SPS</title>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        
        /* Cores e Identidade */
        .text-primary { color: #1B408E; }
        .bg-primary { background-color: #1B408E; color: white; }
        .border-primary { border: 1px solid #1B408E; }
        
        /* Cabeçalho */
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1B408E; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header .org-name { font-size: 14px; font-weight: bold; margin-top: 5px; }
        
        /* Seções */
        .section { margin-bottom: 25px; clear: both; }
        .section-title { background: #f1f4f9; color: #1B408E; padding: 8px 12px; font-size: 12px; font-weight: bold; border-left: 4px solid #1B408E; margin-bottom: 12px; text-transform: uppercase; }
        
        /* Tabelas */
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #dee2e6; padding: 6px 8px; text-align: left; }
        th { background: #f8f9fa; font-size: 9px; color: #1B408E; text-transform: uppercase; font-weight: bold; }
        
        /* Tags de Identidade */
        .tag-container { margin-top: 10px; }
        .tag { display: inline-block; padding: 3px 10px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; margin-right: 5px; margin-bottom: 5px; font-size: 9px; font-weight: bold; }
        .tag-warning { background: #fffcf0; border-color: #ffe69c; color: #856404; }

        /* Gráfico de Colunas (CSS Chart) */
        .chart-container { width: 100%; height: 200px; margin-bottom: 30px; position: relative; border-bottom: 1px solid #ddd; margin-top: 20px; }
        .column-wrapper { float: left; height: 100%; position: relative; text-align: center; }
        .column-bar { position: absolute; bottom: 0; width: 40px; margin: 0 auto; left: 0; right: 0; border-radius: 3px 3px 0 0; }
        .column-value { position: absolute; top: -15px; width: 100%; font-weight: bold; font-size: 8px; }
        .column-label { position: absolute; bottom: -35px; width: 100%; font-size: 7px; height: 30px; line-height: 1.1; overflow: hidden; }

        /* Matriz SWOT */
        .swot-grid { width: 100%; border-collapse: separate; border-spacing: 5px; }
        .swot-box { width: 50%; height: auto; min-height: 80px; padding: 10px; vertical-align: top; border-radius: 5px; }
        .swot-s { background-color: #d1e7dd; border: 1px solid #a3cfbb; }
        .swot-w { background-color: #f8d7da; border: 1px solid #f1aeb5; }
        .swot-o { background-color: #cfe2ff; border: 1px solid #9ec5fe; }
        .swot-t { background-color: #fff3cd; border: 1px solid #ffe69c; }
        .swot-title { font-weight: bold; font-size: 11px; margin-bottom: 5px; display: block; }
        .swot-list { margin: 0; padding-left: 15px; font-size: 9px; }

        /* Barras de Progresso */
        .progress-container { background: #eee; border-radius: 10px; height: 10px; width: 100%; position: relative; margin-top: 4px; }
        .progress-bar { background: #1B408E; height: 100%; border-radius: 10px; }
        
        /* Status Badges */
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; text-transform: uppercase; color: white; }
        .bg-success { background-color: #429B22; }
        .bg-warning { background-color: #F3C72B; color: #000; }
        .bg-danger { background-color: #dc3545; }
        .bg-info { background-color: #0dcaf0; color: #000; }
        .bg-secondary { background-color: #475569; }
        
        /* Legendas */
        .legend-box { margin-top: 5px; padding: 10px; background: #fff; border: 1px solid #eee; border-radius: 5px; }
        .legend-item { display: inline-block; margin-right: 15px; font-size: 8px; }
        .legend-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 4px; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @php
        $getCorSatisfacao = function($percentual) use ($grausSatisfacao) {
            foreach ($grausSatisfacao as $grau) {
                if ($percentual >= $grau->vlr_minimo && $percentual <= $grau->vlr_maximo) {
                    return $grau->cor;
                }
            }
            return '#dee2e6';
        };

        $ano = $filtros['ano'];
        $mesLimite = $filtros['mesLimite'];

        // Preparar dados para o gráfico de colunas
        $dadosGrafico = [];
        foreach($perspectivas as $p) {
            $atingimentosPersp = [];
            foreach($p->objetivos as $obj) {
                $atingimentosPersp[] = $obj->calcularAtingimentoConsolidado($ano, $mesLimite);
            }
            $mediaPersp = count($atingimentosPersp) > 0 ? array_sum($atingimentosPersp) / count($atingimentosPersp) : 0;
            $dadosGrafico[] = [
                'label' => $p->dsc_perspectiva,
                'valor' => $mediaPersp,
                'cor' => $getCorSatisfacao($mediaPersp)
            ];
        }
    @endphp

    <!-- Cabeçalho -->
    <div class="header">
        <h1 class="text-primary">Relatório Executivo de Gestão Estratégica</h1>
        <div class="org-name">{{ $organizacao->nom_organizacao }}</div>
        
        @if(isset($filtros))
            <div style="margin-top: 10px; font-size: 9px; color: #555;">
                <strong>Ano de Referência:</strong> {{ $ano }} | 
                <strong>Período Analisado:</strong> {{ $filtros['periodo'] }} (até Mês {{ $mesLimite }}) | 
                <strong>Filtro Perspectiva:</strong> {{ $filtros['perspectiva'] }}
            </div>
        @endif
        <div style="font-size: 8px; color: #999; margin-top: 5px;">Gerado em {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <!-- 1. Identidade e Diagnóstico -->
    <div class="section">
        <div class="section-title">1. Identidade e Diagnóstico Estratégico</div>
        
        <table style="border: none; margin-bottom: 10px;">
            <tr>
                <td style="width: 50%; border: none; padding: 0 5px 0 0;">
                    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; border: 1px solid #eee; min-height: 60px;">
                        <strong class="text-primary" style="font-size: 9px;">MISSÃO</strong><br>
                        <p style="font-style: italic; margin-top: 5px; font-size: 10px;">"{{ $identidade->dsc_missao ?: 'Não definida.' }}"</p>
                    </div>
                </td>
                <td style="width: 50%; border: none; padding: 0 0 0 5px;">
                    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; border: 1px solid #eee; min-height: 60px;">
                        <strong class="text-primary" style="font-size: 9px;">VISÃO</strong><br>
                        <p style="font-style: italic; margin-top: 5px; font-size: 10px;">"{{ $identidade->dsc_visao ?: 'Não definida.' }}"</p>
                    </div>
                </td>
            </tr>
        </table>

        <div>
            <strong class="text-primary" style="font-size: 9px; text-transform: uppercase;">Valores Institucionais:</strong>
            <div class="tag-container">
                @forelse($valores as $valor)
                    <span class="tag">{{ $valor->nom_valor }}</span>
                @empty
                    <span style="font-style: italic; color: #999; font-size: 9px;">Nenhum valor cadastrado.</span>
                @endforelse
            </div>
        </div>

        <div style="margin-top: 10px;">
            <strong class="text-primary" style="font-size: 9px; text-transform: uppercase;">Objetivos Estratégicos:</strong>
            <div class="tag-container">
                @forelse($objetivosEstrategicos as $obj)
                    <span class="tag tag-warning">{{ $obj->nom_objetivo_estrategico }}</span>
                @empty
                    <span style="font-style: italic; color: #999; font-size: 9px;">Nenhum objetivo estratégico cadastrado para esta unidade.</span>
                @endforelse
            </div>
        </div>

        @if($swot->isNotEmpty())
            <div style="margin-top: 15px; font-weight: bold; color: #666; margin-bottom: 5px; font-size: 9px; text-transform: uppercase;">Análise de Ambiente (SWOT):</div>
            <table class="swot-grid">
                <tr>
                    <td class="swot-box swot-s">
                        <span class="swot-title text-success">FORÇAS (Interno +)</span>
                        <ul class="swot-list">
                            @forelse($swot->get('Força', []) as $item) <li>{{ $item->dsc_item }}</li> @empty <li>-</li> @endforelse
                        </ul>
                    </td>
                    <td class="swot-box swot-w">
                        <span class="swot-title text-danger">FRAQUEZAS (Interno -)</span>
                        <ul class="swot-list">
                            @forelse($swot->get('Fraqueza', []) as $item) <li>{{ $item->dsc_item }}</li> @empty <li>-</li> @endforelse
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td class="swot-box swot-o">
                        <span class="swot-title text-primary">OPORTUNIDADES (Externo +)</span>
                        <ul class="swot-list">
                            @forelse($swot->get('Oportunidade', []) as $item) <li>{{ $item->dsc_item }}</li> @empty <li>-</li> @endforelse
                        </ul>
                    </td>
                    <td class="swot-box swot-t">
                        <span class="swot-title" style="color: #856404;">AMEAÇAS (Externo -)</span>
                        <ul class="swot-list">
                            @forelse($swot->get('Ameaça', []) as $item) <li>{{ $item->dsc_item }}</li> @empty <li>-</li> @endforelse
                        </ul>
                    </td>
                </tr>
            </table>
        @endif
    </div>

    <!-- GRÁFICO DE DESEMPENHO (ANUNCIADO) -->
    <div class="section">
        <div class="section-title">Análise de Desempenho por Perspectiva</div>
        <p style="font-size: 8px; color: #666; margin-bottom: 5px; font-style: italic;">Visualização consolidada do atingimento médio dos indicadores por perspectiva BSC.</p>
        
        <div class="chart-container">
            @php $colWidth = 100 / max(count($dadosGrafico), 1); @endphp
            @foreach($dadosGrafico as $item)
                <div class="column-wrapper" style="width: {{ $colWidth }}%;">
                    <div class="column-value" style="color: {{ $item['cor'] }};">{{ number_format($item['valor'], 1) }}%</div>
                    <div class="column-bar" style="height: {{ min($item['valor'], 100) }}%; background-color: {{ $item['cor'] }};"></div>
                    <div class="column-label">{{ $item['label'] }}</div>
                </div>
            @endforeach
        </div>
        <div style="height: 40px; clear: both;"></div> {{-- Espaçador para as labels do gráfico --}}
    </div>

    <!-- 2. Performance dos Objetivos -->
    <div class="section">
        <div class="section-title">2. Desempenho dos Objetivos (Indicadores)</div>
        
        {{-- Legenda de Indicadores --}}
        <div class="legend-box" style="margin-bottom: 10px;">
            <strong style="font-size: 8px; color: #1B408E; text-transform: uppercase;">Legenda de Desempenho (Indicadores):</strong><br>
            @foreach($grausSatisfacao as $grau)
                <div class="legend-item">
                    <span class="legend-dot" style="background-color: {{ $grau->cor }};"></span>
                    {{ $grau->dsc_grau_satisfcao }} ({{ number_format($grau->vlr_minimo, 0) }}% a {{ number_format($grau->vlr_maximo, 0) }}%)
                </div>
            @endforeach
        </div>

        @foreach($perspectivas as $p)
            <div style="font-weight: bold; background: #1B408E; color: white; padding: 4px 10px; margin-top: 10px; font-size: 9px; border-radius: 3px 3px 0 0;">
                {{ $p->dsc_perspectiva }}
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 60%;">Objetivo</th>
                        <th style="width: 15%; text-align: center;">Indicadores</th>
                        <th style="width: 25%; text-align: center;">Atingimento Global</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($p->objetivos as $obj)
                        @php 
                            $atingimento = $obj->calcularAtingimentoConsolidado($ano, $mesLimite);
                            $cor = $getCorSatisfacao($atingimento);
                            $qtdInd = $obj->indicadores->count();
                        @endphp
                        <tr>
                            <td style="font-weight: bold;">{{ $obj->nom_objetivo }}</td>
                            <td style="text-align: center;">{{ $qtdInd }}</td>
                            <td>
                                <div style="display: block; width: 100%;">
                                    <div style="float: left; width: 70%;">
                                        <div class="progress-container">
                                            <div class="progress-bar" style="width: {{ min($atingimento, 100) }}%; background-color: {{ $cor }};"></div>
                                        </div>
                                    </div>
                                    <div style="float: right; width: 30%; text-align: right; font-weight: bold; color: {{ $cor }};">
                                        @brazil_percent($atingimento, 1)
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align: center; color: #999;">Sem objetivos vinculados nesta perspectiva.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endforeach
    </div>

    <div class="page-break"></div>

    <!-- 3. Status dos Planos de Ação -->
    <div class="section">
        <div class="section-title">3. Status de Execução dos Planos de Ação ({{ $ano }})</div>
        
        {{-- Legenda de Planos (Dinâmica) --}}
        <div class="legend-box" style="margin-bottom: 10px;">
            <strong style="font-size: 8px; color: #1B408E; text-transform: uppercase;">Legenda de Status (Planos de Ação):</strong><br>
            @foreach(\App\Models\ActionPlan\PlanoDeAcao::getStatusLegend() as $item)
                <div class="legend-item"><span class="legend-dot" style="background-color: {{ $item['color'] }};"></span> {{ $item['label'] }}</div>
            @endforeach
        </div>

        <table style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 20%;">Perspectiva / Objetivo</th>
                    <th style="width: 45%;">Plano de Ação</th>
                    <th style="width: 15%; text-align: center;">Status</th>
                    <th style="width: 20%; text-align: center;">Progresso (Entregas)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($planos as $plano)
                    @php 
                        $prog = $plano->calcularProgressoEntregas(); 
                        $perspectivaNome = $plano->objetivo?->perspectiva?->dsc_perspectiva ?? 'Não definida';
                        $objetivoNome = $plano->objetivo?->nom_objetivo ?? 'Não definido';
                        $corStatus = $plano->getSatisfacaoColor();
                        $textClass = ($corStatus == '#F3C72B') ? 'color: #000;' : 'color: #fff;';
                    @endphp
                    <tr>
                        <td style="font-size: 8px; background: #fcfcfc;">
                            <strong class="text-primary">{{ $perspectivaNome }}</strong><br>
                            <span class="text-muted">{{ $objetivoNome }}</span>
                        </td>
                        <td>
                            <div style="font-weight: bold;">{{ $plano->dsc_plano_de_acao }}</div>
                            <div style="font-size: 8px; color: #777;">Vigência: {{ $plano->dte_inicio?->format('d/m/Y') }} a {{ $plano->dte_fim?->format('d/m/Y') }}</div>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge" style="background-color: {{ $corStatus }}; {{ $textClass }}">{{ $plano->isAtrasado() ? 'Atrasado' : $plano->bln_status }}</span>
                        </td>
                        <td>
                            <div class="progress-container" style="height: 6px;">
                                <div class="progress-bar" style="width: {{ min($prog, 100) }}%; background-color: #429B22;"></div>
                            </div>
                            <div style="text-align: right; font-weight: bold; font-size: 8px; margin-top: 2px;">@brazil_percent($prog, 0)</div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align: center; color: #999;">Nenhum plano para o período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- 4. Panorama de Riscos -->
    <div class="section">
        <div class="section-title">4. Panorama de Riscos Estratégicos</div>
        
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 30%; vertical-align: top; border: none; padding-right: 20px;">
                    <div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px; text-align: center;">
                        <div style="font-size: 28px; font-weight: bold; color: #dc3545;">{{ $riscosSummary['Crítico'] ?? 0 }}</div>
                        <div style="text-transform: uppercase; font-weight: bold; color: #dc3545; font-size: 10px;">Riscos Críticos</div>
                        <p style="font-size: 7px; color: #777; margin-top: 10px;">Ações de mitigação imediatas são necessárias para estes itens.</p>
                    </div>
                </td>
                <td style="width: 70%; vertical-align: top; border: none;">
                    <p style="font-size: 9px; font-weight: bold; color: #666; margin-bottom: 10px;">DISTRIBUIÇÃO POR NÍVEL DE SEVERIDADE:</p>
                    @foreach(['Alto' => '#fd7e14', 'Médio' => '#ffc107', 'Baixo' => '#198754'] as $nivel => $corRisco)
                        @php $totalNivel = $riscosSummary[$nivel] ?? 0; @endphp
                        <div style="margin-bottom: 12px;">
                            <div style="display: block; width: 100%; font-size: 9px; margin-bottom: 3px;">
                                <span style="float: left; font-weight: bold;">{{ $nivel }}</span>
                                <span style="float: right;">{{ $totalNivel }} risco(s)</span>
                            </div>
                            <div style="clear: both;"></div>
                            <div class="progress-container" style="height: 8px;">
                                <div class="progress-bar" style="width: {{ min(($totalNivel) * 10, 100) }}%; background: {{ $corRisco }};"></div>
                            </div>
                        </div>
                    @endforeach
                </td>
            </tr>
        </table>

        {{-- Tabela Detalhada de Riscos (NOVO) --}}
        <div style="margin-top: 20px;">
            <p style="font-size: 9px; font-weight: bold; color: #666; margin-bottom: 10px;">DETALHAMENTO DOS RISCOS IDENTIFICADOS:</p>
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 10%; text-align: center;">Código</th>
                        <th style="width: 45%;">Título do Risco</th>
                        <th style="width: 15%;">Categoria</th>
                        <th style="width: 15%; text-align: center;">Nível (P x I)</th>
                        <th style="width: 15%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riscosDetalhado as $risco)
                        @php
                            $nivelRisco = $risco->num_nivel_risco;
                            $corNivel = $risco->getNivelRiscoCor();
                            $labelNivel = $risco->getNivelRiscoLabel();
                        @endphp
                        <tr>
                            <td style="text-align: center; font-family: monospace;">R-{{ str_pad($risco->num_codigo_risco, 3, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="font-weight: bold;">{{ $risco->dsc_titulo }}</div>
                            </td>
                            <td>{{ $risco->dsc_categoria }}</td>
                            <td style="text-align: center;">
                                <span style="color: {{ $corNivel }}; font-weight: bold;">{{ $nivelRisco }}</span><br>
                                <small style="font-size: 7px;">({{ $labelNivel }})</small>
                            </td>
                            <td style="text-align: center;">{{ $risco->dsc_status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align: center; color: #999;">Nenhum risco registrado para esta unidade.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        Relatório Gerencial Strategic Planning System | Gerado em {{ now()->format('d/m/Y H:i') }} | Página {{ isset($pdf) ? $pdf->get_canvas()->get_page_number() : '3' }}
    </div>
</body>
</html>