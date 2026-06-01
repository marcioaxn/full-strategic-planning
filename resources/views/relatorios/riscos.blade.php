<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gestão de Riscos</title>
    @include('relatorios.partials.estilos')
    <style>
        .matriz { border-collapse: collapse; margin: 4px auto; }
        .matriz td { width: 44px; height: 36px; text-align: center; vertical-align: middle; border: 2px solid #fff; font-weight: bold; font-size: 10px; color: #fff; border-radius: 4px; }
        .matriz .axis   { background: transparent; color: #718096; font-size: 7.5px; font-weight: bold; width: 20px; }
        .matriz .corner { background: transparent; }
        .gz-baixo   { background: #65a30d; }
        .gz-medio   { background: #eab308; }
        .gz-alto    { background: #f97316; }
        .gz-critico { background: #dc2626; }
    </style>
</head>
<body>
    @include('relatorios.partials.cabecalho', [
        'rptTitulo'    => 'Gestão de Riscos',
        'rptEyebrow'   => 'Matriz de Riscos · Módulo 02 — Planejar',
        'rptSubtitulo' => $organizacao ? $organizacao->nom_organizacao : 'Todas as Unidades',
        'rptIcon'      => '&#9888;',
    ])
    @include('relatorios.partials.rodape')

    <main>
        <div class="rpt-filtros">
            <span><strong>Unidade:</strong> {{ $organizacao ? $organizacao->nom_organizacao : 'Todas' }}</span>
            <span><strong>Referência:</strong> {{ now()->format('d/m/Y') }}</span>
            <span><strong>Total de Riscos:</strong> {{ $riscos->count() }}</span>
        </div>

        @php
            $nivel    = fn($r) => $r->num_probabilidade * $r->num_impacto;
            $criticos = $riscos->filter(fn($r) => $r->num_nivel_risco >= 16)->count();
            $altos    = $riscos->filter(fn($r) => $r->num_nivel_risco >= 10 && $r->num_nivel_risco < 16)->count();
            $medios   = $riscos->filter(fn($r) => $r->num_nivel_risco >= 5  && $r->num_nivel_risco < 10)->count();
            $baixos   = $riscos->filter(fn($r) => $r->num_nivel_risco < 5)->count();
            $comMitig = $riscos->filter(fn($r) => $r->mitigacoes->count() > 0)->count();
            $comOcorr = $riscos->filter(fn($r) => $r->ocorrencias->count() > 0)->count();
        @endphp

        {{-- KPIs --}}
        <table class="kpi-grid">
            <tr>
                <td class="kpi-card danger" style="width:20%;">
                    <p class="kpi-label">Críticos</p>
                    <p class="kpi-value" style="color:#dc2626;">{{ $criticos }}</p>
                    <p class="kpi-sub">nível &ge; 16</p>
                </td>
                <td class="kpi-card warning" style="width:20%;">
                    <p class="kpi-label">Altos</p>
                    <p class="kpi-value" style="color:#f97316;">{{ $altos }}</p>
                    <p class="kpi-sub">nível 10–15</p>
                </td>
                <td class="kpi-card" style="width:20%;">
                    <p class="kpi-label">Médios + Baixos</p>
                    <p class="kpi-value">{{ $medios + $baixos }}</p>
                    <p class="kpi-sub">{{ $medios }} médios · {{ $baixos }} baixos</p>
                </td>
                <td class="kpi-card success" style="width:20%;">
                    <p class="kpi-label">Com Mitigação</p>
                    <p class="kpi-value" style="color:#2e8b57;">{{ $comMitig }}</p>
                    <p class="kpi-sub">de {{ $riscos->count() }} riscos</p>
                </td>
                <td class="kpi-card accent" style="width:20%;">
                    <p class="kpi-label">Com Ocorrências</p>
                    <p class="kpi-value">{{ $comOcorr }}</p>
                    <p class="kpi-sub">registradas</p>
                </td>
            </tr>
        </table>

        {{-- Matriz de Calor + Classificação --}}
        <table style="width:100%; margin-bottom:8px;"><tr>
            <td style="width:50%; vertical-align:top;">
                <div class="secao-titulo" style="margin-top:0;">Matriz de Probabilidade × Impacto</div>
                @php
                    $celulas = [];
                    foreach ($riscos as $r) {
                        $celulas[$r->num_impacto][$r->num_probabilidade] = ($celulas[$r->num_impacto][$r->num_probabilidade] ?? 0) + 1;
                    }
                    $classeGut = function($p, $i) {
                        $v = $p * $i;
                        return $v >= 16 ? 'gz-critico' : ($v >= 10 ? 'gz-alto' : ($v >= 5 ? 'gz-medio' : 'gz-baixo'));
                    };
                @endphp
                <table class="matriz">
                    @for($i = 5; $i >= 1; $i--)
                    <tr>
                        <td class="axis">{{ $i }}</td>
                        @for($p = 1; $p <= 5; $p++)
                            <td class="{{ $classeGut($p, $i) }}">{{ $celulas[$i][$p] ?? '' }}</td>
                        @endfor
                    </tr>
                    @endfor
                    <tr>
                        <td class="corner"></td>
                        @for($p = 1; $p <= 5; $p++)<td class="axis">{{ $p }}</td>@endfor
                    </tr>
                </table>
                <div style="text-align:center; font-size:7.5px; color:#a0aec0; margin-top:2px;">
                    Eixo vertical: Impacto &nbsp;|&nbsp; Eixo horizontal: Probabilidade
                </div>
            </td>
            <td style="width:50%; vertical-align:top; padding-left:14px;">
                <div class="secao-titulo" style="margin-top:0;">Classificação e Distribuição</div>
                <table class="rpt bordered">
                    <tbody>
                        <tr>
                            <td><span class="farol" style="background:#dc2626;"></span> <strong>Crítico</strong></td>
                            <td class="text-end" style="font-size:8px; color:#718096;">P×I &ge; 16</td>
                            <td class="text-center" style="font-weight:bold; color:#dc2626;">{{ $criticos }}</td>
                        </tr>
                        <tr>
                            <td><span class="farol" style="background:#f97316;"></span> <strong>Alto</strong></td>
                            <td class="text-end" style="font-size:8px; color:#718096;">10 – 15</td>
                            <td class="text-center" style="font-weight:bold; color:#f97316;">{{ $altos }}</td>
                        </tr>
                        <tr>
                            <td><span class="farol" style="background:#eab308;"></span> <strong>Médio</strong></td>
                            <td class="text-end" style="font-size:8px; color:#718096;">5 – 9</td>
                            <td class="text-center" style="font-weight:bold; color:#eab308;">{{ $medios }}</td>
                        </tr>
                        <tr>
                            <td><span class="farol" style="background:#65a30d;"></span> <strong>Baixo</strong></td>
                            <td class="text-end" style="font-size:8px; color:#718096;">&lt; 5</td>
                            <td class="text-center" style="font-weight:bold; color:#65a30d;">{{ $baixos }}</td>
                        </tr>
                    </tbody>
                </table>
                <p style="font-size:7.5px; color:#a0aec0; margin-top:6px;">
                    Nível = Probabilidade × Impacto (escala 1–5 cada).
                    Riscos críticos exigem plano de mitigação imediato.
                </p>
                @if($riscos->count() > 0 && $comMitig < $riscos->count())
                <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:6px; padding:8px 10px; font-size:8px; color:#92400e; margin-top:6px;">
                    <strong>Atenção:</strong> {{ $riscos->count() - $comMitig }} risco(s) ainda sem plano de mitigação definido.
                </div>
                @endif
            </td>
        </tr></table>

        {{-- Riscos Priorizados --}}
        <div class="secao-titulo">Riscos Priorizados</div>
        <table class="rpt">
            <thead>
                <tr>
                    <th style="width:5%;">Cód.</th>
                    <th style="width:22%;">Risco</th>
                    <th style="width:22%;">Descrição</th>
                    <th class="text-center" style="width:26px;">P</th>
                    <th class="text-center" style="width:26px;">I</th>
                    <th class="text-center" style="width:38px;">Nível</th>
                    <th class="text-center" style="width:62px;">Classe</th>
                    <th class="text-center" style="width:42px;">Mitig.</th>
                    <th class="text-center" style="width:42px;">Ocorr.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riscos as $risco)
                @php
                    $nv   = $risco->num_nivel_risco;
                    $lbl  = $risco->getNivelRiscoLabel();
                    $corR = $risco->getNivelRiscoCor();
                    $clsR = $nv >= 16 ? 'pill-danger' : ($nv >= 10 ? 'pill-warning' : ($nv >= 5 ? 'pill-warning' : 'pill-success'));
                @endphp
                <tr>
                    <td style="text-align:center; font-family:monospace; font-size:8px; color:#718096;">R-{{ str_pad($risco->num_codigo_risco, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="row-titulo">{{ $risco->dsc_titulo }}</td>
                    <td class="row-desc">{{ Str::limit($risco->txt_descricao ?? '', 70) ?: '—' }}</td>
                    <td class="text-center">{{ $risco->num_probabilidade }}</td>
                    <td class="text-center">{{ $risco->num_impacto }}</td>
                    <td class="text-center" style="font-weight:bold; color:{{ $corR }};">{{ $nv }}</td>
                    <td class="text-center"><span class="pill {{ $clsR }}">{{ $lbl }}</span></td>
                    <td class="text-center">
                        @if($risco->mitigacoes->count() > 0)
                            <span class="pill pill-info">{{ $risco->mitigacoes->count() }}</span>
                        @else
                            <span style="color:#cbd5e0; font-size:7.5px;">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($risco->ocorrencias->count() > 0)
                            <span class="pill pill-warning">{{ $risco->ocorrencias->count() }}</span>
                        @else
                            <span style="color:#cbd5e0; font-size:7.5px;">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="vazio mb-0">Nenhum risco cadastrado para os filtros selecionados.</div></td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- Detalhamento das Mitigações (se houver) --}}
        @php $riscosComMitig = $riscos->filter(fn($r) => $r->mitigacoes->count() > 0); @endphp
        @if($riscosComMitig->isNotEmpty())
        <div class="secao-titulo">Planos de Mitigação Detalhados</div>
        @foreach($riscosComMitig as $risco)
        <div style="margin-bottom:10px; page-break-inside:avoid;">
            <div style="background:#f7fafc; border:1px solid #e2e8f0; border-left:4px solid {{ $risco->getNivelRiscoCor() }}; border-radius:6px; padding:8px 12px; margin-bottom:4px;">
                <strong style="font-size:9px; color:#1a3a5c;">R-{{ str_pad($risco->num_codigo_risco, 3, '0', STR_PAD_LEFT) }} · {{ $risco->dsc_titulo }}</strong>
                <span class="pill {{ $risco->num_nivel_risco >= 16 ? 'pill-danger' : ($risco->num_nivel_risco >= 10 ? 'pill-warning' : 'pill-success') }}" style="float:right;">{{ $risco->getNivelRiscoLabel() }} (P×I={{ $risco->num_nivel_risco }})</span>
            </div>
            <table class="rpt bordered" style="margin:0;">
                <thead>
                    <tr>
                        <th style="width:40%;">Ação de Mitigação</th>
                        <th style="width:15%; text-align:center;">Tipo</th>
                        <th style="width:15%; text-align:center;">Status</th>
                        <th style="width:15%; text-align:center;">Prazo</th>
                        <th>Responsável</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($risco->mitigacoes as $mit)
                    <tr>
                        <td class="row-desc">{{ Str::limit($mit->txt_descricao ?? '—', 80) }}</td>
                        <td class="text-center" style="font-size:8px;">{{ $mit->dsc_tipo ?? '—' }}</td>
                        <td class="text-center">
                            @php $stM = $mit->dsc_status ?? ''; @endphp
                            @if($stM)
                                <span class="pill {{ $stM === 'Concluído' ? 'pill-success' : ($stM === 'Em Andamento' ? 'pill-info' : 'pill-neutral') }}" style="font-size:7.5px;">{{ $stM }}</span>
                            @else
                                <span style="color:#cbd5e0;">—</span>
                            @endif
                        </td>
                        <td class="text-center" style="font-size:8px;">{{ $mit->dte_prazo?->format('d/m/Y') ?? '—' }}</td>
                        <td class="row-desc">{{ $mit->responsavel?->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
        @endif
    </main>
</body>
</html>
