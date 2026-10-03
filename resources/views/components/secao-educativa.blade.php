{{--
    Parte educativa das telas: explica, em linguagem simples, o que a tela trata,
    por que importa e como usá-la. Mesma estrutura em todas as telas.

    Uso:
    <x-secao-educativa titulo="O que é a Cadeia de Valor?" subtitulo="..." referencia="GPPEI p. 45"
        :passos="['...', '...']" exemplo="..." dica="..." por-que="...">
        Texto do "O que é" (slot).
    </x-secao-educativa>

    Cores: corpo em var(--bs-body-bg) e texto em text-body / text-body-secondary,
    que passam o contraste mínimo (WCAG 4,5:1) nos temas claro e escuro.
--}}
@props([
    'titulo',
    'subtitulo' => null,
    'icone' => 'mortarboard',
    'porQue' => null,
    'passos' => [],
    'exemplo' => null,
    'dica' => null,
    'referencia' => null,
])

<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm mb-4 educational-card-gradient']) }} x-data="{ expanded: false }">
    <div class="card-header bg-transparent border-0 p-4">
        <button type="button" @click="expanded = !expanded" :aria-expanded="expanded.toString()"
                class="btn btn-link text-white text-decoration-none p-0 w-100 text-start d-flex align-items-center justify-content-between gap-3">
            <span class="d-flex align-items-center gap-3">
                <span class="icon-circle bg-white bg-opacity-25 flex-shrink-0">
                    <i class="bi bi-book-fill fs-4 text-white" aria-hidden="true"></i>
                </span>
                <span>
                    <span class="h5 fw-bold mb-1 text-white d-block">
                        <i class="bi bi-{{ $icone }} me-2" aria-hidden="true"></i>{{ $titulo }}
                    </span>
                    @if($subtitulo)
                        <span class="mb-0 text-white-50 small d-block">{{ $subtitulo }}</span>
                    @endif
                </span>
            </span>
            <i class="bi fs-4 text-white" :class="expanded ? 'bi-chevron-up' : 'bi-chevron-down'" aria-hidden="true"></i>
        </button>
    </div>

    <div x-show="expanded" x-transition.opacity.duration.200ms style="display: none;">
        <div class="card-body p-4 secao-educativa-corpo">
            <div class="row g-4">
                <div class="{{ ($passos || $exemplo) ? 'col-lg-6' : 'col-12' }}">
                    <h6 class="fw-bold secao-educativa-titulo mb-2">
                        <i class="bi bi-info-circle me-2" aria-hidden="true"></i>O que é
                    </h6>
                    <div class="text-body mb-3 lh-lg">{{ $slot }}</div>

                    @if($porQue)
                        <h6 class="fw-bold secao-educativa-titulo mb-2">
                            <i class="bi bi-star me-2" aria-hidden="true"></i>Por que importa
                        </h6>
                        <p class="text-body mb-0 lh-lg">{{ $porQue }}</p>
                    @endif
                </div>

                @if($passos || $exemplo)
                    <div class="col-lg-6">
                        @if($passos)
                            <h6 class="fw-bold secao-educativa-titulo mb-2">
                                <i class="bi bi-list-ol me-2" aria-hidden="true"></i>Como usar esta tela
                            </h6>
                            <ol class="text-body mb-3 ps-3 lh-lg">
                                @foreach($passos as $passo)
                                    <li>{{ $passo }}</li>
                                @endforeach
                            </ol>
                        @endif

                        @if($exemplo)
                            <div class="secao-educativa-exemplo rounded-3 p-3">
                                <div class="fw-bold mb-1"><i class="bi bi-lightbulb me-2" aria-hidden="true"></i>Exemplo</div>
                                <div class="mb-0">{{ $exemplo }}</div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            @if($dica || $referencia)
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-between align-items-md-center border-top mt-4 pt-3">
                    @if($dica)
                        <div class="text-body small"><i class="bi bi-exclamation-diamond text-warning-emphasis me-2" aria-hidden="true"></i><strong>Atenção:</strong> {{ $dica }}</div>
                    @endif
                    @if($referencia)
                        <div class="text-body-secondary small text-nowrap"><i class="bi bi-book me-1" aria-hidden="true"></i>{{ $referencia }}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
