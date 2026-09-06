{{--
    Relatório de Gestão — saída em PDF.

    Espelha o Relatório de Gestão 2025 da Presidência da República
    (documentacao/relatorios/RelatriodeGesto2025PReVPR31mar.pdf): A4 paisagem,
    corpo em colunas, cabeçalho e rodapé com régua verde, capa sangrada.

    O conteúdo vem pronto de EstruturaRelatorioGestao: esta view só desenha.
    A mesma estrutura alimenta a saída em DOCX — é isso que impede os dois
    formatos de divergirem.

    A CAPA não está aqui: é pintada no canvas, em RelatorioController, porque
    o DomPDF não deixa um elemento sangrar para fora da margem de @page. A
    página 1 existe neste HTML apenas como folha em branco.
--}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório de Gestão {{ $dados['ano'] }} — {{ $dados['capa']['orgao'] }}</title>
    @include('relatorios.gestao.estilos')
</head>
<body>

@php
    $somente = $somente ?? null;   // null = documento inteiro; 'prefixo' ou índice do capítulo
    $capitulos = $dados['capitulos'];
    $meio = (int) ceil(count($capitulos) / 2);
    $colunasSumario = [array_slice($capitulos, 0, $meio), array_slice($capitulos, $meio)];
@endphp

{{-- Cabeçalho e rodapé NÃO estão aqui.

     Elemento `position: fixed` do DomPDF é desenhado em TODAS as páginas,
     inclusive na capa. Pintar a capa por cima escondia a faixa, mas o texto
     continuava na camada de texto do PDF: quem selecionasse a capa copiava
     "PÁGINA 1" de uma página que não mostra nada disso, e um leitor de tela
     anunciaria o mesmo. Interface que mostra uma coisa e guarda outra mente.

     Os dois são desenhados no canvas, da página 2 em diante, em
     App\Services\Reports\RelatorioGestao\AcabamentoPdf. --}}

@if($somente === null || $somente === 'prefixo')
{{-- Página 1: folha em branco. O desenho é do canvas. --}}
<div style="page-break-after: always;">&nbsp;</div>
@endif

{{-- ────────────────────────── SUMÁRIO ────────────────────────── --}}
@if($somente === null || $somente === 'prefixo')
<div class="rg-sumario">
    <p class="rg-cap-titulo">Sumário</p>

    <table style="width:100%; border-collapse:collapse;">
        <tr>
            @foreach($colunasSumario as $indice => $coluna)
            <td class="rg-sum-col {{ $indice === 1 ? 'rg-ultima' : '' }}">
                @foreach($coluna as $capitulo)
                    <p class="rg-sum-cap">{{ $capitulo['numero'] }}. {{ $capitulo['titulo'] }}</p>
                    @foreach($capitulo['secoes'] as $secao)
                        <p class="rg-sum-item">
                            {{ $secao['numero'] }} {{ $secao['titulo'] }}
                            @if($secao['externa'])
                                <span class="rg-sum-ext">— a preencher pela unidade</span>
                            @endif
                        </p>
                    @endforeach
                @endforeach
            </td>
            @endforeach
        </tr>
    </table>

    <p class="rg-corpo" style="margin-top:20px;">
        @if($dados['variante'] === 'replica')
            <strong>Versão rascunho — estrutura completa do modelo.</strong>
            As seções alimentadas por sistemas externos aparecem marcadas, com a indicação
            da fonte que as preenche. Complete-as antes de publicar.
        @else
            Este documento apresenta as seções para as quais há informação registrada
            no Planejamento Estratégico Institucional.
        @endif
    </p>
</div>
@endif

{{-- ───────────────────────── CAPÍTULOS ───────────────────────── --}}
@foreach($dados['capitulos'] as $indiceCapitulo => $capitulo)
@continue($somente !== null && $somente !== $indiceCapitulo)
<div class="rg-cap {{ $somente === $indiceCapitulo ? 'rg-cap-primeiro' : '' }}">
    {{-- data-rpt-secao: é por este atributo que AcabamentoPdf descobre, --}}
    {{-- durante a renderização, em que página cada capítulo começou.       --}}
    <p class="rg-cap-titulo" data-rpt-secao="{{ $capitulo['numero'] }}">{{ $capitulo['numero'] }}. {{ $capitulo['titulo'] }}</p>

    @foreach($capitulo['secoes'] as $secao)
        <p class="rg-sec-titulo">{{ $secao['numero'] }} {{ $secao['titulo'] }}</p>

        @include('relatorios.gestao.secoes.' . ($secao['externa'] ? 'externa' : $secao['tipo']), ['secao' => $secao, 'dados' => $dados])
    @endforeach
</div>
@endforeach

</body>
</html>
