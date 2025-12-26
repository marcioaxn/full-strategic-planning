<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Indicadores de Desempenho</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #1B408E; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dee2e6; padding: 6px; text-align: left; }
        th { background: #f8f9fa; color: #1B408E; font-size: 10px; text-transform: uppercase; }
        .farol { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0; color: #1B408E;">Relatório de Indicadores de Desempenho</h2>
        <div style="margin-top: 5px;">Unidade: {{ $organizacao->nom_organizacao }} | Referência: {{ now()->format('d/m/Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Indicador</th>
                <th>Unidade</th>
                <th>Vínculo</th>
                <th style="text-align: center;">Meta</th>
                <th style="text-align: center;">Ating.</th>
                <th style="width: 20px;"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($indicadores as $ind)
                <tr>
                    <td style="font-weight: bold;">{{ $ind->nom_indicador }}</td>
                    <td>{{ $ind->dsc_unidade_medida }}</td>
                    <td style="font-size: 9px;">
                        {{ $ind->cod_objetivo_estrategico ? 'OBJ: '.$ind->objetivoEstrategico->nom_objetivo_estrategico : 'PLAN: '.$ind->planoDeAcao->dsc_plano_de_acao }}
                    </td>
                    <td style="text-align: center;">{{ $ind->dsc_meta }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($ind->calcularAtingimento(), 1) }}%</td>
                    <td style="text-align: center;">
                        <div class="farol" style="background-color: {{ $ind->getCorFarol() ?: '#eee' }}"></div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} | SEAE
    </div>
</body>
</html>
