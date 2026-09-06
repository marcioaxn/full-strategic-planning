{{-- Seção 1.8 do modelo, alimentada pelas análises SWOT e PESTEL do sistema. --}}
@php
    $swot = $secao['dados']['swot'] ?? collect();
    $pestel = $secao['dados']['pestel'] ?? collect();
@endphp

@if($swot->isEmpty() && $pestel->isEmpty())
    @include('relatorios.gestao.secoes._sem-registro', [
        'mensagem' => 'Nenhuma análise SWOT ou PESTEL foi registrada neste ciclo.',
    ])
@endif

@if($swot->isNotEmpty())
    <p class="rg-tabela-titulo"><strong>Matriz SWOT</strong></p>
    <table class="rg-tabela">
        <thead>
            <tr>
                <th style="width:26%;">Categoria</th>
                <th>Itens identificados</th>
            </tr>
        </thead>
        <tbody>
            @foreach($swot as $categoria => $itens)
            <tr>
                <td class="rg-centro">{{ $categoria }}</td>
                <td>
                    <ul class="rg-lista">
                        @foreach($itens as $item)
                            <li>{{ $item->dsc_item }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if($pestel->isNotEmpty())
    <p class="rg-tabela-titulo"><strong>Análise PESTEL</strong></p>
    <table class="rg-tabela">
        <thead>
            <tr>
                <th style="width:26%;">Dimensão</th>
                <th>Fatores identificados</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pestel as $categoria => $itens)
            <tr>
                <td class="rg-centro">{{ $categoria }}</td>
                <td>
                    <ul class="rg-lista">
                        @foreach($itens as $item)
                            <li>{{ $item->dsc_item }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
