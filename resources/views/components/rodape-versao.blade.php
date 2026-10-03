{{--
    Rodapé com a versão em execução e o último deploy — presente em todos os
    layouts (app, public e guest), com ou sem login. Ver App\Support\VersaoAplicacao.

    $fixo: na tela de login o conteúdo ocupa a altura toda; fixado no pé, o
    rodapé continua visível sem rolar.
--}}
@props(['fixo' => false])

@php($versaoApp = \App\Support\VersaoAplicacao::atual())

<footer {{ $attributes->class([
    'rodape-versao border-top py-2 px-3 small text-body-secondary bg-body',
    'position-fixed bottom-0 start-0 end-0' => $fixo,
]) }} style="font-size: .75rem; {{ $fixo ? 'z-index: 1020;' : '' }}">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span>&copy; {{ now()->year }} {{ config('app.name') }}</span>
        <span class="font-monospace" title="Versão em execução{{ $versaoApp['branch'] ? ' — branch '.$versaoApp['branch'] : '' }}">
            <i class="bi bi-tag me-1" aria-hidden="true"></i>{{ \App\Support\VersaoAplicacao::paraExibicao() }}
        </span>
    </div>
</footer>
