<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Indicadores de Desempenho (KPIs)</title>
    @include('relatorios.partials.estilos')
</head>
<body>
    @include('relatorios.partials.cabecalho', [
        'rptTitulo'    => 'Indicadores de Desempenho',
        'rptEyebrow'   => 'KPIs — Módulo 03 — Monitorar e Avaliar',
        'rptSubtitulo' => $organizacao ? $organizacao->nom_organizacao : 'Todas as Unidades',
        'rptIcon'      => '&#9650;',
    ])
    @include('relatorios.partials.rodape')

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

            $verde    = $mensuraveis->filter(fn($i) => $i->calcularAtingimento() >= 80)->count();
            $amarelo  = $mensuraveis->filter(fn($i) => $i->calcularAtingimento() >= 50 && $i->calcularAtingimento() < 80)->count();
            $vermelho = $mensuraveis->filter(fn($i) => $i->calcularAtingimento() < 50)->count();

            // Agrupar por perspectiva → objective
            $porPerspectiva = $indicadores->groupBy(function($i) {
                return $i->objetivo?->perspectiva?->dsc_perspectiva ?? 'Indicadores de Planos / Sem Perspectiva';
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
                <td class="kpi-card success" style="width:25%;">
                    <p class="kpi-label">Dentro da Meta</p>
                    <p class="kpi-value" style="color:#2e8b57;">{{ $verde }}</p>
                    <p class="kpi-sub">&ge; 80% de atingimento</p>
                </td>
                <td class="kpi-card danger" style="width:25%;">
                    <p class="kpi-label">Atenção / Crítico</p>
                    <p class="kpi-value" style="color:#dc3545;">{{ $amarelo + $vermelho }}</p>
                    <p class="kpi-sub">{{ $amarelo }} atenção · {{ $vermelho }} crítico</p>
                </td>
            </tr>
        </table>

        {{-- Legenda --}}
        <div style="margin-bottom:14px; font-size:8px; color:#718096; background:#f7fafc; padding:6px 10px; border-radius:6px; border:1px solid #e2e8f0;">
            <strong style="color:#1a3a5c;">Legenda de Atingimento:</strong>
            <span style="margin-left:12px;"><span class="farol" style="background:#2e8b57;"></span> Bom (&ge;80%)</span>
            <span style="margin-left:12px;"><span class="farol" style="background:#d97706;"></span> Atenção (50–79%)</span>
            <span style="margin-left:12px;"><span class="farol" style="background:#dc3545;"></span> Crítico (&lt;50%)</span>
            <span style="margin-left:12px;"><span class="farol" style="background:#a0aec0;"></span> Não aplicável</span>
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
                        $cor = $na ? '#a0aec0' : ($ind->getCorFarol() ?: '#cbd5e0');
                        $ult = $ind->getUltimaEvolucao();
                        $metaTxt = $ind->dsc_meta ?: '—';
                    @endphp
                    <tr>
                        <td class="row-titulo">{{ $ind->nom_indicador }}</td>
                        <td class="row-desc">
                            @if($ind->cod_objetivo)
                                {{ Str::limit($ind->objetivo?->nom_objetivo ?? '—', 40) }}
                            @elseif($ind->planoDeAcao)
                                <span class="pill pill-neutral" style="font-size:7px;">Plano</span>
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
