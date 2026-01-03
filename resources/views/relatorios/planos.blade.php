<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Planos de Acao</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #1B408E; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #dee2e6; padding: 6px; text-align: left; }
        th { background: #f8f9fa; color: #1B408E; font-size: 10px; text-transform: uppercase; }
        .status { padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .status-concluido { background: #d1e7dd; color: #0f5132; }
        .status-andamento { background: #cfe2ff; color: #084298; }
        .status-atrasado { background: #f8d7da; color: #842029; }
        .status-nao-iniciado { background: #e2e3e5; color: #41464b; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
        .progress-bar { background: #e9ecef; border-radius: 3px; height: 12px; overflow: hidden; }
        .progress-fill { background: #198754; height: 100%; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0; color: #1B408E;">Relatório de Planos de Ação</h2>
        
        <!-- Bloco de Filtros Aplicados -->
        <div style="margin-top: 10px; padding: 8px; background: #f8f9fa; border: 1px solid #dee2e6; display: inline-block; border-radius: 5px; font-size: 10px;">
            <span style="margin-right: 15px;"><strong>Ano:</strong> {{ $ano }}</span>
            <span style="margin-right: 15px;"><strong>Unidade:</strong> {{ $organizacao ? $organizacao->nom_organizacao : 'Todas' }}</span>
            <span><strong>Data de Emissão:</strong> {{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Plano de Acao</th>
                <th style="width: 20%;">Objetivo</th>
                <th style="text-align: center;">Inicio</th>
                <th style="text-align: center;">Fim</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: center; width: 80px;">Progresso</th>
            </tr>
        </thead>
        <tbody>
            @forelse($planos as $plano)
                @php
                    $entregas = $plano->entregas->count();
                    $entregasConcluidas = $plano->entregas->where('bln_concluida', true)->count();
                    $progresso = $entregas > 0 ? round(($entregasConcluidas / $entregas) * 100, 1) : 0;

                    $statusClass = match($plano->bln_status) {
                        'Concluido' => 'status-concluido',
                        'Em Andamento' => 'status-andamento',
                        'Atrasado' => 'status-atrasado',
                        default => 'status-nao-iniciado'
                    };
                @endphp
                <tr>
                    <td style="font-weight: bold;">{{ $plano->dsc_plano_de_acao }}</td>
                    <td style="font-size: 9px;">{{ $plano->objetivo?->nom_objetivo ?? '-' }}</td>
                    <td style="text-align: center;">{{ $plano->dte_inicio?->format('d/m/Y') ?? '-' }}</td>
                    <td style="text-align: center;">{{ $plano->dte_fim?->format('d/m/Y') ?? '-' }}</td>
                    <td style="text-align: center;">
                        <span class="status {{ $statusClass }}">{{ $plano->bln_status ?? 'N/D' }}</span>
                    </td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $progresso }}%;"></div>
                        </div>
                        <div style="text-align: center; font-size: 9px; margin-top: 2px;">{{ $progresso }}%</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #999;">
                        Nenhum plano de acao encontrado para os filtros selecionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 10px; color: #666;">
        <strong>Resumo:</strong>
        Total de Planos: {{ $planos->count() }} |
        Concluidos: {{ $planos->where('bln_status', 'Concluido')->count() }} |
        Em Andamento: {{ $planos->where('bln_status', 'Em Andamento')->count() }} |
        Atrasados: {{ $planos->where('bln_status', 'Atrasado')->count() }}
    </div>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} | SPS
    </div>
</body>
</html>
