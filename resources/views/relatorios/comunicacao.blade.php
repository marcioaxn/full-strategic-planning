<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Plano de Comunicação — {{ $organizacao?->nom_organizacao }}</title>
    @include('relatorios.partials.estilos', ['orientacao' => 'portrait'])
</head>
<body>

    @php
        $totalItems  = $planos->sum(fn($p) => $p->comunicacoes->count());
        $totalPlanos = $planos->count();
        $canais      = $planos->flatMap(fn($p) => $p->comunicacoes->pluck('dsc_canal'))->unique()->count();
    @endphp

    <div class="rpt-filtros">
        <span><strong>Organização:</strong> {{ $organizacao?->nom_organizacao ?? 'Todas' }}</span>
        @if($pei)
        <span><strong>Ciclo PEI:</strong> {{ $pei->dsc_pei }}</span>
        @endif
        <span><strong>Emissão:</strong> {{ $data }}</span>
        <span><strong>Referência:</strong> Guia de Projetos MGI, p. 143</span>
    </div>

    {{-- KPIs --}}
    <table class="kpi-grid">
        <tr>
            <td class="kpi-card" style="width:33%;">
                <p class="kpi-label">Iniciativas com Comunicação</p>
                <p class="kpi-value">{{ $totalPlanos }}</p>
                <p class="kpi-sub">iniciativas com plano definido</p>
            </td>
            <td class="kpi-card accent" style="width:33%;">
                <p class="kpi-label">Itens de Comunicação</p>
                <p class="kpi-value">{{ $totalItems }}</p>
                <p class="kpi-sub">mensagens planejadas no total</p>
            </td>
            <td class="kpi-card" style="width:34%; border-top-color:#0891b2;">
                <p class="kpi-label">Canais Distintos</p>
                <p class="kpi-value" style="color:#0e6e85;">{{ $canais }}</p>
                <p class="kpi-sub">meios de comunicação utilizados</p>
            </td>
        </tr>
    </table>

    @forelse($planos as $plano)
    <div style="margin-bottom:16px; page-break-inside:avoid;">
        <div class="grupo-band" style="background:linear-gradient(90deg,#0891b2,#0e7490);">
            {{ $plano->dsc_plano_de_acao }}
            <span class="contador">{{ $plano->comunicacoes->count() }} item(ns)</span>
        </div>
        <table class="rpt bordered" style="margin:0; border-radius:0 0 6px 6px;">
            <thead>
                <tr>
                    <th style="width:18%;">Público-Alvo</th>
                    <th style="width:34%;">Mensagem-Chave</th>
                    <th style="width:13%; text-align:center;">Canal</th>
                    <th style="width:13%; text-align:center;">Frequência</th>
                    <th style="width:22%;">Responsável</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plano->comunicacoes as $com)
                <tr>
                    <td class="row-titulo">{{ $com->nom_publico_alvo }}</td>
                    <td class="row-desc">{{ $com->dsc_mensagem_chave }}</td>
                    <td class="text-center"><span class="pill pill-info">{{ $com->dsc_canal }}</span></td>
                    <td class="text-center" style="font-size:8px;">{{ $com->dsc_frequencia }}</td>
                    <td style="font-size:8px;">{{ $com->nom_responsavel ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @empty
    <div class="vazio">Nenhum item de comunicação cadastrado nas iniciativas desta organização/ciclo PEI.</div>
    @endforelse
</body>
</html>
