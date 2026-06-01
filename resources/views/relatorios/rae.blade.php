<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RAE — Revisão e Avaliação da Estratégia</title>
    <style>
        @page { margin: 1.5cm; size: a4 portrait; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; line-height: 1.4; margin: 0; padding: 0; }

        .header { border-bottom: 3px solid #1B408E; padding-bottom: 10px; margin-bottom: 20px; }
        .header-title { font-size: 20px; font-weight: bold; color: #1B408E; text-transform: uppercase; margin: 0 0 4px 0; }
        .header-sub { font-size: 11px; color: #555; margin: 0; }
        .header-meta { font-size: 9px; color: #888; text-align: right; }

        .section { margin-bottom: 18px; page-break-inside: avoid; }
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #fff; padding: 5px 10px; margin-bottom: 8px; border-radius: 3px; }
        .section-body { padding: 10px 12px; border-radius: 3px; font-size: 10px; line-height: 1.5; white-space: pre-wrap; }

        .success { background-color: #198754; }
        .danger  { background-color: #dc3545; }
        .primary { background-color: #1B408E; }
        .info    { background-color: #0891B2; }

        .bg-success-light { background-color: #d1e7dd; }
        .bg-danger-light  { background-color: #f8d7da; }
        .bg-primary-light { background-color: #cfe2ff; }
        .bg-info-light    { background-color: #cff4fc; }

        .meta-grid { display: table; width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .meta-cell { display: table-cell; padding: 8px 12px; border: 1px solid #dee2e6; font-size: 9px; vertical-align: top; }
        .meta-label { font-weight: bold; color: #555; text-transform: uppercase; display: block; margin-bottom: 2px; }
        .meta-val   { font-size: 11px; color: #222; font-weight: bold; }

        .progress-bar-container { background-color: #e9ecef; border-radius: 4px; height: 12px; margin-top: 4px; }
        .progress-bar-fill { border-radius: 4px; height: 12px; }

        .participantes { font-size: 9px; color: #555; margin-top: 6px; }
        .participante-tag { display: inline-block; background: #e9ecef; border-radius: 3px; padding: 2px 6px; margin: 2px; font-size: 9px; }

        .footer { border-top: 1px solid #dee2e6; padding-top: 8px; margin-top: 30px; font-size: 8px; color: #aaa; text-align: center; }
    </style>
</head>
<body>
    {{-- Cabeçalho --}}
    <div class="header">
        <table style="width:100%;">
            <tr>
                <td>
                    <p class="header-title">Revisão e Avaliação da Estratégia</p>
                    <p class="header-sub">{{ $rae->dsc_tipo_reuniao }} · Ref.: {{ $rae->dte_referencia->format('F/Y') }}</p>
                    <p class="header-sub">{{ $rae->organizacao?->nom_organizacao }} · {{ $rae->pei?->dsc_pei }}</p>
                </td>
                <td class="header-meta">
                    Gerado em: {{ $data }}<br>
                    Guia GPPEI · Pág. 138
                </td>
            </tr>
        </table>
    </div>

    {{-- Metadados --}}
    <div class="meta-grid">
        <div class="meta-cell">
            <span class="meta-label">Período de Referência</span>
            <span class="meta-val">{{ $rae->dte_referencia->format('d/m/Y') }}</span>
        </div>
        @if($rae->dte_reuniao)
        <div class="meta-cell">
            <span class="meta-label">Data da Reunião</span>
            <span class="meta-val">{{ $rae->dte_reuniao->format('d/m/Y') }}</span>
        </div>
        @endif
        <div class="meta-cell">
            <span class="meta-label">Tipo</span>
            <span class="meta-val">{{ $rae->dsc_tipo_reuniao }}</span>
        </div>
        @if($rae->num_progresso_geral !== null)
        <div class="meta-cell">
            <span class="meta-label">Progresso Geral</span>
            <span class="meta-val">{{ number_format($rae->num_progresso_geral, 1) }}%</span>
            @php $cor = $rae->num_progresso_geral >= 70 ? '#198754' : ($rae->num_progresso_geral >= 40 ? '#ffc107' : '#dc3545'); @endphp
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width:{{ min(100, $rae->num_progresso_geral) }}%;background-color:{{ $cor }};"></div>
            </div>
        </div>
        @endif
    </div>

    {{-- Destaques Positivos --}}
    @if($rae->txt_destaques_positivos)
    <div class="section">
        <div class="section-title success">✓ Destaques Positivos</div>
        <div class="section-body bg-success-light">{{ $rae->txt_destaques_positivos }}</div>
    </div>
    @endif

    {{-- Problemas Identificados --}}
    @if($rae->txt_problemas_identificados)
    <div class="section">
        <div class="section-title danger">⚠ Problemas Identificados</div>
        <div class="section-body bg-danger-light">{{ $rae->txt_problemas_identificados }}</div>
    </div>
    @endif

    {{-- Encaminhamentos --}}
    @if($rae->txt_encaminhamentos)
    <div class="section">
        <div class="section-title primary">→ Encaminhamentos e Decisões</div>
        <div class="section-body bg-primary-light">{{ $rae->txt_encaminhamentos }}</div>
    </div>
    @endif

    {{-- Participantes --}}
    @if(!empty($rae->json_participantes))
    <div class="section">
        <div class="section-title info">Participantes da Reunião</div>
        <div class="section-body bg-info-light">
            @foreach($rae->json_participantes as $p)
                <span class="participante-tag">{{ $p }}</span>
            @endforeach
        </div>
    </div>
    @endif

    <div class="footer">
        Documento gerado pelo Sistema de Planejamento Estratégico Institucional (PEI) &mdash;
        Baseado no Guia Prático de Planejamento Estratégico Institucional (GPPEI/MGI 2025)
    </div>
</body>
</html>
