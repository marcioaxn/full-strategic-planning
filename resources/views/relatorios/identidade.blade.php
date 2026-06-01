<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mapa Estratégico — {{ $organizacao->nom_organizacao }}</title>
    @include('relatorios.partials.estilos')
    <style>
        /* ── Orientação Paisagem ── */
        @page { size: a4 landscape; margin: 95px 28px 52px 28px; }
        .rpt-header { top: -78px; height: 70px; }

        /* ── Swimlanes BSC ── */
        .persp-row { margin-bottom: 7px; border: 1px solid #e2e8f0; border-radius: 7px; overflow: hidden; page-break-inside: avoid; }
        .persp-header { color: #fff; padding: 5px 12px; font-weight: bold; text-transform: uppercase; font-size: 9px; letter-spacing: .5px; }
        .persp-body { padding: 6px; background: #fdfdfd; }
        .obj-card {
            display: inline-block; width: 18%; vertical-align: top;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 5px;
            padding: 6px; margin: 2px; min-height: 42px;
        }
        .obj-title { font-weight: bold; font-size: 7.5px; color: #2d3748; display: block; line-height: 1.2; margin-bottom: 3px; }

        /* ── Cards de Identidade ── */
        .id-card-l { background: #fff; border: 1px solid #e2e8f0; border-left: 4px solid #1B408E; border-radius: 7px; padding: 8px 12px; }
        .id-card-r { background: #fff; border: 1px solid #e2e8f0; border-left: 4px solid #e07b39; border-radius: 7px; padding: 8px 12px; }
        .id-label  { font-weight: bold; font-size: 7.5px; text-transform: uppercase; letter-spacing: .5px; display: block; margin-bottom: 4px; }
        .id-text   { font-style: italic; font-size: 8.5px; line-height: 1.4; color: #2d3748; }
        .chip-sm { display: inline-block; background: #eef2f9; color: #1B408E; border: 1px solid #c7d6ec; border-radius: 999px; padding: 3px 9px; margin: 2px; font-size: 7.5px; font-weight: bold; }
    </style>
</head>
<body>
    @include('relatorios.partials.cabecalho', [
        'rptTitulo'    => 'Mapa Estratégico',
        'rptEyebrow'   => 'Balanced Scorecard · Módulo 02 — Planejar',
        'rptSubtitulo' => $organizacao->nom_organizacao . ' · Exercício ' . $filtros['ano'],
        'rptIcon'      => '&#9737;',
    ])
    @include('relatorios.partials.rodape')

    @php
        $coresNivel = [1 => '#475569', 2 => '#2e8b57', 3 => '#0891b2', 4 => '#d97706', 5 => '#1B408E'];
    @endphp

    {{-- Missão / Visão --}}
    <table style="width:100%; border-collapse:separate; border-spacing:6px 0; margin-bottom:6px;">
        <tr>
            <td style="width:50%;">
                <div class="id-card-l">
                    <span class="id-label" style="color:#1B408E;">Missão</span>
                    <div class="id-text">{{ $identidade->dsc_missao ?? 'Não definida' }}</div>
                </div>
            </td>
            <td style="width:50%;">
                <div class="id-card-r">
                    <span class="id-label" style="color:#e07b39;">Visão</span>
                    <div class="id-text">{{ $identidade->dsc_visao ?? 'Não definida' }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Valores · Temas Norteadores · Legenda --}}
    <table style="width:100%; border-collapse:separate; border-spacing:6px 0; margin-bottom:10px;">
        <tr>
            @if($valores->isNotEmpty())
            <td style="background:#fff; border:1px solid #e2e8f0; border-radius:7px; padding:7px 12px; text-align:center; vertical-align:middle;">
                <div style="color:#718096; font-weight:bold; font-size:7px; text-transform:uppercase; margin-bottom:4px; letter-spacing:.5px;">Valores Institucionais</div>
                @foreach($valores as $valor)
                    <span class="chip-sm">{{ $valor->nom_valor }}</span>
                @endforeach
            </td>
            @endif
            @if($temasNorteadores->isNotEmpty())
            <td style="background:#fff; border:1px solid #e2e8f0; border-radius:7px; padding:7px 12px; text-align:center; vertical-align:middle;">
                <div style="color:#718096; font-weight:bold; font-size:7px; text-transform:uppercase; margin-bottom:4px; letter-spacing:.5px;">Temas Norteadores</div>
                @foreach($temasNorteadores as $t)
                    <span class="chip-sm" style="background:#fff8e1; color:#d97706; border-color:#fde68a;">{{ $t->nom_tema_norteador }}</span>
                @endforeach
            </td>
            @endif
            <td style="background:#f7fafc; border:1px solid #e2e8f0; border-radius:7px; padding:7px 12px; vertical-align:middle;">
                <div style="color:#718096; font-weight:bold; font-size:7px; text-transform:uppercase; margin-bottom:4px; letter-spacing:.5px;">Legenda de Atingimento</div>
                @foreach($grausSatisfacao as $grau)
                    <span style="font-size:7.5px; margin-right:8px; white-space:nowrap;">
                        <span class="farol" style="background:{{ $grau->cor }};"></span>
                        {{ $grau->dsc_grau_satisfcao ?? $grau->dsc_grau_satisfacao ?? '' }}
                        ({{ number_format($grau->vlr_minimo, 0) }}–{{ number_format($grau->vlr_maximo, 0) }}%)
                    </span>
                @endforeach
            </td>
        </tr>
    </table>

    {{-- Swimlanes BSC --}}
    @forelse($perspectivas->sortByDesc('num_nivel_hierarquico_apresentacao') as $persp)
        @php $corP = $coresNivel[$persp->num_nivel_hierarquico_apresentacao] ?? '#1B408E'; @endphp
        <div class="persp-row">
            <div class="persp-header" style="background:{{ $corP }};">
                {{ $persp->dsc_perspectiva }}
                <span style="float:right; background:rgba(255,255,255,.2); border-radius:10px; padding:1px 8px; font-size:8px;">
                    {{ $persp->objetivos->count() }} objetivo(s)
                </span>
            </div>
            <div class="persp-body">
                @forelse($persp->objetivos as $obj)
                    @php
                        $at  = $obj->atingimento_calculado ?? 0;
                        $cor = $getCorSatisfacao($at);
                    @endphp
                    <div class="obj-card" style="border-left:3px solid {{ $cor }};">
                        <span class="obj-title">{{ $obj->nom_objetivo }}</span>
                        <div style="font-size:7px; color:#718096;">
                            <span class="farol" style="background:{{ $cor }}; width:8px; height:8px;"></span>
                            <strong style="color:{{ $cor }};">{{ number_format($at, 1, ',', '.') }}%</strong>
                        </div>
                    </div>
                @empty
                    <span style="font-style:italic; color:#a0aec0; font-size:8px; padding:8px; display:block;">Sem objetivos vinculados nesta perspectiva.</span>
                @endforelse
            </div>
        </div>
    @empty
        <div class="vazio">Nenhuma perspectiva cadastrada para este ciclo PEI.</div>
    @endforelse
</body>
</html>
