<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Objetivos Estratégicos (BSC)</title>
    @include('relatorios.partials.estilos')
</head>
<body>
    @include('relatorios.partials.cabecalho', [
        'rptTitulo'    => 'Objetivos Estratégicos',
        'rptEyebrow'   => 'Balanced Scorecard · Módulo 02 — Planejar',
        'rptSubtitulo' => $pei->dsc_pei . ' (' . $pei->num_ano_inicio_pei . '–' . $pei->num_ano_fim_pei . ')',
        'rptIcon'      => '&#9737;',
    ])
    @include('relatorios.partials.rodape')

    <main>
        {{-- Filtros --}}
        @if(isset($filtros))
        <div class="rpt-filtros">
            <span><strong>Ano:</strong> {{ $filtros['ano'] }}</span>
            <span><strong>Perspectiva:</strong> {{ $filtros['perspectiva'] }}</span>
            <span><strong>Unidade:</strong> {{ $filtros['organizacao'] }}</span>
        </div>
        @endif

        @php
            $totalObjetivos = $perspectivas->sum(fn($p) => $p->objetivos->count());
            $perspComObj = $perspectivas->filter(fn($p) => $p->objetivos->count() > 0)->count();
        @endphp

        {{-- KPIs --}}
        <table class="kpi-grid">
            <tr>
                <td class="kpi-card" style="width:33%;">
                    <p class="kpi-label">Perspectivas BSC</p>
                    <p class="kpi-value">{{ $perspectivas->count() }}</p>
                    <p class="kpi-sub">{{ $perspComObj }} com objetivos definidos</p>
                </td>
                <td class="kpi-card accent" style="width:33%;">
                    <p class="kpi-label">Objetivos Estratégicos</p>
                    <p class="kpi-value">{{ $totalObjetivos }}</p>
                    <p class="kpi-sub">distribuídos nas perspectivas</p>
                </td>
                <td class="kpi-card success" style="width:34%;">
                    <p class="kpi-label">Média por Perspectiva</p>
                    <p class="kpi-value">{{ $perspectivas->count() > 0 ? number_format($totalObjetivos / $perspectivas->count(), 1, ',', '.') : '0' }}</p>
                    <p class="kpi-sub">objetivos por perspectiva</p>
                </td>
            </tr>
        </table>

        {{-- Objetivos por Perspectiva --}}
        @foreach($perspectivas as $p)
        <div class="avoid-break">
            <div class="grupo-band">
                {{ $p->dsc_perspectiva }}
                <span class="contador">{{ $p->objetivos->count() }} {{ $p->objetivos->count() == 1 ? 'objetivo' : 'objetivos' }}</span>
            </div>
            <table class="rpt bordered">
                <thead>
                    <tr>
                        <th style="width:40px; text-align:center;">Nº</th>
                        <th style="width:32%;">Objetivo</th>
                        <th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($p->objetivos->sortBy('num_nivel_hierarquico_apresentacao') as $obj)
                    <tr>
                        <td class="text-center" style="font-weight:bold; color:#1B408E;">{{ $obj->num_nivel_hierarquico_apresentacao }}</td>
                        <td class="row-titulo">{{ $obj->nom_objetivo }}</td>
                        <td class="row-desc">{{ $obj->dsc_objetivo ?: '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center" style="color:#a0aec0; padding:14px;">Nenhum objetivo nesta perspectiva.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endforeach

        @if($perspectivas->isEmpty())
            <div class="vazio">Nenhuma perspectiva cadastrada para este ciclo PEI.</div>
        @endif
    </main>
</body>
</html>
