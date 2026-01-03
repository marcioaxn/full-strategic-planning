<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gestao de Riscos</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #1B408E; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #dee2e6; padding: 6px; text-align: left; }
        th { background: #f8f9fa; color: #1B408E; font-size: 10px; text-transform: uppercase; }
        .nivel { padding: 3px 8px; border-radius: 3px; font-size: 9px; font-weight: bold; text-align: center; }
        .nivel-critico { background: #f8d7da; color: #842029; }
        .nivel-alto { background: #ffe5d0; color: #984c0c; }
        .nivel-medio { background: #fff3cd; color: #664d03; }
        .nivel-baixo { background: #d1e7dd; color: #0f5132; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
        .matriz { margin: 20px 0; }
        .matriz-cell { width: 40px; height: 30px; text-align: center; vertical-align: middle; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0; color: #1B408E;">Relatorio de Gestao de Riscos</h2>
        <div style="margin-top: 5px;">
            @if($organizacao)
                Unidade: {{ $organizacao->nom_organizacao }} |
            @endif
            Referencia: {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Risco</th>
                <th style="width: 30%;">Descricao</th>
                <th style="text-align: center;">P</th>
                <th style="text-align: center;">I</th>
                <th style="text-align: center;">Nivel</th>
                <th style="text-align: center;">Class.</th>
                <th style="text-align: center;">Mitig.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riscos as $risco)
                @php
                    $nivel = $risco->num_probabilidade * $risco->num_impacto;
                    $classificacao = $nivel >= 15 ? 'Critico' : ($nivel >= 10 ? 'Alto' : ($nivel >= 5 ? 'Medio' : 'Baixo'));
                    $nivelClass = match(true) {
                        $nivel >= 15 => 'nivel-critico',
                        $nivel >= 10 => 'nivel-alto',
                        $nivel >= 5 => 'nivel-medio',
                        default => 'nivel-baixo'
                    };
                @endphp
                <tr>
                    <td style="font-weight: bold;">{{ $risco->nom_risco }}</td>
                    <td style="font-size: 9px;">{{ Str::limit($risco->dsc_risco, 100) }}</td>
                    <td style="text-align: center;">{{ $risco->num_probabilidade }}</td>
                    <td style="text-align: center;">{{ $risco->num_impacto }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $nivel }}</td>
                    <td style="text-align: center;">
                        <span class="nivel {{ $nivelClass }}">{{ $classificacao }}</span>
                    </td>
                    <td style="text-align: center;">{{ $risco->mitigacoes->count() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #999;">
                        Nenhum risco encontrado para os filtros selecionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 10px; color: #666;">
        <strong>Resumo:</strong>
        Total de Riscos: {{ $riscos->count() }} |
        @php
            $criticos = $riscos->filter(fn($r) => ($r->num_probabilidade * $r->num_impacto) >= 15)->count();
            $altos = $riscos->filter(fn($r) => ($r->num_probabilidade * $r->num_impacto) >= 10 && ($r->num_probabilidade * $r->num_impacto) < 15)->count();
            $medios = $riscos->filter(fn($r) => ($r->num_probabilidade * $r->num_impacto) >= 5 && ($r->num_probabilidade * $r->num_impacto) < 10)->count();
            $baixos = $riscos->filter(fn($r) => ($r->num_probabilidade * $r->num_impacto) < 5)->count();
        @endphp
        Criticos: {{ $criticos }} |
        Altos: {{ $altos }} |
        Medios: {{ $medios }} |
        Baixos: {{ $baixos }}
    </div>

    <div style="margin-top: 15px; font-size: 9px; color: #888;">
        <strong>Legenda:</strong> P = Probabilidade (1-5) | I = Impacto (1-5) | Nivel = P x I
    </div>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} | SPS
    </div>
</body>
</html>
