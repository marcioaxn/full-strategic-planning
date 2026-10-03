{{--
    Texto gerado pela IA dentro de um relatório em PDF.

    A IA escreve em Markdown (**negrito**, listas com "*", títulos com "#").
    Antes, o texto era impresso com nl2br(e(...)) e os asteriscos saíam crus no
    PDF — 61 marcações "**" numa única página do Relatório Executivo.

    Str::markdown com html_input=strip: só a marcação Markdown vira HTML; HTML
    bruto vindo do texto é descartado.

    Parâmetro: $texto
--}}
<div class="ai-texto">{!! \Illuminate\Support\Str::markdown((string) $texto, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
