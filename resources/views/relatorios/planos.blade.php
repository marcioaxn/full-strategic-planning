<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Planos de Ação</title>
    @include('relatorios.partials.estilos')
</head>
<body>
    @include('relatorios.partials.cabecalho', [
        'rptTitulo'    => 'Planos de Ação',
        'rptEyebrow'   => 'Carteira de Iniciativas · Módulo 02 — Planejar',
        'rptSubtitulo' => ($organizacao ? $organizacao->nom_organizacao : 'Todas as Unidades') . ' · Exercício ' . $ano,
        'rptIcon'      => '&#10003;',
    ])
    @include('relatorios.partials.rodape')

    <main>
        <div class="rpt-filtros">
            <span><strong>Exercício:</strong> {{ $ano }}</span>
            <span><strong>Unidade:</strong> {{ $organizacao ? $organizacao->nom_organizacao : 'Todas' }}</span>
            <span><strong>Emissão:</strong> {{ now()->format('d/m/Y H:i') }}</span>
        </div>

        {{-- KPIs --}}
        <table class="kpi-grid">
            <tr>
                <td class="kpi-card" style="width:25%;">
                    <p class="kpi-label">Total de Planos</p>
                    <p class="kpi-value">{{ $resumo['total'] }}</p>
                    <p class="kpi-sub">vigentes em {{ $ano }}</p>
                </td>
                <td class="kpi-card accent" style="width:25%;">
                    <p class="kpi-label">Progresso Médio</p>
                    <p class="kpi-value">{{ number_format($resumo['progresso_medio'], 0, ',', '.') }}<span style="font-size:13px;">%</span></p>
                    <p class="kpi-sub">execução no exercício</p>
                </td>
                <td class="kpi-card success" style="width:25%;">
                    <p class="kpi-label">Concluídos</p>
                    <p class="kpi-value" style="color:#2e8b57;">{{ $resumo['concluidos'] }}</p>
                    <p class="kpi-sub">de {{ $resumo['total'] }} planos</p>
                </td>
                <td class="kpi-card danger" style="width:25%;">
                    <p class="kpi-label">Atrasados</p>
                    <p class="kpi-value" style="color:#dc3545;">{{ $resumo['atrasados'] }}</p>
                    <p class="kpi-sub">requerem atenção</p>
                </td>
            </tr>
        </table>

        {{-- Distribuição por status --}}
        <div style="margin-bottom:16px;">
            @php
                $segments = [
                    ['n' => $resumo['concluidos'],   'c' => '#2e8b57', 'l' => 'Concluído'],
                    ['n' => $resumo['andamento'],    'c' => '#1B408E', 'l' => 'Em Andamento'],
                    ['n' => $resumo['atrasados'],    'c' => '#dc3545', 'l' => 'Atrasado'],
                    ['n' => $resumo['nao_iniciado'], 'c' => '#94a3b8', 'l' => 'Não Iniciado'],
                ];
                $tot = max(1, $resumo['total']);
            @endphp
            <div class="progress-track" style="height:14px;">
                @foreach($segments as $s)
                    @if($s['n'] > 0)
                        <div style="float:left; height:14px; width:{{ round($s['n'] / $tot * 100, 1) }}%; background:{{ $s['c'] }};"></div>
                    @endif
                @endforeach
            </div>
            <div style="margin-top:6px; font-size:8px; color:#718096;">
                @foreach($segments as $s)
                    <span style="margin-right:14px;"><span class="farol" style="background:{{ $s['c'] }};"></span> {{ $s['l'] }}: <strong>{{ $s['n'] }}</strong></span>
                @endforeach
            </div>
        </div>

        <div class="secao-titulo">Detalhamento dos Planos</div>
        <table class="rpt">
            <thead>
                <tr>
                    <th style="width:30%;">Plano de Ação</th>
                    <th>Objetivo Vinculado</th>
                    <th class="text-center" style="width:62px;">Vigência</th>
                    <th class="text-center" style="width:78px;">Status</th>
                    <th class="text-center" style="width:90px;">Progresso</th>
                </tr>
            </thead>
            <tbody>
                @forelse($planos as $plano)
                @php
                    $prog = $plano->progresso_anual ?? 0;
                    $st = $plano->status_anual ?? 'Não Iniciado';
                    $stCfg = match($st) {
                        'Concluído'    => ['pill' => 'pill-success', 'bar' => '#2e8b57'],
                        'Em Andamento' => ['pill' => 'pill-info', 'bar' => '#1B408E'],
                        'Atrasado'     => ['pill' => 'pill-danger', 'bar' => '#dc3545'],
                        default        => ['pill' => 'pill-neutral', 'bar' => '#94a3b8'],
                    };
                @endphp
                <tr>
                    <td>
                        <span class="row-titulo">{{ $plano->dsc_plano_de_acao }}</span>
                        @if($plano->tipoExecucao)
                            <span class="pill pill-neutral" style="font-size:7px;">{{ $plano->tipoExecucao->dsc_tipo_execucao }}</span>
                        @endif
                    </td>
                    <td class="row-desc">{{ $plano->objetivo?->nom_objetivo ?? '—' }}</td>
                    <td class="text-center" style="font-size:8px;">
                        {{ $plano->dte_inicio?->format('d/m/y') ?? '—' }}<br>
                        <span style="color:#a0aec0;">a {{ $plano->dte_fim?->format('d/m/y') ?? '—' }}</span>
                    </td>
                    <td class="text-center"><span class="pill {{ $stCfg['pill'] }}">{{ $st }}</span></td>
                    <td>
                        <div style="text-align:center; font-weight:bold; font-size:9px; margin-bottom:3px;">{{ number_format($prog, 0, ',', '.') }}%</div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width:{{ min(100, max(0, $prog)) }}%; background:{{ $stCfg['bar'] }};"></div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5"><div class="vazio mb-0">Nenhum plano de ação vigente em {{ $ano }}.</div></td></tr>
                @endforelse
            </tbody>
        </table>

        @if($resumo['orcamento_total'] > 0)
        <div style="margin-top:14px; padding:10px 14px; background:#f7fafc; border-radius:6px; border-left:3px solid #e07b39; font-size:9px;">
            <strong style="color:#1a3a5c;">Orçamento Previsto Total:</strong>
            R$ {{ number_format($resumo['orcamento_total'], 2, ',', '.') }}
        </div>
        @endif
    </main>
</body>
</html>
