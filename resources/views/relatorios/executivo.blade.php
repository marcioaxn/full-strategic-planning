<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Executivo — {{ $organizacao->nom_organizacao }}</title>
    @include('relatorios.partials.estilos')
    <style>
        .page-break { page-break-after: always; }
        .swot-cell { width: 50%; vertical-align: top; padding: 10px; border-radius: 6px; }
        .swot-cell strong { font-size: 10px; }
        .swot-cell ul { margin: 6px 0 0 0; padding-left: 14px; font-size: 8.5px; line-height: 1.5; }
        .ai-box { background: #f0f7ff; border-left: 4px solid #1B408E; border-radius: 6px; padding: 12px 14px; margin-top: 12px; }
        .ai-box p { margin: 5px 0 0 0; font-style: italic; font-size: 9px; line-height: 1.5; }
    </style>
</head>
<body>
    @include('relatorios.partials.cabecalho', [
        'rptTitulo'    => 'Relatório Executivo',
        'rptEyebrow'   => 'Gestão Estratégica Consolidada · GPPEI/MGI 2025',
        'rptSubtitulo' => $organizacao->nom_organizacao . ' · Exercício ' . $filtros['ano'],
        'rptIcon'      => '&#9733;',
    ])
    @include('relatorios.partials.rodape')

    @php
        $ano       = $filtros['ano'];
        $mesLimite = $filtros['mesLimite'];

        $getCorSatisfacao = function($percentual) use ($grausSatisfacao) {
            foreach ($grausSatisfacao as $grau) {
                if ($percentual >= $grau->vlr_minimo && $percentual <= $grau->vlr_maximo) return $grau->cor;
            }
            return '#dee2e6';
        };

        $coresNivel = [1 => '#475569', 2 => '#2e8b57', 3 => '#0891b2', 4 => '#d97706', 5 => '#1B408E'];

        $totalObjetivos   = $perspectivas->sum(fn($p) => $p->objetivos->count());
        $totalIndicadores = $perspectivas->sum(fn($p) => $p->objetivos->sum(fn($o) => $o->indicadores->count()));

        $atingimentos = [];
        foreach($perspectivas as $p) {
            foreach($p->objetivos as $obj) {
                $atingimentos[] = $obj->calcularAtingimentoConsolidado($ano, $mesLimite);
            }
        }
        $mediaAtingimento = count($atingimentos) > 0 ? array_sum($atingimentos) / count($atingimentos) : 0;

        $totalPlanos     = $planos->count();
        $planosConcluidos  = $planos->where('status_anual', 'Concluído')->count();
        $planosEmAndamento = $planos->where('status_anual', 'Em Andamento')->count();
        $planosAtrasados   = $planos->where('status_anual', 'Atrasado')->count();
        $planosNaoIniciados = $planos->whereIn('status_anual', ['Não Iniciado', 'Sem Entregas'])->count();

        $riscosCriticos = $riscosDetalhado->filter(fn($r) => $r->num_nivel_risco >= 16)->count();
        $riscosAltos    = $riscosDetalhado->filter(fn($r) => $r->num_nivel_risco >= 10 && $r->num_nivel_risco < 16)->count();

        $statusCfg = [
            'Concluído'    => ['c' => '#2e8b57', 'pill' => 'pill-success'],
            'Em Andamento' => ['c' => '#1B408E', 'pill' => 'pill-info'],
            'Atrasado'     => ['c' => '#dc3545', 'pill' => 'pill-danger'],
            'Não Iniciado' => ['c' => '#94a3b8', 'pill' => 'pill-neutral'],
            'Sem Entregas' => ['c' => '#94a3b8', 'pill' => 'pill-neutral'],
        ];
    @endphp

    <div class="rpt-filtros">
        <span><strong>Período:</strong> {{ $filtros['periodo'] }}</span>
        <span><strong>Exercício:</strong> {{ $ano }}</span>
        <span><strong>Perspectiva:</strong> {{ $filtros['perspectiva'] }}</span>
        <span><strong>Emissão:</strong> {{ now()->format('d/m/Y H:i') }}</span>
    </div>

    {{-- ══ KPIs ══ --}}
    <table class="kpi-grid">
        <tr>
            <td class="kpi-card" style="width:25%;">
                <p class="kpi-label">Objetivos BSC</p>
                <p class="kpi-value">{{ $totalObjetivos }}</p>
                <p class="kpi-sub">{{ $totalIndicadores }} indicadores vinculados</p>
            </td>
            <td class="kpi-card accent" style="width:25%;">
                <p class="kpi-label">Atingimento Médio</p>
                <p class="kpi-value">{{ number_format($mediaAtingimento, 0, ',', '.') }}<span style="font-size:14px;">%</span></p>
                <p class="kpi-sub" style="color:{{ $getCorSatisfacao($mediaAtingimento) }};">desempenho geral</p>
            </td>
            <td class="kpi-card {{ $planosAtrasados > 0 ? 'warning' : 'success' }}" style="width:25%;">
                <p class="kpi-label">Planos de Ação</p>
                <p class="kpi-value">{{ $totalPlanos }}</p>
                <p class="kpi-sub">{{ $planosConcluidos }} concluídos · {{ $planosAtrasados }} atrasados</p>
            </td>
            <td class="kpi-card {{ $riscosCriticos > 0 ? 'danger' : 'success' }}" style="width:25%;">
                <p class="kpi-label">Riscos Críticos</p>
                <p class="kpi-value" style="color:{{ $riscosCriticos > 0 ? '#dc3545' : '#2e8b57' }};">{{ $riscosCriticos }}</p>
                <p class="kpi-sub">{{ $riscosAltos }} altos · {{ $riscosDetalhado->count() }} no total</p>
            </td>
        </tr>
    </table>

    {{-- ══ IDENTIDADE ESTRATÉGICA ══ --}}
    <div class="secao-titulo">Identidade Estratégica</div>

    <table style="width:100%; border-collapse:separate; border-spacing:6px 0; margin-bottom:8px;">
        <tr>
            <td style="width:50%; background:#fff; border:1px solid #e2e8f0; border-left:4px solid #1B408E; border-radius:8px; padding:12px; vertical-align:top;">
                <div style="color:#1B408E; font-weight:bold; font-size:8px; text-transform:uppercase; letter-spacing:.5px; margin-bottom:5px;">Missão</div>
                <div style="font-style:italic; font-size:9.5px; line-height:1.5; color:#2d3748;">{{ $identidade->dsc_missao ?? 'Não definida' }}</div>
            </td>
            <td style="width:50%; background:#fff; border:1px solid #e2e8f0; border-left:4px solid #e07b39; border-radius:8px; padding:12px; vertical-align:top;">
                <div style="color:#e07b39; font-weight:bold; font-size:8px; text-transform:uppercase; letter-spacing:.5px; margin-bottom:5px;">Visão</div>
                <div style="font-style:italic; font-size:9.5px; line-height:1.5; color:#2d3748;">{{ $identidade->dsc_visao ?? 'Não definida' }}</div>
            </td>
        </tr>
    </table>

    @if($valores->isNotEmpty() || $temasNorteadores->isNotEmpty())
    <table style="width:100%; border-collapse:separate; border-spacing:6px 0; margin-bottom:14px;">
        <tr>
            @if($valores->isNotEmpty())
            <td style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:10px; text-align:center; vertical-align:top;">
                <div style="color:#718096; font-weight:bold; font-size:7.5px; text-transform:uppercase; margin-bottom:6px; letter-spacing:.5px;">Valores Institucionais</div>
                @foreach($valores as $valor)
                    <span class="pill pill-info" style="margin:2px;">{{ $valor->nom_valor }}</span>
                @endforeach
            </td>
            @endif
            @if($temasNorteadores->isNotEmpty())
            <td style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:10px; text-align:center; vertical-align:top;">
                <div style="color:#718096; font-weight:bold; font-size:7.5px; text-transform:uppercase; margin-bottom:6px; letter-spacing:.5px;">Temas Norteadores</div>
                @foreach($temasNorteadores as $t)
                    <span style="display:inline-block; background:#eef2f9; color:#1B408E; border:1px solid #c7d6ec; border-radius:999px; padding:4px 12px; margin:2px; font-size:8px; font-weight:bold;">{{ $t->nom_tema_norteador }}</span>
                @endforeach
            </td>
            @endif
        </tr>
    </table>
    @endif

    {{-- ══ DESEMPENHO BSC ══ --}}
    <div class="secao-titulo">Desempenho Estratégico por Perspectiva (BSC)</div>

    <div style="margin-bottom:10px; font-size:8px; color:#718096; background:#f7fafc; padding:6px 10px; border-radius:6px; border:1px solid #e2e8f0;">
        <strong style="color:#1a3a5c;">Graus de Satisfação:</strong>
        @foreach($grausSatisfacao as $grau)
            <span style="margin-left:12px;">
                <span class="farol" style="background:{{ $grau->cor }};"></span>
                {{ $grau->dsc_grau_satisfcao ?? $grau->dsc_grau_satisfacao ?? '' }}
                ({{ number_format($grau->vlr_minimo, 0) }}–{{ number_format($grau->vlr_maximo, 0) }}%)
            </span>
        @endforeach
    </div>

    @foreach($perspectivas->sortByDesc('num_nivel_hierarquico_apresentacao') as $persp)
        @php $corP = $coresNivel[$persp->num_nivel_hierarquico_apresentacao] ?? '#1B408E'; @endphp
        <div style="margin-bottom:10px; page-break-inside:avoid;">
            <div style="background:{{ $corP }}; color:#fff; padding:6px 12px; font-weight:bold; font-size:9px; border-radius:6px 6px 0 0; text-transform:uppercase; letter-spacing:.5px;">
                {{ $persp->dsc_perspectiva }}
            </div>
            <table class="rpt" style="margin:0; border-radius:0 0 6px 6px;">
                <thead>
                    <tr>
                        <th style="width:55%;">Objetivo Estratégico</th>
                        <th class="text-center" style="width:60px;">KPIs</th>
                        <th class="text-center" style="width:120px;">Atingimento</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($persp->objetivos as $obj)
                        @php
                            $at  = $obj->calcularAtingimentoConsolidado($ano, $mesLimite);
                            $cor = $getCorSatisfacao($at);
                        @endphp
                        <tr>
                            <td class="row-titulo">
                                {{ $obj->nom_objetivo }}
                                @if($obj->ods->isNotEmpty())
                                    <span style="white-space:nowrap;">
                                        @foreach($obj->ods as $ods)
                                            <span style="display:inline-block; background:{{ $ods->cod_cor }}; color:#fff; font-size:6.5px; font-weight:bold; padding:1px 4px; border-radius:3px; margin-left:2px;">ODS {{ $ods->num_ods }}</span>
                                        @endforeach
                                    </span>
                                @endif
                            </td>
                            <td class="text-center" style="font-size:8px; color:#718096;">{{ $obj->indicadores->count() }}</td>
                            <td>
                                <table style="width:100%; border:none;"><tr style="border:none;">
                                    <td style="border:none; width:72%; vertical-align:middle; padding:0 4px 0 0;">
                                        <div class="progress-track">
                                            <div class="progress-fill" style="width:{{ min(100, max(0, $at)) }}%; background:{{ $cor }};"></div>
                                        </div>
                                    </td>
                                    <td style="border:none; width:28%; text-align:right; vertical-align:middle; font-weight:bold; font-size:9px; color:{{ $cor }}; padding:0;">
                                        {{ number_format($at, 1, ',', '.') }}%
                                    </td>
                                </tr></table>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center" style="color:#a0aec0; font-style:italic; padding:10px;">Sem objetivos nesta perspectiva.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach

    @if($aiSummary)
    <div class="ai-box">
        <strong style="color:#1B408E; font-size:9px;">&#9733; INSIGHT ESTRATÉGICO (IA)</strong>
        <p>{!! nl2br(e($aiSummary)) !!}</p>
    </div>
    @endif

    <div class="page-break"></div>

    {{-- ══ ANÁLISE SWOT ══ --}}
    @if($swot->isNotEmpty())
    <div class="secao-titulo">Análise de Ambiente — Matriz SWOT</div>
    <table style="width:100%; border-collapse:separate; border-spacing:6px;">
        <tr>
            <td class="swot-cell" style="background:#e8f5e9;">
                <strong style="color:#198754;">FORÇAS (Interno +)</strong>
                <ul>
                    @forelse($swot->get('Força', []) as $i)
                        <li>{{ $i->dsc_item }}@if(($i->num_gravidade ?? 0)) <span style="color:#a0aec0; font-size:7.5px;">(GUT {{ $i->num_gravidade * $i->num_urgencia * $i->num_tendencia }})</span>@endif</li>
                    @empty <li style="list-style:none; color:#a0aec0;">—</li> @endforelse
                </ul>
            </td>
            <td class="swot-cell" style="background:#fbecec;">
                <strong style="color:#dc3545;">FRAQUEZAS (Interno −)</strong>
                <ul>
                    @forelse($swot->get('Fraqueza', []) as $i)
                        <li>{{ $i->dsc_item }}@if(($i->num_gravidade ?? 0)) <span style="color:#a0aec0; font-size:7.5px;">(GUT {{ $i->num_gravidade * $i->num_urgencia * $i->num_tendencia }})</span>@endif</li>
                    @empty <li style="list-style:none; color:#a0aec0;">—</li> @endforelse
                </ul>
            </td>
        </tr>
        <tr>
            <td class="swot-cell" style="background:#e7f1ff;">
                <strong style="color:#1B408E;">OPORTUNIDADES (Externo +)</strong>
                <ul>
                    @forelse($swot->get('Oportunidade', []) as $i)
                        <li>{{ $i->dsc_item }}</li>
                    @empty <li style="list-style:none; color:#a0aec0;">—</li> @endforelse
                </ul>
            </td>
            <td class="swot-cell" style="background:#fff8e1;">
                <strong style="color:#d97706;">AMEAÇAS (Externo −)</strong>
                <ul>
                    @forelse($swot->get('Ameaça', []) as $i)
                        <li>{{ $i->dsc_item }}</li>
                    @empty <li style="list-style:none; color:#a0aec0;">—</li> @endforelse
                </ul>
            </td>
        </tr>
    </table>
    @endif

    {{-- ══ PLANOS DE AÇÃO ══ --}}
    <div class="secao-titulo">Carteira de Planos de Ação — {{ $ano }}</div>

    @php $tot = max(1, $totalPlanos); @endphp
    <div style="margin-bottom:10px;">
        <div class="progress-track" style="height:14px; border-radius:4px;">
            @foreach(['Concluído' => $planosConcluidos, 'Em Andamento' => $planosEmAndamento, 'Atrasado' => $planosAtrasados, 'Não Iniciado' => $planosNaoIniciados] as $s => $n)
                @if($n > 0)
                    <div style="float:left; height:14px; width:{{ round($n / $tot * 100, 1) }}%; background:{{ $statusCfg[$s]['c'] ?? '#94a3b8' }};"></div>
                @endif
            @endforeach
        </div>
        <div style="margin-top:5px; font-size:8px; color:#718096; clear:both;">
            @foreach(['Concluído' => $planosConcluidos, 'Em Andamento' => $planosEmAndamento, 'Atrasado' => $planosAtrasados, 'Não Iniciado' => $planosNaoIniciados] as $s => $n)
                <span style="margin-right:14px;"><span class="farol" style="background:{{ $statusCfg[$s]['c'] ?? '#94a3b8' }};"></span> {{ $s }}: <strong>{{ $n }}</strong></span>
            @endforeach
        </div>
    </div>

    <table class="rpt">
        <thead>
            <tr>
                <th style="width:34%;">Plano de Ação</th>
                <th>Perspectiva / Objetivo</th>
                <th class="text-center" style="width:80px;">Status</th>
                <th class="text-center" style="width:100px;">Progresso</th>
            </tr>
        </thead>
        <tbody>
            @forelse($planos as $plano)
            @php
                $prog = $plano->progresso_anual ?? 0;
                $st   = $plano->status_anual ?? 'Não Iniciado';
                $cfg  = $statusCfg[$st] ?? ['c' => '#94a3b8', 'pill' => 'pill-neutral'];
            @endphp
            <tr>
                <td>
                    <span class="row-titulo">{{ $plano->dsc_plano_de_acao }}</span>
                    <div style="font-size:7.5px; color:#a0aec0; margin-top:2px;">
                        {{ $plano->dte_inicio?->format('d/m/Y') }} a {{ $plano->dte_fim?->format('d/m/Y') }}
                        &middot; {{ $plano->entregas_ano_count ?? 0 }} entrega(s) no exercício
                    </div>
                </td>
                <td class="row-desc">
                    <span style="font-size:8px; font-weight:bold; color:#1a3a5c;">{{ $plano->objetivo?->perspectiva?->dsc_perspectiva ?? '—' }}</span><br>
                    {{ Str::limit($plano->objetivo?->nom_objetivo ?? '—', 55) }}
                </td>
                <td class="text-center"><span class="pill {{ $cfg['pill'] }}">{{ $st }}</span></td>
                <td>
                    <div style="text-align:center; font-weight:bold; font-size:9px; margin-bottom:2px; color:{{ $cfg['c'] }};">{{ number_format($prog, 0) }}%</div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width:{{ min(100, max(0, $prog)) }}%; background:{{ $cfg['c'] }};"></div>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="4"><div class="vazio mb-0">Nenhum plano de ação vigente em {{ $ano }}.</div></td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ══ RISCOS ══ --}}
    @if($riscosDetalhado->count() > 0)
    <div class="secao-titulo">Panorama de Riscos Estratégicos</div>

    <table class="kpi-grid" style="margin-bottom:10px;">
        <tr>
            @foreach(['Crítico' => ['c' => '#dc2626', 'b' => 'danger'], 'Alto' => ['c' => '#f97316', 'b' => 'warning'], 'Médio' => ['c' => '#eab308', 'b' => 'warning'], 'Baixo' => ['c' => '#65a30d', 'b' => 'success']] as $nivel => $cfgR)
            <td class="kpi-card {{ $cfgR['b'] }}" style="width:25%; border-top-color:{{ $cfgR['c'] }};">
                <p class="kpi-label">{{ $nivel }}</p>
                <p class="kpi-value" style="color:{{ $cfgR['c'] }}; font-size:20px;">{{ $riscosSummary[$nivel] ?? 0 }}</p>
                <p class="kpi-sub">risco(s)</p>
            </td>
            @endforeach
        </tr>
    </table>

    <table class="rpt">
        <thead>
            <tr>
                <th style="width:5%; text-align:center;">Cód.</th>
                <th style="width:32%;">Risco</th>
                <th>Categoria</th>
                <th class="text-center" style="width:40px;">P</th>
                <th class="text-center" style="width:40px;">I</th>
                <th class="text-center" style="width:48px;">P×I</th>
                <th class="text-center" style="width:64px;">Nível</th>
                <th class="text-center" style="width:52px;">Mitig.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riscosDetalhado as $risco)
            @php
                $nv     = $risco->num_nivel_risco;
                $lbl    = $risco->getNivelRiscoLabel();
                $corR   = $risco->getNivelRiscoCor();
                $pillR  = $nv >= 16 ? 'pill-danger' : ($nv >= 10 ? 'pill-warning' : ($nv >= 5 ? 'pill-warning' : 'pill-success'));
            @endphp
            <tr>
                <td class="text-center" style="font-family:monospace; font-size:8px; color:#718096;">R-{{ str_pad($risco->num_codigo_risco, 3, '0', STR_PAD_LEFT) }}</td>
                <td class="row-titulo">{{ $risco->dsc_titulo }}</td>
                <td class="row-desc">{{ $risco->dsc_categoria }}</td>
                <td class="text-center">{{ $risco->num_probabilidade }}</td>
                <td class="text-center">{{ $risco->num_impacto }}</td>
                <td class="text-center" style="font-weight:bold; color:{{ $corR }};">{{ $nv }}</td>
                <td class="text-center"><span class="pill {{ $pillR }}">{{ $lbl }}</span></td>
                <td class="text-center">
                    @if($risco->mitigacoes->count() > 0)
                        <span class="pill pill-info">{{ $risco->mitigacoes->count() }}</span>
                    @else
                        <span style="color:#cbd5e0; font-size:8px;">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center" style="color:#a0aec0; font-style:italic;">Nenhum risco registrado.</td></tr>
            @endforelse
        </tbody>
    </table>
    @endif

    @if($aiTrends)
    <div class="ai-box" style="border-left-color:#6a4c9c;">
        <strong style="color:#6a4c9c; font-size:9px;">&#9733; ANÁLISE PREDITIVA (IA)</strong>
        <p>{!! nl2br(e($aiTrends)) !!}</p>
    </div>
    @endif
</body>
</html>
