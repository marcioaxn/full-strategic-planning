<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Objetivos Estratégicos - PEI</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid #1B408E; padding-bottom: 10px; }
        .perspectiva-header { background: #1B408E; color: white; padding: 8px 15px; font-weight: bold; margin-top: 20px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f8f9fa; font-weight: bold; color: #1B408E; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0; color: #1B408E;">Relatório de Objetivos Estratégicos</h2>
        <div style="margin-top: 5px;">Plano Estratégico Institucional: {{ $pei->dsc_pei }} ({{ $pei->num_ano_inicio_pei }} - {{ $pei->num_ano_fim_pei }})</div>
    </div>

    @foreach($perspectivas as $p)
        <div class="perspectiva-header">{{ $p->dsc_perspectiva }}</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">Nível</th>
                    <th>Objetivo</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
                @forelse($p->objetivos->sortBy('num_nivel_hierarquico_apresentacao') as $obj)
                    <tr>
                        <td style="text-align: center;">{{ $obj->num_nivel_hierarquico_apresentacao }}</td>
                        <td style="font-weight: bold;">{{ $obj->nom_objetivo_estrategico }}</td>
                        <td style="font-size: 11px;">{{ $obj->dsc_objetivo_estrategico }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align: center; color: #999;">Nenhum objetivo nesta perspectiva.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

    <div class="footer">
        Página 1 | Gerado em {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
