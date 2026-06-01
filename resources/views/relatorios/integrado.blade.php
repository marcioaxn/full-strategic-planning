<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dossiê Estratégico Integrado</title>
    @include('relatorios.partials.estilos')
    <style>
        /* Capa */
        .capa { text-align: center; padding-top: 120px; page-break-after: always; }
        .capa-org { font-size: 15px; color: #4a5568; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        .capa-titulo { font-size: 38px; font-weight: bold; color: #1a3a5c; margin: 14px 0 6px 0; letter-spacing: -1px; }
        .capa-linha { width: 90px; height: 4px; background: #e07b39; margin: 18px auto 30px auto; border-radius: 2px; }
        .capa-sub { font-size: 13px; color: #718096; }
        .capa-meta { margin-top: 60px; display: inline-block; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 36px; background: #f7fafc; font-size: 10px; color: #4a5568; line-height: 1.9; }
        .capa-meta strong { color: #1a3a5c; }
        .capa-modulos { margin-top: 40px; }
        .capa-mod { display: inline-block; margin: 0 6px; padding: 6px 14px; border-radius: 999px; font-size: 8px; font-weight: bold; color: #fff; }

        /* Capítulo */
        .cap { font-size: 9px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; color: #e07b39; margin-top: 6px; }
        .cap-titulo { font-size: 19px; font-weight: bold; color: #1a3a5c; margin: 2px 0 4px 0; border-bottom: 2px solid #1a3a5c; padding-bottom: 8px; }

        /* Cards identidade */
        .id-card { background: #fff; border: 1px solid #e2e8f0; border-left: 4px solid #1B408E; border-radius: 8px; padding: 14px; vertical-align: top; }
        .id-label { color: #1B408E; font-weight: bold; font-size: 9px; text-transform: uppercase; letter-spacing: .5px; display: block; margin-bottom: 6px; }
        .id-text { font-style: italic; font-size: 10px; line-height: 1.5; color: #2d3748; }
        .chip { display: inline-block; background: #eef2f9; color: #1B408E; border: 1px solid #c7d6ec; border-radius: 999px; padding: 4px 12px; margin: 3px; font-size: 8.5px; font-weight: bold; }

        /* Swimlanes mapa */
        .persp-row { margin-bottom: 12px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; page-break-inside: avoid; }
        .persp-header { color: #fff; padding: 7px 12px; font-weight: bold; text-transform: uppercase; font-size: 10px; letter-spacing: .5px; }
        .persp-body { padding: 8px; background: #fdfdfd; }
        .obj-card { display: inline-block; width: 30%; vertical-align: top; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; margin: 4px; min-height: 50px; }
        .obj-title { font-weight: bold; font-size: 8.5px; color: #2d3748; display: block; }

        /* SWOT quadrantes */
        .swot-cell { width: 50%; vertical-align: top; padding: 10px; border-radius: 6px; }
        .swot-cell strong { font-size: 10px; }
        .swot-cell ul { margin: 6px 0 0 0; padding-left: 16px; font-size: 8.5px; line-height: 1.5; }

        .ai-box { background: #f0f7ff; border-left: 4px solid #1B408E; border-radius: 6px; padding: 12px 14px; margin-top: 14px; }
        .ai-box strong { color: #1B408E; font-size: 9px; }
        .ai-box p { margin: 5px 0 0 0; font-style: italic; font-size: 9px; line-height: 1.5; }
    </style>
</head>
<body>
    @include('relatorios.partials.cabecalho', [
        'rptTitulo'    => 'Dossiê Estratégico Integrado',
        'rptEyebrow'   => 'Documento Consolidado · GPPEI/MGI 2025',
        'rptSubtitulo' => $organizacao->nom_organizacao . ' · Exercício ' . $filtros['ano'],
        'rptIcon'      => '&#9733;',
    ])
    @include('relatorios.partials.rodape')

    @php
        $getCorSatisfacao = function($percentual) use ($grausSatisfacao) {
            foreach ($grausSatisfacao as $grau) {
                if ($percentual >= $grau->vlr_minimo && $percentual <= $grau->vlr_maximo) return $grau->cor;
            }
            return '#dee2e6';
        };
        $coresNivel = [1 => '#475569', 2 => '#2e8b57', 3 => '#0891b2', 4 => '#d97706', 5 => '#1B408E'];
        $tiposCenarioMeta = ['Otimista' => '#2e8b57', 'Tendencial' => '#1B408E', 'Pessimista' => '#dc3545'];
        $licaoMeta = ['Aprendizado' => '#2e8b57', 'Problema' => '#dc3545', 'Melhoria' => '#1B408E', 'Boas Práticas' => '#d97706'];
    @endphp

    {{-- ═══════════════ CAPA ═══════════════ --}}
    <div class="capa">
        <div class="capa-org">{{ $organizacao->nom_organizacao }}</div>
        <div class="capa-titulo">Dossiê Estratégico Integrado</div>
        <div class="capa-linha"></div>
        <div class="capa-sub">Documento consolidado de Planejamento Estratégico Institucional</div>

        <div class="capa-modulos">
            <span class="capa-mod" style="background:#1a3a5c;">01 · Inaugurar e Integrar</span>
            <span class="capa-mod" style="background:#2e6da4;">02 · Planejar</span>
            <span class="capa-mod" style="background:#6a4c9c;">03 · Monitorar e Avaliar</span>
        </div>

        <div class="capa-meta">
            <strong>Ciclo Estratégico:</strong> {{ $pei->dsc_pei ?? $filtros['ano'] }}<br>
            <strong>Período Analisado:</strong> {{ $filtros['periodo'] }}<br>
            <strong>Data de Geração:</strong> {{ now()->format('d/m/Y \à\s H:i') }}
        </div>
    </div>

    {{-- ═══════════════ CAP 1 — IDENTIDADE ═══════════════ --}}
    <div class="cap">Capítulo 1 · Módulo 02 — Planejar</div>
    <div class="cap-titulo">Identidade Estratégica</div>

    <table style="width:100%; border-collapse:separate; border-spacing:6px 0; margin-bottom:8px;">
        <tr>
            <td class="id-card" style="width:50%;">
                <span class="id-label">Missão</span>
                <div class="id-text">{{ $identidade->dsc_missao ?? 'Não definida' }}</div>
            </td>
            <td class="id-card" style="width:50%;">
                <span class="id-label">Visão</span>
                <div class="id-text">{{ $identidade->dsc_visao ?? 'Não definida' }}</div>
            </td>
        </tr>
    </table>

    <div class="id-card text-center" style="margin-bottom:6px;">
        <span class="id-label">Valores Institucionais</span>
        @forelse($valores as $valor)
            <span class="chip">{{ $valor->nom_valor }}</span>
        @empty
            <span style="font-style:italic; color:#a0aec0; font-size:8px;">Valores não cadastrados.</span>
        @endforelse
    </div>

    @if($temasNorteadores->isNotEmpty())
    <div class="id-card text-center">
        <span class="id-label">Temas Norteadores</span>
        @foreach($temasNorteadores as $t)
            <span class="chip">{{ $t->nom_tema_norteador }}</span>
        @endforeach
    </div>
    @endif

    @if($aiSummary)
    <div class="ai-box">
        <strong>&#9733; INSIGHT ESTRATÉGICO (IA)</strong>
        <p>{!! nl2br(e($aiSummary)) !!}</p>
    </div>
    @endif

    {{-- ═══════════════ CAP 2 — INAUGURAR E INTEGRAR ═══════════════ --}}
    <div class="page-break"></div>
    <div class="cap" style="color:#1a3a5c;">Capítulo 2 · Módulo 01 — Inaugurar e Integrar</div>
    <div class="cap-titulo">Planejamento do Processo e Integração</div>

    @if($inaugurar)
        <table class="kpi-grid"><tr>
            <td class="kpi-card" style="width:50%;">
                <p class="kpi-label">Equipe de Planejamento</p>
                <p style="font-size:9px; margin:4px 0 0 0; color:#2d3748;">{{ Str::limit($inaugurar->txt_equipe, 180) ?: '—' }}</p>
            </td>
            <td class="kpi-card success" style="width:25%;">
                <p class="kpi-label">Aprovação</p>
                <p class="kpi-value" style="font-size:14px; color:{{ $inaugurar->bln_aprovado ? '#2e8b57' : '#d97706' }};">{{ $inaugurar->bln_aprovado ? 'Aprovado' : 'Pendente' }}</p>
                <p class="kpi-sub">pela Alta Direção</p>
            </td>
            <td class="kpi-card accent" style="width:25%;">
                <p class="kpi-label">Início do Processo</p>
                <p style="font-size:12px; font-weight:bold; margin:6px 0 0 0; color:#1a3a5c;">{{ $inaugurar->dte_inicio_processo?->format('d/m/Y') ?? '—' }}</p>
            </td>
        </tr></table>
        @if($inaugurar->txt_diretrizes)
        <div class="id-card" style="margin-bottom:10px;">
            <span class="id-label">Diretrizes da Alta Direção</span>
            <div style="font-size:9px; color:#2d3748;">{{ $inaugurar->txt_diretrizes }}</div>
        </div>
        @endif
    @else
        <div class="vazio">Planejamento do processo (Módulo 01) ainda não preenchido.</div>
    @endif

    <div class="secao-titulo">Integração com Instrumentos de Governo</div>
    @if($integracoes->isNotEmpty())
    <table class="rpt">
        <thead><tr><th>Instrumento</th><th>Tipo</th><th class="text-center">Intensidade</th><th>Pontos de Atenção</th></tr></thead>
        <tbody>
            @foreach($integracoes as $i)
            <tr>
                <td class="row-titulo">{{ $i->dsc_instrumento }}</td>
                <td><span class="pill pill-info">{{ $i->dsc_tipo_instrumento }}</span></td>
                <td class="text-center">{{ $i->dsc_intensidade }}</td>
                <td class="row-desc">{{ Str::limit($i->txt_pontos_atencao ?? '—', 80) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <div class="vazio">Nenhuma integração com instrumentos (PPA/LOA/ODS) registrada.</div>
    @endif

    {{-- ═══════════════ CAP 3 — CADEIA DE VALOR ═══════════════ --}}
    <div class="page-break"></div>
    <div class="cap">Capítulo 3 · Módulo 02 — Planejar</div>
    <div class="cap-titulo">Cadeia de Valor</div>

    @php $finalisticas = $cadeiaValor->get('Finalística', collect()); $suporte = $cadeiaValor->get('Suporte', collect()); @endphp
    @if($finalisticas->isNotEmpty() || $suporte->isNotEmpty())
        <div class="grupo-band" style="background:linear-gradient(90deg,#1a3a5c,#2e6da4);">Atividades Finalísticas <span class="contador">{{ $finalisticas->count() }}</span></div>
        <table class="rpt bordered">
            <thead><tr><th style="width:35%;">Atividade</th><th>Processos (Entrada → Transformação → Saída)</th></tr></thead>
            <tbody>
                @forelse($finalisticas as $a)
                <tr>
                    <td class="row-titulo">{{ $a->dsc_atividade }}</td>
                    <td class="row-desc">
                        @forelse($a->processos as $proc)
                            &#8226; {{ Str::limit($proc->dsc_transformacao, 70) }}<br>
                        @empty <span style="color:#cbd5e0;">Sem processos detalhados.</span> @endforelse
                    </td>
                </tr>
                @empty <tr><td colspan="2" class="text-center" style="color:#a0aec0;">—</td></tr> @endforelse
            </tbody>
        </table>
        <div class="grupo-band" style="background:#475569;">Atividades de Suporte <span class="contador">{{ $suporte->count() }}</span></div>
        <table class="rpt bordered">
            <thead><tr><th style="width:35%;">Atividade</th><th>Processos</th></tr></thead>
            <tbody>
                @forelse($suporte as $a)
                <tr>
                    <td class="row-titulo">{{ $a->dsc_atividade }}</td>
                    <td class="row-desc">
                        @forelse($a->processos as $proc) &#8226; {{ Str::limit($proc->dsc_transformacao, 70) }}<br>
                        @empty <span style="color:#cbd5e0;">Sem processos detalhados.</span> @endforelse
                    </td>
                </tr>
                @empty <tr><td colspan="2" class="text-center" style="color:#a0aec0;">—</td></tr> @endforelse
            </tbody>
        </table>
    @else
        <div class="vazio">Cadeia de Valor não cadastrada para este ciclo PEI.</div>
    @endif

    {{-- ═══════════════ CAP 4 — ANÁLISE DE AMBIENTE ═══════════════ --}}
    <div class="page-break"></div>
    <div class="cap">Capítulo 4 · Módulo 02 — Planejar</div>
    <div class="cap-titulo">Análise de Ambiente</div>

    {{-- SWOT --}}
    <div class="secao-titulo">Matriz SWOT</div>
    @if($swot->isNotEmpty())
    <table style="width:100%; border-collapse:separate; border-spacing:6px;">
        <tr>
            <td class="swot-cell" style="background:#e8f5e9;">
                <strong style="color:#198754;">FORÇAS</strong>
                <ul>@forelse($swot->get('Força', []) as $i)<li>{{ $i->dsc_item }} @if(($i->num_gravidade ?? 0))<span style="color:#718096;">(GUT {{ ($i->num_gravidade)*($i->num_urgencia)*($i->num_tendencia) }})</span>@endif</li>@empty<li style="list-style:none;color:#a0aec0;">—</li>@endforelse</ul>
            </td>
            <td class="swot-cell" style="background:#fbecec;">
                <strong style="color:#dc3545;">FRAQUEZAS</strong>
                <ul>@forelse($swot->get('Fraqueza', []) as $i)<li>{{ $i->dsc_item }} @if(($i->num_gravidade ?? 0))<span style="color:#718096;">(GUT {{ ($i->num_gravidade)*($i->num_urgencia)*($i->num_tendencia) }})</span>@endif</li>@empty<li style="list-style:none;color:#a0aec0;">—</li>@endforelse</ul>
            </td>
        </tr>
        <tr>
            <td class="swot-cell" style="background:#e7f1ff;">
                <strong style="color:#1B408E;">OPORTUNIDADES</strong>
                <ul>@forelse($swot->get('Oportunidade', []) as $i)<li>{{ $i->dsc_item }}</li>@empty<li style="list-style:none;color:#a0aec0;">—</li>@endforelse</ul>
            </td>
            <td class="swot-cell" style="background:#fff8e1;">
                <strong style="color:#d97706;">AMEAÇAS</strong>
                <ul>@forelse($swot->get('Ameaça', []) as $i)<li>{{ $i->dsc_item }}</li>@empty<li style="list-style:none;color:#a0aec0;">—</li>@endforelse</ul>
            </td>
        </tr>
    </table>
    @else
        <div class="vazio">Análise SWOT não cadastrada.</div>
    @endif

    {{-- PESTEL --}}
    @if($pestel->isNotEmpty())
    <div class="secao-titulo">Análise PESTEL</div>
    <table class="rpt bordered">
        <thead><tr><th style="width:18%;">Dimensão</th><th>Fatores Identificados</th></tr></thead>
        <tbody>
            @foreach($pestel as $dimensao => $itens)
            <tr>
                <td class="row-titulo">{{ $dimensao }}</td>
                <td class="row-desc">@foreach($itens as $i){{ $i->dsc_item }}@if(!$loop->last) · @endif @endforeach</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Partes Interessadas --}}
    @if($partesInteressadas->isNotEmpty())
    <div class="secao-titulo">Partes Interessadas (Stakeholders)</div>
    <table class="rpt">
        <thead><tr><th>Parte Interessada</th><th>Tipo</th><th class="text-center">Interesse</th><th class="text-center">Influência</th><th>Quadrante</th></tr></thead>
        <tbody>
            @foreach($partesInteressadas as $p)
            <tr>
                <td class="row-titulo">{{ $p->nom_parte }}</td>
                <td>{{ $p->dsc_tipo }}</td>
                <td class="text-center">{{ $p->num_interesse }}/5</td>
                <td class="text-center">{{ $p->num_influencia }}/5</td>
                <td><span class="pill pill-info">{{ $p->getQuadrante() }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Cenários Prospectivos --}}
    @if($cenarios->isNotEmpty())
    <div class="secao-titulo">Cenários Prospectivos</div>
    <table class="rpt">
        <thead><tr><th>Cenário</th><th class="text-center">Tipo</th><th>Resposta Estratégica</th><th class="text-center">Prob×Imp</th></tr></thead>
        <tbody>
            @foreach($cenarios as $c)
            <tr>
                <td class="row-titulo">{{ $c->nom_cenario }}</td>
                <td class="text-center"><span class="pill" style="background:{{ ($tiposCenarioMeta[$c->dsc_tipo] ?? '#718096') }}; color:#fff;">{{ $c->dsc_tipo }}</span></td>
                <td class="row-desc">{{ Str::limit($c->txt_resposta_estrategica ?? '—', 80) }}</td>
                <td class="text-center">{{ $c->num_probabilidade }}×{{ $c->num_impacto }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ═══════════════ CAP 5 — MAPA ESTRATÉGICO ═══════════════ --}}
    <div class="page-break"></div>
    <div class="cap">Capítulo 5 · Módulo 02 — Planejar</div>
    <div class="cap-titulo">Mapa Estratégico (BSC)</div>

    @foreach($perspectivas->sortByDesc('num_nivel_hierarquico_apresentacao') as $persp)
        @php
            $corP   = $coresNivel[$persp->num_nivel_hierarquico_apresentacao] ?? '#1B408E';
            $chunks = $persp->objetivos->chunk(3);
        @endphp
        <div class="persp-row">
            <div class="persp-header" style="background:{{ $corP }};">{{ $persp->dsc_perspectiva }}</div>
            <div class="persp-body">
                @if($persp->objetivos->isEmpty())
                    <span style="font-style:italic; color:#a0aec0; font-size:8px;">Sem objetivos vinculados.</span>
                @else
                <table style="width:100%; border-collapse:separate; border-spacing:4px;">
                    @foreach($chunks as $chunk)
                    <tr>
                        @foreach($chunk as $obj)
                        @php $at = $obj->calcularAtingimentoConsolidado($filtros['ano'], $filtros['mesLimite']); $cor = $getCorSatisfacao($at); @endphp
                        <td style="width:33.33%; vertical-align:top; background:#fff; border:1px solid #e2e8f0; border-left:3px solid {{ $cor }}; border-radius:6px; padding:8px;">
                            <span class="obj-title">{{ $obj->nom_objetivo }}</span>
                            <div style="font-size:8px; color:#718096; margin-top:3px;">
                                <span class="farol" style="background:{{ $cor }};"></span> {{ number_format($at, 1, ',', '.') }}%
                            </div>
                            @if(($obj->ods ?? collect())->isNotEmpty())
                                <div style="margin-top:4px;">
                                    @foreach($obj->ods as $ods)
                                        <span style="display:inline-block; background:{{ $ods->cod_cor }}; color:#fff; font-size:6px; font-weight:bold; padding:1px 4px; border-radius:3px; margin-right:2px;">ODS {{ $ods->num_ods }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        @endforeach
                        @for($i = $chunk->count(); $i < 3; $i++)
                        <td style="width:33.33%;"></td>
                        @endfor
                    </tr>
                    @endforeach
                </table>
                @endif
            </div>
        </div>
    @endforeach

    {{-- ═══════════════ CAP 6 — INDICADORES ═══════════════ --}}
    <div class="page-break"></div>
    <div class="cap">Capítulo 6 · Módulo 03 — Monitorar e Avaliar</div>
    <div class="cap-titulo">Monitoramento de Indicadores (KPIs)</div>

    <div class="legend-container" style="background:#f7fafc; border:1px solid #e2e8f0; border-radius:6px; padding:8px 12px; margin-bottom:12px;">
        <span style="font-size:8px; font-weight:bold; color:#718096; text-transform:uppercase;">Grau de Satisfação:</span>
        @foreach($grausSatisfacao as $grau)
            <span style="font-size:8px; margin-left:10px;"><span class="farol" style="background:{{ $grau->cor }};"></span> {{ $grau->dsc_grau_satisfcao ?? $grau->dsc_grau_satisfacao ?? '' }}</span>
        @endforeach
    </div>

    @foreach($perspectivas as $persp)
        @php $objsComInd = $persp->objetivos->filter(fn($o) => $o->indicadores->count() > 0); @endphp
        @if($objsComInd->isNotEmpty())
            <div class="grupo-band">{{ $persp->dsc_perspectiva }}</div>
            <table class="rpt">
                <thead><tr><th style="width:38%;">Indicador</th><th class="text-center">Unid.</th><th class="text-end">Meta</th><th class="text-end">Realizado</th><th class="text-center" style="width:80px;">Atingimento</th></tr></thead>
                <tbody>
                    @foreach($objsComInd as $obj)
                        @foreach($obj->indicadores as $ind)
                            @php
                                $na = $ind->dsc_polaridade === 'Não Aplicável';
                                $perc = $ind->calcularAtingimento();
                                $cor = $ind->getCorFarol();
                                $ult = $ind->getUltimaEvolucao();
                                $metaAno = optional($ind->metasPorAno->first())->meta;
                                $metaTxt = $metaAno !== null ? number_format($metaAno, 2, ',', '.') : ($ind->dsc_meta ?: '—');
                            @endphp
                            <tr>
                                <td class="row-titulo">{{ $ind->nom_indicador }}</td>
                                <td class="text-center" style="font-size:8px;">{{ $ind->dsc_unidade_medida }}</td>
                                <td class="text-end" style="font-size:8.5px; font-weight:bold; color:#1a3a5c;">{{ $metaTxt }}</td>
                                <td class="text-end" style="font-size:8.5px;">{{ $ult ? number_format($ult->vlr_realizado, 2, ',', '.') : '—' }}</td>
                                <td class="text-center">
                                    @if($na)<span class="pill pill-neutral">N/A</span>
                                    @else<span class="farol" style="background:{{ $cor }};"></span> <strong style="font-size:9px;">{{ number_format($perc, 1, ',', '.') }}%</strong>@endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    {{-- ═══════════════ CAP 7 — PLANOS, ENTREGAS, MODELO LÓGICO E RACI ═══════════════ --}}
    <div class="page-break"></div>
    <div class="cap">Capítulo 7 · Módulo 02 — Planejar</div>
    <div class="cap-titulo">Portfólio de Planos e Entregas</div>

    @forelse($planos as $plano)
        @php
            $prog = $plano->progresso_anual ?? 0;
            $st = $plano->status_anual ?? 'Não Iniciado';
            $cor = match($st) { 'Concluído' => '#2e8b57', 'Em Andamento' => '#1B408E', 'Atrasado' => '#dc3545', default => '#94a3b8' };
            $ml = $plano->json_modelo_logico ?? [];
            $raciPlano = $racis->get($plano->cod_plano_de_acao, collect());
            $entregas = $plano->detalhes_calculo ?? [];
        @endphp
        <div style="border:1px solid #e2e8f0; border-radius:8px; margin-bottom:14px; page-break-inside:avoid;">
            <div style="background:#f7fafc; padding:8px 12px; border-bottom:1px solid #e2e8f0;">
                <table style="width:100%; border:none;"><tr style="border:none;">
                    <td style="border:none;">
                        <strong style="color:#1a3a5c; font-size:10px;">{{ $plano->dsc_plano_de_acao }}</strong>
                        @if($plano->objetivo)<div style="font-size:8px; color:#a0aec0;">Objetivo: {{ Str::limit($plano->objetivo->nom_objetivo, 70) }}</div>@endif
                        <div style="font-size:8px; color:#718096;">{{ $plano->dte_inicio?->format('d/m/Y') }} a {{ $plano->dte_fim?->format('d/m/Y') }} · <strong style="color:{{ $cor }};">{{ number_format($prog, 1) }}% concluído</strong> · {{ count($entregas) }} {{ count($entregas) == 1 ? 'entrega' : 'entregas' }}</div>
                    </td>
                    <td style="border:none; text-align:right;"><span class="pill" style="background:{{ $cor }}; color:#fff;">{{ $st }}</span></td>
                </tr></table>
            </div>

            {{-- Entregas detalhadas (dado essencial) --}}
            @if(!empty($entregas))
            <table class="rpt" style="margin:0;">
                <thead><tr>
                    <th style="width:54%; padding-left:12px;">Entrega</th>
                    <th class="text-center" style="width:80px;">Prazo</th>
                    <th class="text-center" style="width:50px;">Peso</th>
                    <th class="text-center" style="width:90px;">Status</th>
                </tr></thead>
                <tbody>
                    @foreach($entregas as $e)
                    @php
                        $stE = $e['status'] ?? '—';
                        $cE = match($stE) { 'Concluído' => 'pill-success', 'Em Andamento' => 'pill-info', 'Atrasado','Cancelado' => 'pill-danger', default => 'pill-neutral' };
                    @endphp
                    <tr>
                        <td style="padding-left:12px;">{{ $e['entrega'] ?? '—' }}</td>
                        <td class="text-center" style="font-size:8px;">{{ $e['prazo'] ?? '—' }}</td>
                        <td class="text-center" style="font-size:8px;">{{ number_format($e['peso'] ?? 0, 1) }}%</td>
                        <td class="text-center"><span class="pill {{ $cE }}">{{ $stE }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div style="padding:8px 12px; font-size:8px; color:#b45309; background:#fffbeb;">
                Nenhuma entrega prevista para o exercício — progresso anual 0%.
            </div>
            @endif

            {{-- Complementos: Modelo Lógico e RACI (quando preenchidos) --}}
            @if(!empty(array_filter($ml)) || $raciPlano->isNotEmpty())
            <div style="padding:6px 12px; border-top:1px solid #edf2f7;">
                @if(!empty(array_filter($ml)))
                <div style="margin-bottom:4px; font-size:8px;">
                    <strong style="color:#1B408E; text-transform:uppercase;">Modelo Lógico:</strong>
                    @if(!empty($ml['insumos']))<span class="chip" style="font-size:7px;">Insumos: {{ Str::limit($ml['insumos'], 30) }}</span>@endif
                    @if(!empty($ml['resultados']))<span class="chip" style="font-size:7px;">Resultados: {{ Str::limit($ml['resultados'], 30) }}</span>@endif
                    @if(!empty($ml['impacto']))<span class="chip" style="font-size:7px;">Impacto: {{ Str::limit($ml['impacto'], 30) }}</span>@endif
                </div>
                @endif
                @if($raciPlano->isNotEmpty())
                <div style="font-size:8px;">
                    <strong style="color:#d97706; text-transform:uppercase;">RACI:</strong>
                    @foreach($raciPlano as $r)<span class="chip" style="font-size:7px; background:#fef3c7; color:#92400e; border-color:#fde68a;">{{ $r->dsc_papel }} · {{ $r->usuario?->name ?? '—' }}</span>@endforeach
                </div>
                @endif
            </div>
            @endif
        </div>
    @empty
        <div class="vazio">Nenhum plano de ação vigente no período.</div>
    @endforelse

    {{-- ═══════════════ CAP 8 — RISCOS ═══════════════ --}}
    <div class="page-break"></div>
    <div class="cap" style="color:#dc3545;">Capítulo 8 · Módulo 02 — Planejar</div>
    <div class="cap-titulo">Gestão de Riscos</div>

    <table class="rpt">
        <thead><tr><th class="text-center" style="width:46px;">Nível</th><th style="width:34%;">Risco</th><th>Categoria</th><th class="text-center" style="width:60px;">Mitigações</th><th style="width:32%;">Ação de Mitigação Principal</th></tr></thead>
        <tbody>
            @forelse($riscosDetalhado as $risco)
            @php $mit = $risco->mitigacoes->first(); @endphp
            <tr>
                <td class="text-center" style="font-weight:bold; color:{{ $risco->getNivelRiscoCor() }};">{{ $risco->num_nivel_risco }}</td>
                <td class="row-titulo">{{ $risco->dsc_titulo }}</td>
                <td class="row-desc">{{ $risco->dsc_categoria }}</td>
                <td class="text-center">
                    @if($risco->mitigacoes->count() > 0)<span class="pill pill-info">{{ $risco->mitigacoes->count() }}</span>
                    @else<span style="color:#cbd5e0;">—</span>@endif
                </td>
                <td class="row-desc">
                    @if($mit)
                        {{ Str::limit($mit->txt_descricao ?? '', 70) ?: '—' }}
                        @if($mit->dsc_status)<br><span class="pill pill-neutral" style="font-size:7px;">{{ $mit->dsc_status }}</span>@endif
                    @else
                        <span style="color:#cbd5e0;">Sem mitigação cadastrada</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center" style="color:#a0aec0;">Nenhum risco monitorado.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ═══════════════ CAP 9 — COMUNICAÇÃO ═══════════════ --}}
    @if($comunicacoes->isNotEmpty())
    <div class="page-break"></div>
    <div class="cap" style="color:#0891b2;">Capítulo 9 · Módulo 03 — Monitorar e Avaliar</div>
    <div class="cap-titulo">Plano de Comunicação</div>
    <table class="rpt">
        <thead><tr><th>Público-Alvo</th><th>Mensagem-Chave</th><th class="text-center">Canal</th><th class="text-center">Frequência</th></tr></thead>
        <tbody>
            @foreach($comunicacoes as $c)
            <tr>
                <td class="row-titulo">{{ $c->nom_publico_alvo }}</td>
                <td class="row-desc">{{ Str::limit($c->dsc_mensagem_chave, 60) }}</td>
                <td class="text-center"><span class="pill pill-info">{{ $c->dsc_canal }}</span></td>
                <td class="text-center" style="font-size:8px;">{{ $c->dsc_frequencia }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ═══════════════ CAP 10 — RAE ═══════════════ --}}
    @if($raes->isNotEmpty())
    <div class="page-break"></div>
    <div class="cap" style="color:#6a4c9c;">Capítulo 10 · Módulo 03 — Monitorar e Avaliar</div>
    <div class="cap-titulo">Revisão e Avaliação da Estratégia (RAE)</div>
    @foreach($raes as $rae)
    <div style="border:1px solid #e2e8f0; border-radius:8px; margin-bottom:12px; padding:10px 14px; page-break-inside:avoid;">
        <table style="width:100%; border:none;"><tr style="border:none;">
            <td style="border:none;"><strong style="color:#6a4c9c; font-size:10px;">{{ $rae->dsc_tipo_reuniao }} · Ref. {{ $rae->dte_referencia->format('m/Y') }}</strong></td>
            <td style="border:none; text-align:right;">@if($rae->num_progresso_geral !== null)<span class="pill pill-info">Progresso: {{ number_format($rae->num_progresso_geral, 1) }}%</span>@endif</td>
        </tr></table>
        @if($rae->txt_destaques_positivos)<div style="font-size:8.5px; margin-top:5px;"><strong style="color:#2e8b57;">Destaques:</strong> {{ Str::limit($rae->txt_destaques_positivos, 150) }}</div>@endif
        @if($rae->txt_problemas_identificados)<div style="font-size:8.5px; margin-top:3px;"><strong style="color:#dc3545;">Problemas:</strong> {{ Str::limit($rae->txt_problemas_identificados, 150) }}</div>@endif
        @if($rae->txt_encaminhamentos)<div style="font-size:8.5px; margin-top:3px;"><strong style="color:#1B408E;">Encaminhamentos:</strong> {{ Str::limit($rae->txt_encaminhamentos, 150) }}</div>@endif
    </div>
    @endforeach
    @endif

    {{-- ═══════════════ CAP 11 — LIÇÕES APRENDIDAS ═══════════════ --}}
    @if($licoesAprendidas->isNotEmpty())
    <div class="page-break"></div>
    <div class="cap" style="color:#d97706;">Capítulo 11 · Guia de Projetos — Domínio 7</div>
    <div class="cap-titulo">Impacto e Aprendizado · Lições Aprendidas</div>
    @foreach($licoesAprendidas as $tipo => $licoes)
    <div class="grupo-band" style="background:{{ $licaoMeta[$tipo] ?? '#475569' }};">{{ $tipo }} <span class="contador">{{ $licoes->count() }}</span></div>
    <table class="rpt bordered">
        <thead><tr><th style="width:22%;">Categoria</th><th>Descrição</th><th style="width:30%;">Recomendação</th></tr></thead>
        <tbody>
            @foreach($licoes as $l)
            <tr>
                <td><span class="pill pill-neutral">{{ $l->dsc_categoria }}</span></td>
                <td class="row-desc">{{ Str::limit($l->txt_descricao, 90) }}</td>
                <td class="row-desc">{{ Str::limit($l->txt_recomendacao ?? '—', 60) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach
    @endif

    {{-- ═══════════════ CAP 12 — AGENDA 2030 (ODS) ═══════════════ --}}
    @if(($odsAderencia ?? collect())->isNotEmpty() || !empty($odsPorObjetivo))
    <div class="page-break"></div>
    <div class="cap" style="color:#2e8b57;">Capítulo 12 · Desenvolvimento Sustentável</div>
    <div class="cap-titulo">Contribuição à Agenda 2030 (ODS)</div>

    @php
        $totalCobertos = count($odsPorObjetivo ?? []);
    @endphp

    <p style="font-size:9px; color:#4a5568; margin-bottom:10px;">
        Alinhamento da estratégia institucional aos Objetivos de Desenvolvimento Sustentável da ONU.
        <strong>{{ $totalCobertos }} de 18 ODS</strong> contam com objetivos estratégicos vinculados neste ciclo.
    </p>

    {{-- Aderência institucional declarada (PEI ↔ ODS) --}}
    @if(($odsAderencia ?? collect())->isNotEmpty())
    <div class="secao-titulo">Aderência Institucional Declarada</div>
    <table class="rpt">
        <thead><tr><th style="width:46px;">ODS</th><th style="width:26%;">Objetivo de Desenvolvimento Sustentável</th><th class="text-center" style="width:70px;">Intensidade</th><th>Contribuição da Instituição</th></tr></thead>
        <tbody>
            @foreach($odsAderencia as $ods)
            <tr>
                <td class="text-center">
                    <span style="display:inline-block; background:{{ $ods->cod_cor }}; color:#fff; font-size:9px; font-weight:bold; padding:3px 7px; border-radius:4px;">{{ $ods->num_ods }}</span>
                </td>
                <td class="row-titulo">{{ $ods->nom_ods }}</td>
                <td class="text-center">
                    @php $int = $ods->pivot->dsc_intensidade ?? 'Media'; $intCor = $int === 'Alta' ? '#dc3545' : ($int === 'Baixa' ? '#2e8b57' : '#d97706'); @endphp
                    <span class="pill" style="background:{{ $intCor }}; color:#fff;">{{ $int }}</span>
                </td>
                <td class="row-desc">{{ $ods->pivot->txt_contribuicao ?: '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Cobertura por objetivos estratégicos --}}
    @if(!empty($odsPorObjetivo))
    <div class="secao-titulo">Objetivos Estratégicos por ODS</div>
    <table class="rpt bordered">
        <thead><tr><th style="width:46px;">ODS</th><th style="width:26%;">Objetivo de Desenvolvimento Sustentável</th><th>Objetivos Estratégicos Vinculados</th></tr></thead>
        <tbody>
            @foreach($odsPorObjetivo as $num => $item)
            <tr>
                <td class="text-center">
                    <span style="display:inline-block; background:{{ $item['ods']->cod_cor }}; color:#fff; font-size:9px; font-weight:bold; padding:3px 7px; border-radius:4px;">{{ $num }}</span>
                </td>
                <td class="row-titulo">{{ $item['ods']->nom_ods }}</td>
                <td class="row-desc">
                    @foreach($item['objetivos'] as $nomeObj)
                        &#8226; {{ $nomeObj }}@if(!$loop->last)<br>@endif
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @endif

</body>
</html>
