{{--
    Seção 2.2 Resultados Alcançados.

    Reproduz as DUAS tabelas do modelo, com os cabeçalhos exatos:

      Tabela 2.2.1 (p. 28) — Identificador · Objetivo Estratégico ·
                             Descrição (resumida) · Principais Iniciativas
      Tabela 2.2.2 (p. 34) — Objetivo · Iniciativas · Resultados

    🔴 É por causa destes cabeçalhos que a renomeação "Plano de Ação" →
    "Iniciativas" precisava vir antes: o documento oficial do cliente já usa
    "Iniciativas", e o relatório gerado divergiria do modelo já na primeira
    tabela.
--}}
@php
    $tabela1 = $secao['dados']['tabela_2_2_1'] ?? [];
    $tabela2 = $secao['dados']['tabela_2_2_2'] ?? [];
@endphp

@if(count($tabela1) === 0 && count($tabela2) === 0)
    @include('relatorios.gestao.secoes._sem-registro', [
        'mensagem' => 'Nenhum objetivo estratégico foi cadastrado neste ciclo, e por isso não há resultados a apurar.',
    ])
@endif

@if(count($tabela1) > 0 || count($tabela2) > 0)
<p class="rg-corpo">
    A seguir são apresentados os objetivos estratégicos do ciclo, sua descrição e as
    principais iniciativas vinculadas. Na sequência, os resultados apurados no exercício
    de {{ $dados['ano'] }}.
</p>

@if(count($tabela1) > 0)
<p class="rg-tabela-titulo">Tabela 2.2.1 — Informações sobre o Planejamento Estratégico Integrado</p>
<table class="rg-tabela">
    <thead>
        <tr>
            <th style="width:10%;">Identificador</th>
            <th style="width:24%;">Objetivo Estratégico</th>
            <th style="width:30%;">Descrição (resumida)</th>
            <th>Principais Iniciativas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tabela1 as $linha)
        <tr>
            <td class="rg-centro">{{ $linha['identificador'] }}</td>
            <td class="rg-centro">{{ $linha['objetivo'] }}</td>
            <td>{{ $linha['descricao'] ?: '—' }}</td>
            <td>
                @if(count($linha['iniciativas']) > 0)
                    <ul class="rg-lista">
                        @foreach($linha['iniciativas'] as $iniciativa)
                            <li>{{ $iniciativa }}</li>
                        @endforeach
                    </ul>
                @else
                    <span class="rg-vazio">Em revisão</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@if(count($tabela2) > 0)
<p class="rg-tabela-titulo">Tabela 2.2.2 — Resultados, Objetivos Estratégicos e Prioridades da Gestão</p>
<table class="rg-tabela">
    <thead>
        <tr>
            <th style="width:26%;">Objetivo</th>
            <th style="width:32%;">Iniciativas</th>
            <th>Resultados</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tabela2 as $linha)
        <tr>
            <td class="rg-centro">{{ $linha['objetivo'] }}</td>
            <td>
                @if(count($linha['iniciativas']) > 0)
                    <ul class="rg-lista">
                        @foreach($linha['iniciativas'] as $iniciativa)
                            <li>{{ $iniciativa }}</li>
                        @endforeach
                    </ul>
                @else
                    <span class="rg-vazio">Sem iniciativas vinculadas no período.</span>
                @endif
            </td>
            <td>
                @if(count($linha['resultados']) > 0)
                    <ul class="rg-lista">
                        @foreach($linha['resultados'] as $resultado)
                            <li>{{ $resultado['texto'] }}</li>
                        @endforeach
                    </ul>
                @else
                    <span class="rg-vazio">Sem indicadores com evolução lançada no exercício.</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@endif
