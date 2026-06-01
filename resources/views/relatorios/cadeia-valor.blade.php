<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cadeia de Valor</title>
    <style>
        @page { margin: 1cm; size: a4 landscape; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #333; line-height: 1.4; margin: 0; padding: 0; }

        .header { border-bottom: 3px solid #2e6da4; padding-bottom: 10px; margin-bottom: 16px; }
        .header-title { font-size: 18px; font-weight: bold; color: #1a3a5c; text-transform: uppercase; margin: 0 0 3px 0; }
        .header-sub { font-size: 10px; color: #555; margin: 0; }
        .header-meta { font-size: 8px; color: #888; text-align: right; }

        .block { margin-bottom: 16px; page-break-inside: avoid; }
        .block-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #fff; padding: 6px 12px; border-radius: 3px; margin-bottom: 10px; }
        .block-fin { background: linear-gradient(135deg, #1a3a5c, #2e6da4); }
        .block-sup { background: #475569; }

        .grid { display: table; width: 100%; border-collapse: separate; border-spacing: 6px; }
        .grid-row { display: table-row; }
        .card {
            display: inline-block; width: 31%; vertical-align: top; margin: 0 1% 8px 0;
            border: 1px solid #dee2e6; border-radius: 6px; padding: 8px 10px;
            background: #f8fafc; page-break-inside: avoid;
        }
        .card-fin { border-left: 3px solid #2e6da4; }
        .card-sup { border-left: 3px solid #475569; }
        .card-title { font-weight: bold; font-size: 9.5px; color: #1a3a5c; margin-bottom: 4px; }
        .card-persp { font-size: 7.5px; color: #2e6da4; background: #e8f0fa; display: inline-block; padding: 1px 6px; border-radius: 3px; margin-bottom: 4px; }
        .proc { font-size: 7.5px; color: #555; margin: 2px 0; padding-left: 8px; border-left: 2px solid #e2e8f0; }
        .proc-label { font-weight: bold; color: #888; text-transform: uppercase; font-size: 6.5px; }

        .empty { color: #aaa; font-style: italic; font-size: 8px; padding: 8px; }
        .footer { border-top: 1px solid #dee2e6; padding-top: 6px; margin-top: 16px; font-size: 7px; color: #aaa; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width:100%;">
            <tr>
                <td>
                    <p class="header-title">Cadeia de Valor</p>
                    <p class="header-sub">{{ $pei->dsc_pei }}</p>
                </td>
                <td class="header-meta">
                    Gerado em: {{ $data }}<br>
                    Guia GPPEI · Pág. 24
                </td>
            </tr>
        </table>
    </div>

    {{-- Atividades Finalísticas --}}
    <div class="block">
        <div class="block-title block-fin">Atividades Finalísticas — Produtos e serviços entregues à sociedade</div>
        @forelse($finalisticas as $ativ)
            <div class="card card-fin">
                <div class="card-title">{{ $ativ->dsc_atividade }}</div>
                @if($ativ->perspectiva)
                    <span class="card-persp">{{ $ativ->perspectiva->dsc_perspectiva }}</span>
                @endif
                @foreach($ativ->processos as $proc)
                    <div class="proc">
                        @if($proc->dsc_entrada)<span class="proc-label">Entrada:</span> {{ $proc->dsc_entrada }}<br>@endif
                        <span class="proc-label">Processo:</span> {{ $proc->dsc_transformacao }}
                        @if($proc->dsc_saida)<br><span class="proc-label">Saída:</span> {{ $proc->dsc_saida }}@endif
                    </div>
                @endforeach
            </div>
        @empty
            <div class="empty">Nenhuma atividade finalística cadastrada.</div>
        @endforelse
    </div>

    {{-- Atividades de Suporte --}}
    <div class="block">
        <div class="block-title block-sup">Atividades de Suporte — Infraestrutura e processos internos de apoio</div>
        @forelse($suporte as $ativ)
            <div class="card card-sup">
                <div class="card-title">{{ $ativ->dsc_atividade }}</div>
                @if($ativ->perspectiva)
                    <span class="card-persp">{{ $ativ->perspectiva->dsc_perspectiva }}</span>
                @endif
                @foreach($ativ->processos as $proc)
                    <div class="proc">
                        @if($proc->dsc_entrada)<span class="proc-label">Entrada:</span> {{ $proc->dsc_entrada }}<br>@endif
                        <span class="proc-label">Processo:</span> {{ $proc->dsc_transformacao }}
                        @if($proc->dsc_saida)<br><span class="proc-label">Saída:</span> {{ $proc->dsc_saida }}@endif
                    </div>
                @endforeach
            </div>
        @empty
            <div class="empty">Nenhuma atividade de suporte cadastrada.</div>
        @endforelse
    </div>

    <div class="footer">
        Documento gerado pelo Sistema de Planejamento Estratégico Institucional (PEI) &mdash;
        Baseado no Guia Prático de Planejamento Estratégico Institucional (GPPEI/MGI 2025)
    </div>
</body>
</html>
