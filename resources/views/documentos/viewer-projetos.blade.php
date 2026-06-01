@php
    /*
     * Sumário do Guia Prático de Projetos (MGI/PNUD).
     * Páginas confirmadas a partir das referências x-projetos-link usadas no sistema.
     */
    $secoes = [
        'Introdução' => [
            ['label' => 'Apresentação e Objetivos',        'page' => 1,   'icon' => 'book'],
            ['label' => 'Como Usar Este Guia',             'page' => 7,   'icon' => 'info-circle'],
            ['label' => 'Estrutura dos Domínios',          'page' => 12,  'icon' => 'diagram-3'],
        ],
        'Domínio 1 — Iniciação' => [
            ['label' => 'Início do Domínio 1',             'page' => 15,  'icon' => 'rocket-takeoff'],
            ['label' => 'TAP — Termo de Abertura',         'page' => 23,  'icon' => 'file-earmark-text'],
        ],
        'Domínio 2 — Planejamento' => [
            ['label' => 'Início do Domínio 2',             'page' => 28,  'icon' => 'compass'],
            ['label' => 'Indicadores e Resultados-Chave',  'page' => 33,  'icon' => 'graph-up'],
            ['label' => 'EAP — Estrutura Analítica',       'page' => 36,  'icon' => 'diagram-2'],
        ],
        'Domínio 3 — Equipe e Responsabilidades' => [
            ['label' => 'Início do Domínio 3',             'page' => 85,  'icon' => 'people'],
            ['label' => 'Matriz RACI',                     'page' => 89,  'icon' => 'people-fill'],
        ],
        'Domínio 4 — Execução e Controle' => [
            ['label' => 'Início do Domínio 4',             'page' => 105, 'icon' => 'play-circle'],
            ['label' => 'Monitoramento de Entregas',       'page' => 115, 'icon' => 'check2-all'],
        ],
        'Domínio 5 — Partes Interessadas' => [
            ['label' => 'Início do Domínio 5',             'page' => 143, 'icon' => 'person-lines-fill'],
            ['label' => 'Plano de Comunicação',            'page' => 155, 'icon' => 'megaphone'],
        ],
        'Domínio 6 — Riscos e Qualidade' => [
            ['label' => 'Início do Domínio 6',             'page' => 165, 'icon' => 'shield-exclamation'],
            ['label' => 'Mapeamento de Riscos',            'page' => 170, 'icon' => 'exclamation-diamond'],
        ],
        'Domínio 7 — Impacto e Aprendizado' => [
            ['label' => 'Início do Domínio 7',             'page' => 227, 'icon' => 'lightbulb'],
            ['label' => 'Lições Aprendidas',               'page' => 230, 'icon' => 'journal-check'],
        ],
    ];

    $cores = [
        'Introdução'                          => '#475569',
        'Domínio 1 — Iniciação'               => '#1a3a5c',
        'Domínio 2 — Planejamento'            => '#2e6da4',
        'Domínio 3 — Equipe e Responsabilidades' => '#0891b2',
        'Domínio 4 — Execução e Controle'    => '#2e8b57',
        'Domínio 5 — Partes Interessadas'     => '#e07b39',
        'Domínio 6 — Riscos e Qualidade'      => '#dc2626',
        'Domínio 7 — Impacto e Aprendizado'   => '#6a4c9c',
    ];

    $pdfBase = route('documentos.projetos.pdf');
@endphp

<x-app-layout>
    {{--
        Mesmo mecanismo do viewer-gppei:
        Alpine controla `pagina` para highlight do item ativo.
        A navegação do iframe usa ?p=N#page=N via $refs.pdfFrame.src,
        forçando um novo request (servido do cache) com o fragmento correto.
    --}}
    <div class="d-flex"
         style="height: calc(100vh - 140px); gap: 1rem;"
         x-data="{ pagina: 1 }">

        {{-- Sumário lateral --}}
        <div class="flex-shrink-0 d-flex flex-column" style="width: 300px;">
            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-bottom py-3 px-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-journal-bookmark-fill me-2 text-warning"></i>Sumário do Guia
                    </h6>
                    <small class="text-muted">Guia Prático de Projetos · MGI/PNUD</small>
                </div>

                <div class="card-body p-0 overflow-auto">
                    @foreach($secoes as $dominio => $itens)
                    <div class="px-3 py-2 sticky-top"
                         style="background:{{ $cores[$dominio] ?? '#475569' }}; z-index:1;">
                        <span class="fw-bold text-white"
                              style="font-size:.72rem; letter-spacing:.03em;">{{ $dominio }}</span>
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
                       class="btn btn-sm btn-outline-warning w-100 rounded-pill">
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
                        <i class="bi bi-journal-bookmark-fill text-warning fs-5"></i>
                        <div>
                            <span class="fw-bold small d-block">Guia Prático de Projetos — MGI/PNUD</span>
                            <small class="text-muted" style="font-size:.7rem;">
                                Página: <span x-text="pagina"></span>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0" style="background:#525659;">
                    <iframe x-ref="pdfFrame"
                            src="{{ $pdfBase }}#page=1"
                            style="width:100%; height:100%; border:0; min-height:600px;"
                            title="Guia Prático de Projetos"></iframe>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
