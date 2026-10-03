<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mapa Estratégico — {{ $organizacao->nom_organizacao }}</title>
    {{-- Orientação vai por parâmetro. Este arquivo incluía o sistema de design
         E redefinia o @page logo depois — o <style> posterior vencia, e o
         cabeçalho fixo, posicionado para uma margem, era desenhado sobre outra.
         Sobreposição silenciosa: não dava erro, só saía torto. --}}
    @include('relatorios.partials.estilos', ['orientacao' => 'landscape'])
    <style>

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

    @php
        $coresNivel = [1 => '#475569', 2 => '#2e8b57', 3 => '#0891b2', 4 => '#d97706', 5 => '#1B408E'];
    @endphp

    @php
        $temMissao = trim((string) ($identidade->dsc_missao ?? '')) !== '';
        $temVisao  = trim((string) ($identidade->dsc_visao ?? '')) !== '';
    @endphp

    {{-- Missão / Visão — cada uma só aparece se estiver preenchida --}}
    @if($temMissao || $temVisao)
    <table style="width:100%; border-collapse:separate; border-spacing:6px 0; margin-bottom:6px;">
        <tr>
            @if($temMissao)
            <td style="width:{{ $temVisao ? '50%' : '100%' }};">
                <div class="id-card-l" style="border-left-color:#EDC009;">
                    <span class="id-label" style="color:#8A7200;">Missão</span>
                    <div class="id-text">{{ $identidade->dsc_missao }}</div>
                </div>
            </td>
            @endif
            @if($temVisao)
            <td style="width:{{ $temMissao ? '50%' : '100%' }};">
                <div class="id-card-r" style="border-left-color:#3550A0;">
                    <span class="id-label" style="color:#3550A0;">Visão</span>
                    <div class="id-text">{{ $identidade->dsc_visao }}</div>
                </div>
            </td>
            @endif
        </tr>
    </table>
    @endif

    {{-- Valores · Temas Norteadores · Legenda --}}
    <table style="width:100%; border-collapse:separate; border-spacing:6px 0; margin-bottom:10px;">
        <tr>
            @if($valores->isNotEmpty())
            <td style="background:#fff; border:1px solid #e2e8f0; border-radius:7px; padding:7px 12px; text-align:center; vertical-align:middle;">
                <div style="color:#5a6577; font-weight:bold; font-size:7px; text-transform:uppercase; margin-bottom:4px; letter-spacing:.5px;">Valores Institucionais</div>
                @foreach($valores as $valor)
                    <span class="chip-sm">{{ $valor->nom_valor }}</span>
                @endforeach
            </td>
            @endif
            @if($temasNorteadores->isNotEmpty())
            <td style="background:#fff; border:1px solid #e2e8f0; border-radius:7px; padding:7px 12px; text-align:center; vertical-align:middle;">
                <div style="color:#5a6577; font-weight:bold; font-size:7px; text-transform:uppercase; margin-bottom:4px; letter-spacing:.5px;">Temas Norteadores</div>
                @foreach($temasNorteadores as $t)
                    <span class="chip-sm" style="background:#fff8e1; color:#9a5408; border-color:#fde68a;">{{ $t->nom_tema_norteador }}</span>
                @endforeach
            </td>
            @endif
            <td style="background:#F4FBF3; border:1px solid #D3EED1; padding:7px 12px; vertical-align:middle;">
                @if($grausSatisfacao->isNotEmpty())
                    <div style="color:#595959; font-weight:bold; font-size:7px; text-transform:uppercase; margin-bottom:4px; letter-spacing:.5px;">Legenda de Atingimento</div>
                    @foreach($grausSatisfacao as $grau)
                        <span style="font-size:7.5px; margin-right:8px; white-space:nowrap;">
                            <span class="farol" style="background:{{ $grau->cor }};"></span>
                            {{ $grau->dsc_grau_satisfacao }}
                            ({{ number_format((float) $grau->vlr_minimo, 0) }}–{{ number_format((float) $grau->vlr_maximo, 0) }}%)
                        </span>
                    @endforeach
                @else
                    <div style="font-size:7.5px; color:#595959;">
                        <strong>Sem graus de satisfação configurados neste ciclo.</strong>
                        Os percentuais aparecem em cinza: a organização ainda não definiu a
                        partir de que valor um resultado é bom, regular ou crítico.
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Swimlanes BSC.

         🔴 Regra do gestor: parte que o cliente não preencheu não aparece.
         Uma faixa colorida com "0 objetivo(s)" e a frase "sem objetivos
         vinculados" ocupa espaço para não informar nada.

         O que NÃO se esconde é o fato: a nota ao final diz quantas camadas
         ficaram de fora. Omitir sem avisar faria o mapa parecer completo. --}}
    @php
        $ordenadas = $perspectivas->sortByDesc('num_nivel_hierarquico_apresentacao');
        $comObjetivos = $ordenadas->filter(fn ($x) => $x->objetivos->isNotEmpty());
        $omitidas = $ordenadas->count() - $comObjetivos->count();
    @endphp

    @forelse($comObjetivos as $persp)
        @php $corP = $coresNivel[$persp->num_nivel_hierarquico_apresentacao] ?? '#1B408E'; @endphp
        <div class="persp-row">
            <div class="persp-header" style="background:{{ $corP }};">
                {{ $persp->dsc_perspectiva }}
                <span style="float:right; background:rgba(255,255,255,.2); border-radius:10px; padding:1px 8px; font-size:8px;">
                    {{ $persp->objetivos->count() }} objetivo(s)
                </span>
            </div>
            <div class="persp-body">
                @foreach($persp->objetivos as $obj)
                    @php
                        $at  = $obj->atingimento_calculado ?? 0;
                        $cor = $getCorSatisfacao($at);
                    @endphp
                    <div class="obj-card" style="border-left:3px solid {{ $cor }};">
                        <span class="obj-title">{{ $obj->nom_objetivo }}</span>
                        <div style="font-size:7px; color:#5a6577;">
                            <span class="farol" style="background:{{ $cor }}; width:8px; height:8px;"></span>
                            <strong style="color:{{ \App\Support\CorLegivel::paraTextoSobreBranco($cor) }};">{{ number_format($at, 1, ',', '.') }}%</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="vazio">
            @if($perspectivas->isEmpty())
                Nenhuma perspectiva cadastrada para este ciclo PEI.
            @else
                As {{ $perspectivas->count() }} perspectivas deste ciclo ainda não têm objetivos vinculados.
            @endif
        </div>
    @endforelse

    @if($omitidas > 0 && $comObjetivos->isNotEmpty())
        <p style="font-size:7.5px; color:#595959; margin-top:8px;">
            {{ $omitidas }} {{ $omitidas == 1 ? 'perspectiva deste ciclo ainda não tem' : 'perspectivas deste ciclo ainda não têm' }}
            objetivo vinculado e não {{ $omitidas == 1 ? 'foi exibida' : 'foram exibidas' }} acima.
        </p>
    @endif
</body>
</html>
