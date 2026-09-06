{{--
    Seção 2.1 Estratégia — a página 27 do modelo, reproduzida.

    O layout do modelo, medido na página rasterizada:

      • coluna estreita de texto à esquerda (~26% da largura)
      • à direita, o título "MAPA ESTRATÉGICO — <ÓRGÃO>" centralizado
      • cinco blocos, cada um com um RÓTULO VERTICAL colorido na lateral:
        MISSÃO (amarelo), VISÃO (azul), VALORES (verde), OBJETIVOS
        FINALÍSTICOS (vermelho) e SUPORTE (preto)
      • dentro de VALORES, FINALÍSTICOS e SUPORTE, os itens em GRADE de
        três colunas — não em lista

    O rótulo vertical é escrito uma letra por linha. É o mesmo resultado
    visual do texto rotacionado do modelo, e não depende do suporte a
    `transform` do DomPDF, que é parcial.
--}}
@php
    $identidade = $secao['dados']['identidade'] ?? null;
    $valores = $secao['dados']['valores'] ?? collect();
    $temas = $secao['dados']['temas'] ?? collect();
    $finalisticos = $secao['dados']['objetivos_finalisticos'] ?? collect();
    $suporte = $secao['dados']['objetivos_suporte'] ?? collect();

    $letrasDe = fn (string $texto) => preg_split('//u', mb_strtoupper($texto), -1, PREG_SPLIT_NO_EMPTY);

    // Distribui uma coleção em N colunas para a grade interna dos blocos.
    // Nunca mais colunas do que itens: grade de três com um item só deixa
    // duas células vazias, e o bloco fica com um vazio que não significa nada.
    $emColunas = function ($itens, int $colunas) {
        $lista = collect($itens)->values();
        $colunas = max(1, min($colunas, $lista->count()));
        $porColuna = (int) ceil(max(1, $lista->count()) / $colunas);

        return $lista->chunk($porColuna)->values();
    };
@endphp

@if(! $identidade?->dsc_missao && ! $identidade?->dsc_visao && $valores->isEmpty() && $finalisticos->isEmpty() && $suporte->isEmpty())
    @include('relatorios.gestao.secoes._sem-registro', [
        'mensagem' => 'Missão, visão, valores e objetivos ainda não foram cadastrados neste ciclo.',
    ])
@else
<table style="width:100%; border-collapse:collapse;">
    <tr>
        {{-- Coluna esquerda: o texto de abertura, como no modelo --}}
        <td style="width:26%; vertical-align:top; padding-right:22px; text-align:justify;">
            <p class="rg-corpo">
                O Planejamento Estratégico Institucional
                @if($dados['capa']['ciclo'])
                    <strong>{{ $dados['capa']['ciclo'] }}</strong>
                @endif
                orienta a atuação de {{ $dados['capa']['orgao'] }} no período.
            </p>
            <p class="rg-corpo">
                Missão, visão, valores e objetivos estratégicos ao lado são os que
                estavam vigentes no exercício de {{ $dados['ano'] }}.
            </p>
            @if($temas->isNotEmpty())
                <p class="rg-corpo">
                    <strong>Temas norteadores:</strong>
                    {{ $temas->pluck('nom_tema_norteador')->implode(' · ') }}
                </p>
            @endif
        </td>

        {{-- Coluna direita: o mapa --}}
        <td style="vertical-align:top;">
            <p class="rg-mapa-titulo">Mapa Estratégico — {{ $dados['capa']['orgao'] }}</p>

            @if($identidade?->dsc_missao)
            <table class="rg-bloco rg-b-missao">
                <tr>
                    <td class="rg-bloco-rotulo">@foreach($letrasDe('Missão') as $letra)@if(! $loop->first)<br>@endif{{ $letra }}@endforeach</td>
                    <td class="rg-bloco-corpo">{{ $identidade->dsc_missao }}</td>
                </tr>
            </table>
            @endif

            @if($identidade?->dsc_visao)
            <table class="rg-bloco rg-b-visao">
                <tr>
                    <td class="rg-bloco-rotulo">@foreach($letrasDe('Visão') as $letra)@if(! $loop->first)<br>@endif{{ $letra }}@endforeach</td>
                    <td class="rg-bloco-corpo">{{ $identidade->dsc_visao }}</td>
                </tr>
            </table>
            @endif

            @if($valores->isNotEmpty())
            <table class="rg-bloco rg-b-valores">
                <tr>
                    <td class="rg-bloco-rotulo">@foreach($letrasDe('Valores') as $letra)@if(! $loop->first)<br>@endif{{ $letra }}@endforeach</td>
                    <td class="rg-bloco-corpo">
                        <table class="rg-grade">
                            <tr>
                                @foreach($emColunas($valores, 3) as $coluna)
                                <td class="{{ $loop->last ? 'rg-ultima' : '' }}" style="width:33%;">
                                    @foreach($coluna as $valor)
                                        <p style="margin:0 0 5px 0;">
                                            <strong>{{ $valor->nom_valor }}</strong>@if($valor->dsc_valor) — {{ $valor->dsc_valor }}@endif
                                        </p>
                                    @endforeach
                                </td>
                                @endforeach
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            @endif

            @if($finalisticos->isNotEmpty())
            <table class="rg-bloco rg-b-fim">
                <tr>
                    <td class="rg-bloco-rotulo">@foreach($letrasDe('Finalísticos') as $letra)@if(! $loop->first)<br>@endif{{ $letra }}@endforeach</td>
                    <td class="rg-bloco-corpo">
                        <table class="rg-grade">
                            <tr>
                                @foreach($emColunas($finalisticos, 3) as $coluna)
                                <td class="{{ $loop->last ? 'rg-ultima' : '' }}" style="width:33%;">
                                    @foreach($coluna as $objetivo)
                                        <p style="margin:0 0 5px 0;"><strong>{{ $objetivo->nom_objetivo }}</strong></p>
                                    @endforeach
                                </td>
                                @endforeach
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            @endif

            @if($suporte->isNotEmpty())
            <table class="rg-bloco rg-b-sup">
                <tr>
                    <td class="rg-bloco-rotulo">@foreach($letrasDe('Suporte') as $letra)@if(! $loop->first)<br>@endif{{ $letra }}@endforeach</td>
                    <td class="rg-bloco-corpo">
                        <table class="rg-grade">
                            <tr>
                                @foreach($emColunas($suporte, 3) as $coluna)
                                <td class="{{ $loop->last ? 'rg-ultima' : '' }}" style="width:33%;">
                                    @foreach($coluna as $objetivo)
                                        <p style="margin:0 0 5px 0;"><strong>{{ $objetivo->nom_objetivo }}</strong></p>
                                    @endforeach
                                </td>
                                @endforeach
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            @endif
        </td>
    </tr>
</table>
@endif
