<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dossiê Estratégico Integrado</title>
    <style>
        @page { margin: 1cm; margin-bottom: 1.5cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; line-height: 1.3; margin: 0; padding: 0; }
        
        /* Cores */
        .text-primary { color: #1B408E; }
        .bg-primary { background-color: #1B408E; color: white; }
        .bg-light { background-color: #f8f9fa; }
        
        /* Capa */
        .cover-page { 
            position: relative; 
            height: 950px;
            text-align: center; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            page-break-after: always;
        }
        .cover-title { font-size: 36px; font-weight: bold; color: #1B408E; margin-bottom: 5px; text-transform: uppercase; }
        .cover-subtitle { font-size: 20px; color: #555; margin-top: 5px; margin-bottom: 60px; }
        .cover-meta { font-size: 11px; color: #777; margin-top: 100px; border-top: 1px solid #eee; padding-top: 20px; display: inline-block; width: 60%; }

        /* Mapa Estratégico */
        .map-header-row { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .map-card { 
            border: 1px dashed #ccc; 
            padding: 10px; 
            background: #ffffff; 
            font-size: 10px; 
            text-align: center; 
            border-radius: 6px; 
            height: auto;
            display: block;
            box-sizing: border-box;
        }
        
        .map-container { margin-top: 20px; }
        .persp-row { margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; page-break-inside: avoid; }
        .persp-header { 
            background: #1B408E; color: white; 
            padding: 8px; text-align: center; 
            font-weight: bold; text-transform: uppercase; font-size: 11px; 
            letter-spacing: 1px;
        }
        .persp-body { padding: 10px; background: #fdfdfd; text-align: center; }
        
        .obj-card {
            display: inline-block;
            width: 28%;
            vertical-align: top;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 8px;
            margin: 5px;
            text-align: left;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.05);
            position: relative;
            min-height: 60px;
        }
        .obj-title { font-weight: bold; font-size: 9px; color: #333; margin-bottom: 5px; display: block; }
        .obj-status { position: absolute; top: 0; right: 0; width: 10px; height: 10px; border-radius: 0 6px 0 6px; }

        /* Tabelas */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; page-break-inside: auto; }
        tr { page-break-inside: avoid; }
        th, td { border: 1px solid #e0e0e0; padding: 6px 8px; text-align: left; vertical-align: middle; }
        th { background: #f1f5f9; color: #1B408E; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        
        /* Badges e Status */
        .badge { padding: 3px 8px; border-radius: 10px; font-size: 8px; font-weight: bold; display: inline-block; text-align: center; white-space: nowrap; }
        
        /* Seções */
        .chapter-title { 
            font-size: 18px; color: #1B408E; 
            border-bottom: 2px solid #1B408E; 
            padding-bottom: 8px; margin: 25px 0 15px 0; 
            text-transform: uppercase; font-weight: bold;
        }
        .sub-title { 
            background: #eef2f7; color: #2c3e50; 
            padding: 5px 10px; font-weight: bold; font-size: 10px; 
            border-left: 4px solid #1B408E; margin: 15px 0 10px 0; 
        }

        /* Legenda Semáforo */
        .legend-container { margin-bottom: 15px; background: #fff; padding: 10px; border: 1px solid #eee; border-radius: 5px; }
        .legend-title { font-weight: bold; font-size: 9px; margin-bottom: 5px; color: #666; text-transform: uppercase; }
        .legend-item { display: inline-block; margin-right: 15px; font-size: 8px; }
        .legend-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 5px; vertical-align: middle; }

        /* Entregas */
        .deliverables-table { width: 95%; margin: 5px auto 10px auto; border: 1px solid #eee; }
        .deliverables-table th { background: #fff; color: #666; font-size: 8px; border-bottom: 1px solid #eee; }
        .deliverables-table td { font-size: 8px; border-bottom: 1px solid #eee; background: #fff; }

        /* Rodapé de Página */
        footer { position: fixed; bottom: -30px; left: 0; right: 0; height: 30px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }

        /* Utilitários */
        .page-break { page-break-after: always; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
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
    @endphp

    <!-- CAPA (Página 1) -->
    <div class="cover-page">
        <div class="cover-subtitle">{{ $organizacao->nom_organizacao }}</div>
        
        <div class="cover-title">Dossiê Estratégico Integrado</div>
        
        
        <div class="cover-meta">
            <p><strong>CICLO ESTRATÉGICO:</strong> {{ $filtros['ano'] }}</p>
            <p><strong>PERÍODO ANALISADO:</strong> {{ $filtros['periodo'] }}</p>
            <p><strong>DATA DE GERAÇÃO:</strong> {{ now()->format('d/m/Y \à\s H:i') }}</p>
        </div>
    </div>

    <footer>
        {{ $organizacao->sgl_organizacao }} - Dossiê Estratégico | Página <span class="pagenum"></span>
    </footer>

    <!-- MAPA ESTRATÉGICO (Página 2) -->
    <div class="chapter-title" style="margin-top: 0;">1. Mapa Estratégico</div>
    
    <!-- Identidade Separada (Cards - Altura Igual e Cantos Arredondados) -->
    <table style="width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 15px; table-layout: fixed; border: none;" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <!-- Card Missão -->
            <td style="width: 49%; background: #ffffff; border-left: 4px solid #1B408E; border-top: 1px solid #eeeeee; border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; border-radius: 6px; padding: 15px; vertical-align: top;">
                <strong style="color: #1B408E; margin-bottom: 12px; display: block; font-size: 9px;">MISSÃO</strong>
                <div style="font-style: italic; font-size: 10px; line-height: 1.4;">{{ $identidade->dsc_missao ?? 'Não definida' }}</div>
            </td>
            
            <!-- Espaçador Central (Invisível) -->
            <td style="width: 2%; border: none; background: none;"></td>
            
            <!-- Card Visão -->
            <td style="width: 49%; background: #ffffff; border-left: 4px solid #1B408E; border-top: 1px solid #eeeeee; border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; border-radius: 6px; padding: 15px; vertical-align: top;">
                <strong style="color: #1B408E; margin-bottom: 12px; display: block; font-size: 9px;">VISÃO</strong>
                <div style="font-style: italic; font-size: 10px; line-height: 1.4;">{{ $identidade->dsc_visao ?? 'Não definida' }}</div>
            </td>
        </tr>
    </table>

    <!-- Valores (Card Dedicado - Arredondado) -->
    <table style="width: 100%; margin-bottom: 15px; border-collapse: separate; border-spacing: 0; border: none;" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td style="background: #ffffff; border-left: 4px solid #1B408E; border-top: 1px solid #eeeeee; border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; border-radius: 6px; padding: 15px; vertical-align: top; text-align: center;">
                <strong style="color: #1B408E; margin-bottom: 25px; padding-bottom: 5px; display: block; font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">VALORES INSTITUCIONAIS</strong>
                <div style="text-align: center; margin-top: 10px;">
                    @forelse($valores as $valor)
                        <span class="badge" style="background: #f1f4f9; color: #1B408E; margin: 4px 8px; font-weight: bold; border: 1px solid #1B408E; padding: 5px 12px; font-size: 9px;">
                            {{ $valor->nom_valor }}
                        </span>
                    @empty
                        <span style="font-style: italic; color: #999; font-size: 8px;">Valores não cadastrados.</span>
                    @endforelse
                </div>
            </td>
        </tr>
    </table>

    <!-- Temas Norteadores (Card Dedicado - Arredondado) -->
    @if($temasNorteadores->isNotEmpty())
        <table style="width: 100%; margin-bottom: 25px; border-collapse: separate; border-spacing: 0; border: none;" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td style="background: #ffffff; border-left: 4px solid #1B408E; border-top: 1px solid #eeeeee; border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; border-radius: 6px; padding: 15px; vertical-align: top; text-align: center;">
                    <strong style="color: #1B408E; margin-bottom: 25px; padding-bottom: 5px; display: block; font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">TEMAS NORTEADORES</strong>
                    <div style="line-height: 2.2; text-align: center; margin-top: 10px;">
                        @foreach($temasNorteadores as $objEst)
                            <span class="badge" style="background: #fff; color: #1B408E; margin: 4px 8px; font-weight: bold; border: 1px solid #1B408E; padding: 5px 12px; font-size: 9px;">
                                {{ $objEst->nom_tema_norteador }}
                            </span>
                        @endforeach
                    </div>
                </td>
            </tr>
        </table>
    @endif

    <!-- Swimlanes do Mapa -->
    <div class="map-container">
        @php
            // Mapeamento de Cores por Nível (Cientificamente extraído do MapaEstrategico.php)
            $coresNivel = [
                1 => '#6c757d', // Secondary/Slate
                2 => '#198754', // Success
                3 => '#0dcaf0', // Info
                4 => '#ffc107', // Warning
                5 => '#0d6efd', // Primary
            ];
        @endphp

        @foreach($perspectivas as $persp)
            @php
                $corPersp = $coresNivel[$persp->num_nivel_hierarquico_apresentacao] ?? '#1B408E';
                // Ajuste de contraste para texto (Preto para Warning/Amarelo, Branco para resto)
                $textoCor = ($persp->num_nivel_hierarquico_apresentacao == 4) ? '#000' : '#fff';
            @endphp
            <div class="persp-row" style="border-color: {{ $corPersp }};">
                <div class="persp-header" style="background: {{ $corPersp }}; color: {{ $textoCor }};">{{ $persp->dsc_perspectiva }}</div>
                <div class="persp-body">
                    @forelse($persp->objetivos as $obj)
                        @php
                            $atingimento = $obj->calcularAtingimentoConsolidado($filtros['ano'], $filtros['mesLimite']);
                            $cor = $getCorSatisfacao($atingimento);
                        @endphp
                        <div class="obj-card" style="border-left: 3px solid {{ $cor }};">
                            <div class="obj-status" style="background: {{ $cor }};"></div>
                            <span class="obj-title">{{ $obj->nom_objetivo }}</span>
                            <div style="font-size: 8px; color: #666; margin-top: 4px;">
                                Atingimento: <strong>{{ number_format($atingimento, 2, ',', '.') }}%</strong>
                            </div>
                        </div>
                    @empty
                        <div style="font-style: italic; color: #999; padding: 10px;">Sem objetivos vinculados.</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <!-- IA Summary (Se habilitado) -->
    @if($aiSummary)
        <div style="margin-top: 20px; background: #f0f7ff; border-left: 4px solid #0d6efd; padding: 10px;">
            <strong style="color: #0d6efd; font-size: 9px;">INSIGHT ESTRATÉGICO (IA):</strong>
            <p style="margin: 5px 0 0 0; font-style: italic; font-size: 9px;">{!! nl2br(e($aiSummary)) !!}</p>
        </div>
    @endif

    <div class="page-break"></div>

    <!-- MONITORAMENTO DE INDICADORES (KPIs) -->
    <div class="chapter-title">2. Monitoramento de Indicadores (KPIs)</div>

    <!-- Legenda Semáforo (Indicadores) -->
    <div class="legend-container">
        <div class="legend-title">Parâmetros de Análise (Grau de Satisfação):</div>
        <div style="margin-bottom: 8px;">
            @foreach($grausSatisfacao as $grau)
                <div class="legend-item">
                    <span class="legend-dot" style="background-color: {{ $grau->cor }};"></span>
                    <strong>{{ $grau->dsc_grau_satisfcao }}</strong>
                    <span style="color: #777;">({{ number_format($grau->vlr_minimo, 2, ',', '.') }}% a {{ number_format($grau->vlr_maximo, 2, ',', '.') }}%)</span>
                </div>
            @endforeach
        </div>
        <div style="border-top: 1px dashed #eee; padding-top: 5px; font-size: 7px; color: #666; font-style: italic;">
            * O cálculo de atingimento considera a <strong>polaridade</strong> de cada indicador (Ex: Negativa para custos/atrasos). Indicadores puramente informativos (Não Aplicáveis) são desconsiderados na média global.
        </div>
    </div>
    
    @foreach($perspectivas as $persp)
        @php 
            $objsComInd = $persp->objetivos->filter(fn($o) => $o->indicadores->count() > 0);
        @endphp

        @if($objsComInd->isNotEmpty())
            <div class="sub-title">{{ $persp->dsc_perspectiva }}</div>
            
            @foreach($objsComInd as $obj)
                <div style="margin-bottom: 5px; font-weight: bold; font-size: 9px; color: #555; padding-left: 5px;">
                    Objetivo: {{ $obj->nom_objetivo }}
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 35%;">Indicador</th>
                            <th style="width: 10%; text-align: center;">UN</th>
                            <th style="width: 15%; text-align: right;">Meta (Ano)</th>
                            <th style="width: 15%; text-align: right;">Realizado</th>
                            <th style="width: 10%; text-align: right;">% Ating.</th>
                            <th style="width: 15%; text-align: center;">Grau Satisfação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($obj->indicadores as $ind)
                            @php
                                $perc = $ind->calcularAtingimento();
                                $cor = $ind->getCorFarol();
                                $textoCor = ($cor == '#F3C72B') ? '#000' : '#fff';
                                $ultimaEv = $ind->getUltimaEvolucao();
                            @endphp
                            <tr>
                                <td>{{ $ind->nom_indicador }}</td>
                                <td class="text-center">{{ $ind->dsc_unidade_medida }}</td>
                                <td class="text-right">{{ $ind->dsc_meta }}</td>
                                <td class="text-right">{{ $ultimaEv ? number_format($ultimaEv->vlr_realizado, 2, ',', '.') : '--' }}</td>
                                <td class="text-right bold">
                                    @if($ind->dsc_polaridade === 'Não Aplicável')
                                        --
                                    @else
                                        {{ number_format($perc, 2, ',', '.') }}%
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($ind->dsc_polaridade === 'Não Aplicável')
                                        <span class="badge" style="background: #eee; color: #666; border: 1px solid #ccc;">INFO</span>
                                    @else
                                        <span class="badge" style="background: {{ $cor }}; color: {{ $textoCor }};">
                                            {{ number_format($perc, 2, ',', '.') }}%
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endif
    @endforeach

    <div class="page-break"></div>

    <!-- PORTFÓLIO DE AÇÕES E ENTREGAS -->
    <div class="chapter-title">3. Portfólio de Planos e Entregas</div>

    <!-- Legenda Específica (Entregas) -->
    <div class="legend-container">
        <div class="legend-title">Status de Entregas:</div>
        <div class="legend-item"><span class="legend-dot" style="background: #dbeddb;"></span><strong>Concluído</strong></div>
        <div class="legend-item"><span class="legend-dot" style="background: #fdecc8;"></span><strong>Em Andamento</strong></div>
        <div class="legend-item"><span class="legend-dot" style="background: #ffe2dd;"></span><strong>Atrasado / Cancelado</strong></div>
        <div class="legend-item"><span class="legend-dot" style="background: #e3e2e0;"></span><strong>Não Iniciado</strong></div>
    </div>

    @forelse($planos as $plano)
        @php
            $corStatus = $plano->getSatisfacaoColor();
            $textoClass = $plano->getSatisfacaoTextClass() == 'text-dark' ? 'color: #000' : 'color: #fff';
        @endphp
        
        <div style="border: 1px solid #ccc; border-radius: 5px; margin-bottom: 20px; page-break-inside: avoid;">
            <!-- Cabeçalho do Plano -->
            <div style="background: #f1f1f1; padding: 8px; border-bottom: 1px solid #ddd;">
                <table style="width: 100%; margin: 0; border: none;">
                    <tr style="border: none;">
                        <td style="border: none; width: 70%;">
                            <strong style="color: #1B408E; font-size: 10px;">{{ $plano->dsc_plano_de_acao }}</strong>
                            <div style="font-size: 8px; color: #666; margin-top: 2px;">
                                Início: {{ $plano->dte_inicio?->format('d/m/Y') }} | Fim: {{ $plano->dte_fim?->format('d/m/Y') }}
                            </div>
                        </td>
                        <td style="border: none; width: 30%; text-align: right;">
                            <span class="badge" style="background: {{ $corStatus }}; {{ $textoClass }}; font-size: 9px;">
                                {{ $plano->bln_status }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Entregas do Plano -->
            @if($plano->entregas->isNotEmpty())
                <div style="padding: 5px 10px;">
                    <strong style="font-size: 8px; color: #666; text-transform: uppercase;">Entregas Previstas:</strong>
                    <table class="deliverables-table">
                        <thead>
                            <tr>
                                <th style="width: 45%;">Descrição da Entrega</th>
                                <th style="width: 20%;">Responsável</th>
                                <th style="width: 15%;">Prazo</th>
                                <th style="width: 20%; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plano->entregas as $entrega)
                                <tr>
                                    <!-- CORRIGIDO: dsc_entrega em vez de nom_entrega -->
                                    <td>{{ $entrega->dsc_entrega }}</td>
                                    <td>
                                        @if($entrega->responsaveis->isNotEmpty())
                                            {{ Str::limit($entrega->responsaveis->first()->name, 15) }}
                                            @if($entrega->responsaveis->count() > 1) <span style="font-size: 7px; color: #999;">(+{{ $entrega->responsaveis->count()-1 }})</span> @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <!-- CORRIGIDO: dte_prazo em vez de dte_fim -->
                                    <td>{{ $entrega->dte_prazo?->format('d/m/y') ?? '-' }}</td>
                                    <td class="text-center">
                                        @php
                                            $corEntrega = match($entrega->bln_status) {
                                                'Concluído' => '#dbeddb', // Verde Notion
                                                'Em Andamento' => '#fdecc8', // Amarelo Notion
                                                'Atrasado' => '#ffe2dd', // Vermelho Notion
                                                'Cancelado' => '#ffe2dd',
                                                default => '#e3e2e0' // Cinza Notion
                                            };
                                            $textoEntrega = '#37352f'; // Texto escuro padrão Notion
                                        @endphp
                                        <span style="padding: 2px 5px; border-radius: 3px; background: {{ $corEntrega }}; color: {{ $textoEntrega }}; font-size: 7px; font-weight: bold;">
                                            {{ $entrega->bln_status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="padding: 10px; font-size: 8px; color: #999; text-align: center;">Nenhuma entrega cadastrada para este plano.</div>
            @endif
        </div>
    @empty
        <div style="padding: 20px; text-align: center; color: #999;">Nenhum plano de ação encontrado para o período.</div>
    @endforelse

    <div class="page-break"></div>

    <!-- GESTÃO DE RISCOS -->
    <div class="chapter-title">4. Gestão de Riscos</div>
    
    <table style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 10%; text-align: center;">Nível</th>
                <th style="width: 40%;">Risco</th>
                <th style="width: 20%;">Categoria</th>
                <th style="width: 30%;">Ação de Mitigação</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riscosDetalhado as $risco)
                <tr>
                    <td class="text-center" style="font-weight: bold; color: {{ $risco->getNivelRiscoCor() }}">
                        {{ $risco->num_nivel_risco }}
                    </td>
                    <td>{{ $risco->dsc_titulo }}</td>
                    <td>{{ $risco->dsc_categoria }}</td>
                    <td>
                        @if($risco->mitigacoes->isNotEmpty())
                            {{ Str::limit($risco->mitigacoes->first()->dsc_acao_mitigacao, 60) }}
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center" style="color: #999;">Nenhum risco monitorado.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- SWOT (CONDICIONAL) -->
    @if($swot->isNotEmpty())
        <div class="page-break"></div>
        <div class="chapter-title">Anexo: Análise de Ambiente (SWOT)</div>
        
        <table style="border: 1px solid #ddd;">
            <tr>
                <td style="width: 50%; background: #e8f5e9; vertical-align: top;">
                    <strong style="color: #198754;">FORÇAS</strong>
                    <ul style="margin: 5px 0 0 0; padding-left: 15px; font-size: 9px;">
                        @forelse($swot->get('Força', []) as $item) <li>{{ $item->dsc_item }}</li> @empty <li style="list-style: none;">-</li> @endforelse
                    </ul>
                </td>
                <td style="width: 50%; background: #fbecec; vertical-align: top;">
                    <strong style="color: #dc3545;">FRAQUEZAS</strong>
                    <ul style="margin: 5px 0 0 0; padding-left: 15px; font-size: 9px;">
                        @forelse($swot->get('Fraqueza', []) as $item) <li>{{ $item->dsc_item }}</li> @empty <li style="list-style: none;">-</li> @endforelse
                    </ul>
                </td>
            </tr>
            <tr>
                <td style="width: 50%; background: #e7f1ff; vertical-align: top;">
                    <strong style="color: #0d6efd;">OPORTUNIDADES</strong>
                    <ul style="margin: 5px 0 0 0; padding-left: 15px; font-size: 9px;">
                        @forelse($swot->get('Oportunidade', []) as $item) <li>{{ $item->dsc_item }}</li> @empty <li style="list-style: none;">-</li> @endforelse
                    </ul>
                </td>
                <td style="width: 50%; background: #fff8e1; vertical-align: top;">
                    <strong style="color: #d39e00;">AMEAÇAS</strong>
                    <ul style="margin: 5px 0 0 0; padding-left: 15px; font-size: 9px;">
                        @forelse($swot->get('Ameaça', []) as $item) <li>{{ $item->dsc_item }}</li> @empty <li style="list-style: none;">-</li> @endforelse
                    </ul>
                </td>
            </tr>
        </table>
    @endif

</body>
</html>