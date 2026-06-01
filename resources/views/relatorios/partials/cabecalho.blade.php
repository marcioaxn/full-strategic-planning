{{--
    Cabeçalho fixo do relatório.
    Variáveis esperadas: $rptTitulo, $rptEyebrow, $rptSubtitulo (opc), $rptIcon (opc)
--}}
<div class="rpt-header">
    <table class="rpt-header-table">
        <tr>
            <td style="width:54px;">
                <div class="rpt-header-icon"><span>{!! $rptIcon ?? '&#9632;' !!}</span></div>
            </td>
            <td style="vertical-align:middle; padding-left:10px;">
                <p class="rpt-eyebrow">{{ $rptEyebrow ?? 'Planejamento Estratégico Institucional' }}</p>
                <p class="rpt-title">{{ $rptTitulo ?? 'Relatório' }}</p>
                @if(!empty($rptSubtitulo))
                    <p class="rpt-subtitle">{{ $rptSubtitulo }}</p>
                @endif
            </td>
            <td class="rpt-header-meta">
                <strong>{{ now()->format('d/m/Y') }}</strong><br>
                {{ now()->format('H:i') }}<br>
                <span style="opacity:.7;">Sistema PEI · GPPEI</span>
            </td>
        </tr>
    </table>
    <div class="rpt-accent-bar"></div>
</div>
