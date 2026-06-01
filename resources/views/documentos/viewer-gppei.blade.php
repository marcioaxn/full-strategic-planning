@php
    $secoes = [
        'Módulo 01 — Inaugurar e Integrar' => [
            ['label' => 'Planejar o Planejamento',    'page' => 10,  'icon' => 'flag-fill'],
            ['label' => 'Integração com Instrumentos','page' => 14,  'icon' => 'diagram-3'],
        ],
        'Módulo 02 — Planejar' => [
            ['label' => 'Cadeia de Valor',            'page' => 24,  'icon' => 'diagram-2'],
            ['label' => 'Análise Ambiental',          'page' => 26,  'icon' => 'binoculars'],
            ['label' => 'Referencial Estratégico',    'page' => 29,  'icon' => 'gem'],
            ['label' => 'Mapa Estratégico',           'page' => 30,  'icon' => 'map'],
            ['label' => 'Indicadores / Métricas',     'page' => 31,  'icon' => 'graph-up'],
            ['label' => 'Carteira de Projetos',       'page' => 32,  'icon' => 'kanban'],
            ['label' => 'Análise SWOT',               'page' => 66,  'icon' => 'grid-3x3-gap'],
            ['label' => 'Análise PESTEL',             'page' => 70,  'icon' => 'globe2'],
            ['label' => 'Metas SMART',                'page' => 77,  'icon' => 'check2-circle'],
            ['label' => 'Modelo Lógico',              'page' => 86,  'icon' => 'diagram-3-fill'],
            ['label' => 'Partes Interessadas',        'page' => 89,  'icon' => 'people'],
            ['label' => 'Matriz de Riscos',           'page' => 93,  'icon' => 'shield-exclamation'],
            ['label' => '5W2H',                       'page' => 116, 'icon' => 'list-columns'],
            ['label' => 'Matriz RACI',                'page' => 120, 'icon' => 'people-fill'],
        ],
        'Módulo 03 — Monitorar e Avaliar' => [
            ['label' => 'Monitoramento',              'page' => 42,  'icon' => 'speedometer2'],
            ['label' => 'Avaliação da Estratégia',    'page' => 46,  'icon' => 'clipboard-check'],
            ['label' => 'Comunicação',                'page' => 48,  'icon' => 'megaphone'],
            ['label' => 'RAE — Revisão da Estratégia','page' => 138, 'icon' => 'arrow-repeat'],
        ],
    ];
    $cores = [
        'Módulo 01 — Inaugurar e Integrar' => '#1a3a5c',
        'Módulo 02 — Planejar'             => '#2e6da4',
        'Módulo 03 — Monitorar e Avaliar'  => '#6a4c9c',
    ];
    $pdfBase = route('documentos.gppei');
@endphp

<x-app-layout>
    {{--
        Alpine controla apenas `pagina` (para highlight do item ativo).
        A navegação do iframe é feita via $refs.pdfFrame.src com ?p=N#page=N.
        O query param ?p=N muda a URL base a cada clique, forçando o browser
        a recarregar o iframe (da cache, se disponível). O fragmento #page=N
        instrui o visualizador de PDF nativo a saltar para a página correta.
    --}}
    <div class="d-flex"
         style="height: calc(100vh - 140px); gap: 1rem;"
         x-data="{ pagina: 1 }">

        {{-- Sumário lateral --}}
        <div class="flex-shrink-0 d-flex flex-column" style="width: 300px;">
            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-bottom py-3 px-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-list-ul me-2 text-primary"></i>Sumário do GPPEI
                    </h6>
                    <small class="text-muted">Clique para navegar até a seção</small>
                </div>

                <div class="card-body p-0 overflow-auto">
                    @foreach($secoes as $modulo => $itens)
                    <div class="px-3 py-2 sticky-top"
                         style="background:{{ $cores[$modulo] }}; z-index:1;">
                        <span class="fw-bold text-white"
                              style="font-size:.72rem; letter-spacing:.03em;">{{ $modulo }}</span>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($itens as $item)
                        <button type="button"
                                class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2 px-3 border-0"
                                @click="
                                    pagina = {{ $item['page'] }};
                                    $refs.pdfFrame.src = '{{ $pdfBase }}?p={{ $item['page'] }}#page={{ $item['page'] }}';
                                "
                                :class="pagina === {{ $item['page'] }} ? 'active' : ''">
                            <i class="bi bi-{{ $item['icon'] }} flex-shrink-0"
                               style="font-size:.85rem;"></i>
                            <span class="small flex-grow-1 text-start">{{ $item['label'] }}</span>
                            <span class="badge bg-light text-muted"
                                  style="font-size:.6rem;">p.{{ $item['page'] }}</span>
                        </button>
                        @endforeach
                    </div>
                    @endforeach
                </div>

                <div class="card-footer bg-white border-top py-2 px-3">
                    <a href="{{ $pdfBase }}"
                       target="_blank"
                       class="btn btn-sm btn-outline-primary w-100 rounded-pill">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir em nova aba
                    </a>
                </div>

            </div>
        </div>

        {{-- Visualizador PDF --}}
        <div class="flex-grow-1">
            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i>
                        <div>
                            <span class="fw-bold small d-block">Guia Prático de PEI — GPPEI/MGI 2025</span>
                            <small class="text-muted" style="font-size:.7rem;">
                                Página: <span x-text="pagina"></span>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0" style="background:#525659;">
                    {{--
                        src inicial: carrega o PDF na página 1.
                        Navegação subsequente feita via @click nos botões do sumário
                        usando $refs.pdfFrame.src = '...?p=N#page=N'.
                    --}}
                    <iframe x-ref="pdfFrame"
                            src="{{ $pdfBase }}#page=1"
                            style="width:100%; height:100%; border:0; min-height:600px;"
                            title="Guia GPPEI"></iframe>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
