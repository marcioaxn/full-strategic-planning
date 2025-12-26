<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Executivo Consolidado</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        .page-break { page-break-after: always; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1B408E; padding-bottom: 10px; }
        .section-title { background: #f8f9fa; color: #1B408E; padding: 10px; font-size: 14px; font-weight: bold; border-left: 5px solid #1B408E; margin-top: 30px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        th { background: #f1f1f1; font-size: 10px; color: #666; }
        .farol { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .card { border: 1px solid #eee; padding: 15px; margin-bottom: 10px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; color: #1B408E;">Relatório Executivo Consolidado</h1>
        <div style="font-size: 16px; margin-top: 10px;">{{ $organizacao->nom_organizacao }}</div>
        <div style="color: #666;">Referência: {{ now()->format('d/m/Y') }}</div>
    </div>

    <!-- 1. Identidade -->
    <div class="section-title">1. Identidade Estratégica</div>
    <div class="card">
        <strong style="color: #1B408E;">MISSÃO:</strong>
        <p><em>"{{ $identidade->dsc_missao ?: 'Não definida.' }}"</em></p>
        <strong style="color: #1B408E;">VISÃO:</strong>
        <p><em>"{{ $identidade->dsc_visao ?: 'Não definida.' }}"</em></p>
    </div>

    <!-- 2. Objetivos e Performance -->
    <div class="section-title">2. Objetivos Estratégicos e Desempenho</div>
    @foreach($perspectivas as $p)
        <div style="font-weight: bold; background: #1B408E; color: white; padding: 5px 10px; margin-top: 15px;">
            PERSPECTIVA: {{ $p->dsc_perspectiva }}
        </div>
        <table>
            <thead>
                <tr>
                    <th>Objetivo Estratégico</th>
                    <th style="width: 100px; text-align: center;">Performance</th>
                    <th style="width: 20px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($p->objetivos as $obj)
                    <tr>
                        <td>{{ $obj->nom_objetivo_estrategico }}</td>
                        <td style="text-align: center; font-weight: bold;">
                            @php 
                                $soma = 0; $cont = 0;
                                foreach($obj->indicadores as $ind) { $soma += $ind->calcularAtingimento(); $cont++; }
                                $media = $cont > 0 ? $soma / $cont : 0;
                            @endphp
                            {{ number_format($media, 1) }}%
                        </td>
                        <td style="text-align: center;">
                            <div class="farol" style="background-color: {{ $media >= 100 ? '#198754' : ($media >= 80 ? '#ffc107' : '#dc3545') }}"></div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align: center; color: #999;">Sem objetivos vinculados.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

    <div class="page-break"></div>

    <!-- 3. Planos de Ação Críticos -->
    <div class="section-title">3. Planos de Ação em Execução</div>
    <table>
        <thead>
            <tr>
                <th>Plano de Ação</th>
                <th>Status</th>
                <th>Término</th>
                <th>Progresso</th>
            </tr>
        </thead>
        <tbody>
            @foreach($planos as $plano)
                <tr>
                    <td>{{ $plano->dsc_plano_de_acao }}</td>
                    <td>{{ $plano->bln_status }}</td>
                    <td style="color: {{ $plano->isAtrasado() ? 'red' : 'black' }}">{{ $plano->dte_fim?->format('d/m/Y') }}</td>
                    <td style="font-weight: bold;">{{ number_format($plano->calcularProgressoEntregas(), 1) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Gerado pelo sistema SEAE em {{ now()->format('d/m/Y H:i') }} | Página 1
    </div>
</body>
</html>
