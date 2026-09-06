{{-- Seções 2.3 a 2.5 do modelo: os números de destaque do exercício. --}}
@php $numeros = $secao['dados']['numeros'] ?? []; @endphp

@if(count($numeros) === 0)
    @include('relatorios.gestao.secoes._sem-registro', [
        'mensagem' => 'Ainda não há objetivos, iniciativas ou indicadores suficientes para compor os números do exercício.',
    ])
@endif

@if(count($numeros) > 0)
<p class="rg-corpo">Síntese quantitativa do ciclo no exercício de {{ $dados['ano'] }}.</p>

<table class="rg-numeros">
    <tr>
        @foreach($numeros as $numero)
        <td class="rg-numero-card" style="width:{{ (int) (100 / max(1, count($numeros))) }}%;">
            <p class="rg-numero-valor">{{ $numero['valor'] }}</p>
            <p class="rg-numero-rotulo">{{ $numero['rotulo'] }}</p>
        </td>
        @endforeach
    </tr>
</table>
@endif
