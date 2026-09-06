<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Objetivos Estratégicos (BSC)</title>
    @include('relatorios.partials.estilos', ['orientacao' => 'portrait'])
</head>
<body>

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

        {{-- Objetivos por Perspectiva.

             🔴 Regra do gestor: "se o relatório vai mostrar uma parte e o
             cliente ainda não preencheu, não é necessário mostrar". Uma faixa
             escura com o nome da perspectiva, seguida de uma tabela dizendo
             "nenhum objetivo nesta perspectiva", ocupa meia página para não
             informar nada. --}}
        @php $perspectivasComObjetivos = $perspectivas->filter(fn ($x) => $x->objetivos->isNotEmpty()); @endphp

        @forelse($perspectivasComObjetivos as $p)
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
                    @foreach($p->objetivos->sortBy('num_nivel_hierarquico_apresentacao') as $obj)
                    <tr>
                        <td class="text-center" style="font-weight:bold; color:#1B408E;">{{ $obj->num_nivel_hierarquico_apresentacao }}</td>
                        <td class="row-titulo">{{ $obj->nom_objetivo }}</td>
                        <td class="row-desc">{{ $obj->dsc_objetivo ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @empty
            {{-- Só aqui — quando NADA foi preenchido — o relatório fala do vazio.
                 Documento inteiramente em branco é pior do que a frase. --}}
            <div class="vazio">
                Nenhum objetivo estratégico foi cadastrado neste ciclo.
                @if($perspectivas->isNotEmpty())
                    As {{ $perspectivas->count() }} perspectivas já existem e aguardam o desdobramento em objetivos.
                @endif
            </div>
        @endforelse

        @if($perspectivas->isEmpty())
            <div class="vazio">Nenhuma perspectiva cadastrada para este ciclo PEI.</div>
        @endif
    </main>
</body>
</html>
