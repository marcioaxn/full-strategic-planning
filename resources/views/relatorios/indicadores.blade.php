<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Indicadores de Desempenho (KPIs)</title>
    @include('relatorios.partials.estilos', ['orientacao' => 'landscape'])
</head>
<body>

    <main>
        @if(isset($filtros))
        <div class="rpt-filtros">
            <span><strong>Ano:</strong> {{ $filtros['ano'] }}</span>
            <span><strong>Período:</strong> {{ $filtros['periodo'] }}</span>
            <span><strong>Unidade:</strong> {{ $filtros['organizacao'] }}</span>
        </div>
        @endif

        @php
            $mensuraveis  = $indicadores->filter(fn($i) => ($i->dsc_polaridade ?? 'Positiva') !== 'Não Aplicável');
            $atings       = $mensuraveis->map(fn($i) => $i->calcularAtingimento())->filter(fn($v) => $v !== null);
            $mediaAting   = $atings->count() > 0 ? $atings->avg() : 0;

            /*
             * 🔴 OS CORTES SÃO DA ORGANIZAÇÃO, NÃO DO RELATÓRIO.
             *
             * Aqui estava escrito: bom = 80%, atenção = 50%, crítico abaixo
             * disso. Números que ninguém mediu, e que divergem da régua que a
             * própria organização configurou em Graus de Satisfação — a mesma
             * que acende o farol do Mapa Estratégico. O MESMO indicador saía
             * "dentro da meta" no relatório e crítico no mapa.
             *
             * A melhor faixa é a de maior valor mínimo. "Dentro da meta" passa
             * a significar "na melhor faixa que a organização definiu", e o
             * cartão diz qual é e de quanto a quanto vai.
             */
            $regua        = ($grausSatisfacao ?? collect())->sortBy('vlr_minimo')->values();
            $temRegua     = $regua->isNotEmpty();
            $melhorFaixa  = $temRegua ? $regua->last() : null;

            $naMelhor = $temRegua
                ? $mensuraveis->filter(fn($i) => $i->calcularAtingimento() >= (float) $melhorFaixa->vlr_minimo)->count()
                : 0;

            $foraDaMelhor = $temRegua ? ($mensuraveis->count() - $naMelhor) : 0;

            // Agrupar por perspectiva → objective
            $porPerspectiva = $indicadores->groupBy(function($i) {
                return $i->objetivo?->perspectiva?->dsc_perspectiva ?? 'Indicadores de Iniciativas / Sem Perspectiva';
            })->sortKeys();
        @endphp

        {{-- KPIs --}}
        <table class="kpi-grid">
            <tr>
                <td class="kpi-card" style="width:25%;">
                    <p class="kpi-label">Total de KPIs</p>
                    <p class="kpi-value">{{ $indicadores->count() }}</p>
                    <p class="kpi-sub">{{ $mensuraveis->count() }} mensuráveis</p>
                </td>
                <td class="kpi-card accent" style="width:25%;">
                    <p class="kpi-label">Atingimento Médio</p>
                    <p class="kpi-value">{{ number_format($mediaAting, 0, ',', '.') }}<span style="font-size:13px;">%</span></p>
                    <p class="kpi-sub">média do período</p>
                </td>
                @if($temRegua)
                <td class="kpi-card success" style="width:25%;">
                    <p class="kpi-label">{{ $melhorFaixa->dsc_grau_satisfacao }}</p>
                    <p class="kpi-value">{{ $naMelhor }}</p>
                    <p class="kpi-sub">a partir de {{ number_format((float) $melhorFaixa->vlr_minimo, 0, ',', '.') }}% de atingimento</p>
                </td>
                <td class="kpi-card warning" style="width:25%;">
                    <p class="kpi-label">Abaixo dessa faixa</p>
                    <p class="kpi-value">{{ $foraDaMelhor }}</p>
                    <p class="kpi-sub">de {{ $mensuraveis->count() }} indicadores mensuráveis</p>
                </td>
                @else
                <td class="kpi-card neutro" style="width:50%;" colspan="2">
                    <p class="kpi-label">Faixas de satisfação</p>
                    <p class="kpi-value" style="font-size:12px;">Não configuradas</p>
                    <p class="kpi-sub">
                        Sem as faixas, este relatório não classifica os indicadores —
                        classificar por conta própria seria emitir um juízo que a organização não emitiu.
                    </p>
                </td>
                @endif
            </tr>
        </table>

        {{-- Legenda: as faixas QUE A ORGANIZAÇÃO definiu, com nome e intervalo.
             A legenda anterior era fixa (80/50) e desmentia a régua real. --}}
        <div class="rpt-filtros" style="margin-bottom:14px;">
            @if($temRegua)
                <strong>Graus de satisfação deste ciclo:</strong>
                @foreach($regua as $faixa)
                    <span style="margin-left:12px;">
                        <span class="farol" style="background:{{ $faixa->cor }};"></span>
                        {{ $faixa->dsc_grau_satisfacao }}
                        ({{ number_format((float) $faixa->vlr_minimo, 0, ',', '.') }}–{{ number_format((float) $faixa->vlr_maximo, 0, ',', '.') }}%)
                    </span>
                @endforeach
            @else
                <strong>Sem graus de satisfação configurados neste ciclo.</strong>
                <span style="margin-left:8px;">
                    Os percentuais aparecem sem cor: a organização ainda não definiu a partir de
                    que valor um resultado é bom, regular ou crítico.
                </span>
            @endif
        </div>

        {{-- Indicadores agrupados por Perspectiva --}}
        @forelse($porPerspectiva as $perspNome => $indsPersp)
        <div class="avoid-break">
            <div class="grupo-band">
                {{ $perspNome }}
                <span class="contador">{{ $indsPersp->count() }} {{ $indsPersp->count() == 1 ? 'indicador' : 'indicadores' }}</span>
            </div>
            <table class="rpt">
                <thead>
                    <tr>
                        <th style="width:28%;">Indicador</th>
                        <th style="width:22%;">Objetivo Vinculado</th>
                        <th style="width:8%;">Unidade</th>
                        <th class="text-center" style="width:8%;">Polaridade</th>
                        <th class="text-end" style="width:8%;">Meta</th>
                        <th class="text-end" style="width:8%;">Realizado</th>
                        <th class="text-center" style="width:18%;">Atingimento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($indsPersp as $ind)
                    @php
                        $na  = ($ind->dsc_polaridade ?? 'Positiva') === 'Não Aplicável';
                        $at  = $na ? null : $ind->calcularAtingimento();
                        // getCorFarol() já trata as pontas e devolve o cinza neutro quando
                        // não há régua — o '?:' anterior sobrescrevia isso com outro cinza.
                        $cor = $na ? '#95969A' : $ind->getCorFarol((int) ($ano ?? date('Y')));
                        $ult = $ind->getUltimaEvolucao();
                        $metaTxt = $ind->dsc_meta ?: '—';
                    @endphp
                    <tr>
                        <td class="row-titulo">{{ $ind->nom_indicador }}</td>
                        <td class="row-desc">
                            @if($ind->cod_objetivo)
                                {{ Str::limit($ind->objetivo?->nom_objetivo ?? '—', 40) }}
                            @elseif($ind->planoDeAcao)
                                <span class="pill pill-neutral" style="font-size:7px;">Iniciativa</span>
                                {{ Str::limit($ind->planoDeAcao->dsc_plano_de_acao ?? '—', 32) }}
                            @else
                                <span style="color:#a0aec0;">—</span>
                            @endif
                        </td>
                        <td style="font-size:8px;">{{ $ind->dsc_unidade_medida }}</td>
                        <td class="text-center" style="font-size:8px;">{{ $ind->dsc_polaridade ?? 'Positiva' }}</td>
                        <td class="text-end" style="font-size:8px; font-weight:bold; color:#1a3a5c;">{{ $metaTxt }}</td>
                        <td class="text-end" style="font-size:8px;">
                            {{ $ult ? number_format($ult->vlr_realizado, 2, ',', '.') : '—' }}
                        </td>
                        <td>
                            @if($na)
                                <div class="text-center" style="color:#a0aec0; font-size:8px;">N/A</div>
                            @else
                                <table style="width:100%; border:none;"><tr style="border:none;">
                                    <td style="border:none; width:12px; padding:0; vertical-align:middle;">
                                        <span class="farol" style="background:{{ $cor }};"></span>
                                    </td>
                                    <td style="border:none; width:50%; vertical-align:middle; padding:0 4px;">
                                        <div class="progress-track">
                                            <div class="progress-fill" style="width:{{ min(100, max(0, $at)) }}%; background:{{ $cor }};"></div>
                                        </div>
                                    </td>
                                    <td style="border:none; text-align:right; vertical-align:middle; font-weight:bold; font-size:9px; color:{{ $cor }}; padding:0; white-space:nowrap;">
                                        {{ number_format($at, 1, ',', '.') }}%
                                    </td>
                                </tr></table>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @empty
        <div class="vazio">Nenhum indicador encontrado para os filtros selecionados.</div>
        @endforelse
    </main>
</body>
</html>
