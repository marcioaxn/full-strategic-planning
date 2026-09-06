{{--
    Seção 4.1 do modelo. Segue o formato da Tabela A.1.1 do anexo (p. 8):
    Riscos · Como a organização lida com esses riscos · Status.

    NÃO traz causas nem consequências: são texto livre, frequentemente sobre
    pessoas e vulnerabilidades, e este documento circula fora do órgão.
--}}
@php $riscos = $secao['dados']['riscos'] ?? collect(); @endphp

@if($riscos->isEmpty())
    @include('relatorios.gestao.secoes._sem-registro', [
        'mensagem' => 'Nenhum risco foi registrado para este ciclo no módulo de Gestão de Riscos.',
    ])
@endif

@if($riscos->isNotEmpty())
<p class="rg-corpo">
    Riscos identificados e monitorados no ciclo, ordenados pelo nível de exposição
    (probabilidade × impacto).
</p>

<table class="rg-tabela">
    <thead>
        <tr>
            <th style="width:30%;">Risco</th>
            <th style="width:16%;">Categoria</th>
            <th style="width:12%;">Nível</th>
            <th style="width:20%;">Resposta</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($riscos as $risco)
        <tr>
            <td>{{ $risco->dsc_titulo }}</td>
            <td class="rg-centro">{{ $risco->dsc_categoria }}</td>
            <td class="rg-centro">{{ $risco->num_nivel_risco }}</td>
            <td class="rg-centro">{{ $risco->dsc_estrategia_resposta ?: '—' }}</td>
            <td class="rg-centro">{{ $risco->dsc_status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
