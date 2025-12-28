<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Executivo Consolidado - SEAE</title>
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
        
        /* Grid / Colunas (Simulado com tabelas para DomPDF) */
        .row { width: 100%; display: table; table-layout: fixed; border-spacing: 10px 0; }
        .col { display: table-cell; vertical-align: top; }
        
        /* Tabelas */
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #dee2e6; padding: 6px 8px; text-align: left; }
        th { background: #f8f9fa; font-size: 9px; color: #1B408E; text-transform: uppercase; font-weight: bold; }
        
        /* Matriz SWOT */
        .swot-grid { width: 100%; border-collapse: separate; border-spacing: 5px; }
        .swot-box { width: 50%; height: 100px; padding: 10px; vertical-align: top; border-radius: 5px; }
        .swot-s { background-color: #d1e7dd; border: 1px solid #a3cfbb; } /* Forças */
        .swot-w { background-color: #f8d7da; border: 1px solid #f1aeb5; } /* Fraquezas */
        .swot-o { background-color: #cfe2ff; border: 1px solid #9ec5fe; } /* Oportunidades */
        .swot-t { background-color: #fff3cd; border: 1px solid #ffe69c; } /* Ameaças */
        .swot-title { font-weight: bold; font-size: 11px; margin-bottom: 5px; display: block; }
        .swot-list { margin: 0; padding-left: 15px; font-size: 9px; }

        /* Gráficos / Barras de Progresso */
        .progress-container { background: #eee; border-radius: 10px; height: 10px; width: 100%; position: relative; margin-top: 4px; }
        .progress-bar { background: #1B408E; height: 100%; border-radius: 10px; }
        .progress-text { font-size: 9px; font-weight: bold; margin-left: 5px; }
        
        /* Status Badges */
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; text-transform: uppercase; color: white; }
        .bg-success { background-color: #198754; }
        .bg-warning { background-color: #ffc107; color: #000; }
        .bg-danger { background-color: #dc3545; }
        .bg-info { background-color: #0dcaf0; color: #000; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <div class="header">
        <h1 class="text-primary">Relatório Executivo de Gestão Estratégica</h1>
        <div class="org-name">{{ $organizacao->nom_organizacao }}</div>
        
        @if(isset($filtros))
            <div style="margin-top: 10px; font-size: 9px; color: #555;">
                <strong>Ano:</strong> {{ $filtros['ano'] }} | 
                <strong>Período:</strong> {{ $filtros['periodo'] }} | 
                <strong>Filtro Perspectiva:</strong> {{ $filtros['perspectiva'] }}
            </div>
        @endif
        <div style="font-size: 8px; color: #999; margin-top: 5px;">Gerado em {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <!-- 1. Identidade e Diagnóstico -->
    <div class="section">
        <div class="section-title">1. Identidade e Diagnóstico Estratégico</div>
        <table style="border: none;">
            <tr>
                <td style="width: 50%; border: none; padding: 0 10px 0 0;">
                    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; border: 1px solid #eee; height: 100px;">
                        <strong class="text-primary">MISSÃO</strong><br>
                        <p style="font-style: italic; margin-top: 5px;">"{{ $identidade->dsc_missao ?: 'Não definida.' }}"</p>
                    </div>
                </td>
                <td style="width: 50%; border: none; padding: 0 0 0 10px;">
                    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; border: 1px solid #eee; height: 100px;">
                        <strong class="text-primary">VISÃO</strong><br>
                        <p style="font-style: italic; margin-top: 5px;">"{{ $identidade->dsc_visao ?: 'Não definida.' }}"</p>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Matriz SWOT -->
        <div style="margin-top: 15px; font-weight: bold; color: #666; margin-bottom: 5px;">Diagnóstico de Ambiente (SWOT):</div>
        <table class="swot-grid">
            <tr>
                <td class="swot-box swot-s">
                    <span class="swot-title text-success">FORÇAS (Interno +)</span>
                    <ul class="swot-list">
                        @forelse($swot->get('Força', []) as $item) <li>{{ $item->dsc_item }}</li> @empty <li>Nenhuma registrada.</li> @endforelse
                    </ul>
                </td>
                <td class="swot-box swot-w">
                    <span class="swot-title text-danger">FRAQUEZAS (Interno -)</span>
                    <ul class="swot-list">
                        @forelse($swot->get('Fraqueza', []) as $item) <li>{{ $item->dsc_item }}</li> @empty <li>Nenhuma registrada.</li> @endforelse
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="swot-box swot-o">
                    <span class="swot-title text-primary">OPORTUNIDADES (Externo +)</span>
                    <ul class="swot-list">
                        @forelse($swot->get('Oportunidade', []) as $item) <li>{{ $item->dsc_item }}</li> @empty <li>Nenhuma registrada.</li> @endforelse
                    </ul>
                </td>
                <td class="swot-box swot-t">
                    <span class="swot-title" style="color: #856404;">AMEAÇAS (Externo -)</span>
                    <ul class="swot-list">
                        @forelse($swot->get('Ameaça', []) as $item) <li>{{ $item->dsc_item }}</li> @empty <li>Nenhuma registrada.</li> @endforelse
                    </ul>
                </td>
            </tr>
        </table>
    </div>

    <!-- 2. Performance dos Objetivos -->
    <div class="section">
        <div class="section-title">2. Desempenho dos Objetivos Estratégicos (BSC)</div>
        @foreach($perspectivas as $p)
            <div style="font-weight: bold; background: #1B408E; color: white; padding: 4px 10px; margin-top: 10px; font-size: 9px;">
                {{ $p->dsc_perspectiva }}
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 60%;">Objetivo Estratégico</th>
                        <th style="width: 15%; text-align: center;">Indicadores</th>
                        <th style="width: 25%; text-align: center;">Atingimento Global</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($p->objetivos as $obj)
                        @php 
                            $soma = 0; $cont = 0;
                            foreach($obj->indicadores as $ind) { $soma += $ind->calcularAtingimento(); $cont++; }
                            $atingimento = $cont > 0 ? $soma / $cont : 0;
                            $cor = $atingimento >= 100 ? '#198754' : ($atingimento >= 80 ? '#ffc107' : '#dc3545');
                        @endphp
                        <tr>
                            <td style="font-weight: bold;">{{ $obj->nom_objetivo_estrategico }}</td>
                            <td style="text-align: center;">{{ $cont }}</td>
                            <td>
                                <div style="display: table; width: 100%;">
                                    <div style="display: table-cell; width: 70%;">
                                        <div class="progress-container">
                                            <div class="progress-bar" style="width: {{ min($atingimento, 100) }}%; background-color: {{ $cor }};"></div>
                                        </div>
                                    </div>
                                    <div style="display: table-cell; width: 30%; text-align: right; font-weight: bold; color: {{ $cor }};">
                                        {{ number_format($atingimento, 1) }}%
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

    <!-- 3. Planos de Ação e Riscos -->
    <div class="row">
        <!-- Coluna Planos -->
        <div class="col" style="width: 65%;">
            <div class="section-title">3. Status dos Planos de Ação</div>
            <table>
                <thead>
                    <tr>
                        <th>Plano de Ação</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Progresso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($planos->take(15) as $plano)
                        @php $prog = $plano->calcularProgressoEntregas(); @endphp
                        <tr>
                            <td>
                                <div style="font-weight: bold;">{{ $plano->dsc_plano_de_acao }}</div>
                                <div style="font-size: 8px; color: #777;">Fim: {{ $plano->dte_fim?->format('d/m/Y') }}</div>
                            </td>
                            <td style="text-align: center;">
                                @php
                                    $bClass = match($plano->bln_status) {
                                        'Concluído' => 'bg-success',
                                        'Em Andamento' => 'bg-info',
                                        'Atrasado' => 'bg-danger',
                                        default => 'bg-warning'
                                    };
                                @endphp
                                <span class="badge {{ $bClass }}">{{ $plano->bln_status }}</span>
                            </td>
                            <td style="text-align: center; font-weight: bold;">{{ number_format($prog, 0) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($planos->count() > 15)
                <div style="text-align: center; font-style: italic; font-size: 8px; color: #999;">
                    Exibindo 15 de {{ $planos->count() }} planos. Consulte o relatório específico para a lista completa.
                </div>
            @endif
        </div>

        <!-- Coluna Riscos -->
        <div class="col" style="width: 35%; padding-left: 15px;">
            <div class="section-title">4. Panorama de Riscos</div>
            <div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px;">
                <div style="text-align: center; margin-bottom: 15px;">
                    <div style="font-size: 24px; font-weight: bold; color: #dc3545;">{{ $riscosSummary['Crítico'] ?? 0 }}</div>
                    <div style="text-transform: uppercase; font-weight: bold; color: #dc3545;">Riscos Críticos</div>
                </div>
                
                <div style="margin-bottom: 8px;">
                    <div style="display: table; width: 100%;">
                        <div style="display: table-cell; width: 50%;">Alto:</div>
                        <div style="display: table-cell; width: 50%; text-align: right; font-weight: bold;">{{ $riscosSummary['Alto'] ?? 0 }}</div>
                    </div>
                    <div class="progress-container" style="height: 6px;"><div class="progress-bar" style="width: {{ min(($riscosSummary['Alto'] ?? 0) * 10, 100) }}%; background: #fd7e14;"></div></div>
                </div>

                <div style="margin-bottom: 8px;">
                    <div style="display: table; width: 100%;">
                        <div style="display: table-cell; width: 50%;">Médio:</div>
                        <div style="display: table-cell; width: 50%; text-align: right; font-weight: bold;">{{ $riscosSummary['Médio'] ?? 0 }}</div>
                    </div>
                    <div class="progress-container" style="height: 6px;"><div class="progress-bar" style="width: {{ min(($riscosSummary['Médio'] ?? 0) * 10, 100) }}%; background: #ffc107;"></div></div>
                </div>

                <div>
                    <div style="display: table; width: 100%;">
                        <div style="display: table-cell; width: 50%;">Baixo:</div>
                        <div style="display: table-cell; width: 50%; text-align: right; font-weight: bold;">{{ $riscosSummary['Baixo'] ?? 0 }}</div>
                    </div>
                    <div class="progress-container" style="height: 6px;"><div class="progress-bar" style="width: {{ min(($riscosSummary['Baixo'] ?? 0) * 10, 100) }}%; background: #198754;"></div></div>
                </div>

                <div style="margin-top: 20px; font-size: 8px; color: #777; border-top: 1px solid #ddd; padding-top: 10px;">
                    * O nível de risco é calculado multiplicando Probabilidade x Impacto.
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Relatório Gerencial SEAE - Sistema de Apoio à Estratégia | Página 1/2
    </div>
</body>
</html>