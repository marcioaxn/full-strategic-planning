@php $unidades = $secao['dados']['unidades'] ?? collect(); @endphp

@if($secao['dados']['raiz'] ?? null)
    <p class="rg-corpo">
        <strong>{{ $secao['dados']['raiz']->nom_organizacao }}</strong>
        @if($secao['dados']['raiz']->sgl_organizacao)
            ({{ $secao['dados']['raiz']->sgl_organizacao }})
        @endif
        @if($unidades->isNotEmpty())
            reúne as unidades relacionadas a seguir, todas integradas ao mesmo ciclo
            de Planejamento Estratégico Integrado.
        @else
            responde pelo ciclo de Planejamento Estratégico Integrado relatado neste documento.
        @endif
    </p>
@endif

@if($unidades->isEmpty() && ! ($secao['dados']['raiz'] ?? null))
    @include('relatorios.gestao.secoes._sem-registro', [
        'mensagem' => 'Nenhuma organização foi selecionada para este relatório.',
    ])
@endif

@if($unidades->isNotEmpty())
<table class="rg-tabela">
    <thead>
        <tr>
            <th style="width:18%;">Sigla</th>
            <th>Unidade</th>
        </tr>
    </thead>
    <tbody>
        @foreach($unidades as $unidade)
        <tr>
            <td class="rg-centro">{{ $unidade->sgl_organizacao }}</td>
            <td>{{ $unidade->nom_organizacao }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
