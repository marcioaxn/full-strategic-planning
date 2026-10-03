@php $grupos = $secao['dados']['grupos'] ?? []; @endphp

@if(count($grupos) === 0)
    @include('relatorios.gestao.secoes._sem-registro', [
        'mensagem' => 'A cadeia de valor ainda não foi cadastrada neste ciclo do Planejamento Estratégico Integrado.',
    ])
@else
<p class="rg-corpo">
    A cadeia de valor descreve as atividades por meio das quais a organização entrega
    resultado à sociedade, separando as finalísticas das que lhes dão suporte.
</p>

@foreach($grupos as $grupo)
    <p class="rg-tabela-titulo"><strong>{{ $grupo['tipo'] }}</strong></p>
    <table class="rg-tabela">
        <thead>
            <tr>
                <th style="width:45%;">Atividade</th>
                <th>Processos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grupo['itens'] as $atividade)
            <tr>
                <td>{{ $atividade->dsc_atividade }}</td>
                <td>
                    @if($atividade->processos->isNotEmpty())
                        <ul class="rg-lista">
                            @foreach($atividade->processos as $processo)
                                <li>{{ $processo->dsc_transformacao }}</li>
                            @endforeach
                        </ul>
                    @else
                        <span class="rg-vazio">Processos não detalhados.</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endforeach
@endif
