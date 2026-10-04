<div>
    <x-module-header
        module="referencial"
        title="Agenda 2030 — ODS"
        subtitle="Contribuição institucional para os Objetivos de Desenvolvimento Sustentável"
        icon="globe-americas"
        breadcrumb="Agenda 2030"
        :gppei="14" />

    @if(!$peiAtivo)
        {{-- Sem PEI ativo --}}
        <div class="card card-modern border-dashed">
            <div class="card-body p-5 text-center">
                <i class="bi bi-globe-americas fs-1 text-muted opacity-50 d-block mb-3"></i>
                <h5 class="fw-bold">Nenhum ciclo PEI ativo</h5>
                <p class="text-muted mb-3">Ative ou selecione um ciclo PEI para visualizar a contribuição à Agenda 2030.</p>
                <a href="{{ route('pei.ciclos') }}" wire:navigate class="btn btn-primary gradient-theme-btn">
                    <i class="bi bi-calendar-range me-1"></i> Gerenciar Ciclos PEI
                </a>
            </div>
        </div>
    @else

        {{-- ═══════════ Como ler esta tela ═══════════ --}}
        <div class="card card-modern border-0 shadow-sm mb-4" x-data="{ aberto: false }">
            <div class="card-body p-3 px-4">
                <button type="button" class="btn btn-link p-0 text-decoration-none fw-semibold d-flex align-items-center gap-2" @click="aberto = !aberto">
                    <i class="bi bi-question-circle text-primary"></i> Para que serve esta tela e como lê-la
                    <i class="bi" :class="aberto ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
                <div x-show="aberto" x-cloak class="small text-muted mt-3">
                    <p class="mb-2">
                        Esta tela mostra <strong>como a estratégia do ciclo PEI selecionado contribui para a Agenda 2030</strong> —
                        os Objetivos de Desenvolvimento Sustentável (ODS) da ONU, mais o ODS 18 adotado pelo Brasil.
                        Ela não cria nada: <strong>só lê</strong> dois vínculos feitos em outras telas.
                    </p>
                    <ol class="mb-2 ps-3">
                        <li class="mb-1"><strong>Aderência declarada</strong> (estrela <i class="bi bi-star-fill text-warning"></i>):
                            na etapa <em>Inaugurar e Integrar → Agenda 2030</em>, a organização diz a quais ODS o ciclo se propõe a contribuir. É a intenção.</li>
                        <li class="mb-1"><strong>Cobertura efetiva</strong> (número verde no canto): ao cadastrar ou editar um
                            <em>Objetivo Estratégico</em>, marca-se até 3 ODS para os quais ele contribui. É a estratégia de fato ligada ao ODS.</li>
                    </ol>
                    <p class="mb-2">
                        <strong>Leitura:</strong> ODS colorido = há objetivo contribuindo; ODS apagado = nenhum objetivo. Clicar num ODS
                        mostra quais objetivos contribuem e o atingimento de cada um no ano de referência — é assim que se responde
                        “o que estamos entregando para este ODS e como está indo”.
                    </p>
                    <p class="mb-0">
                        <strong>Coerência:</strong> ODS declarado (estrela) sem nenhum objetivo vinculado é uma promessa sem estratégia que a sustente —
                        a tela avisa abaixo. O vínculo a ODS é <strong>opcional</strong>: nenhum ciclo é obrigado a cobrir todos.
                    </p>
                </div>
            </div>
        </div>

        {{-- ═══════════ KPIs de cobertura ═══════════ --}}
        @php $totalOds = $todosOds->count(); $pctCobertura = $totalOds > 0 ? round(($qtdCobertos / $totalOds) * 100) : 0; @endphp
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-modern border-0 shadow-sm h-100">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="position-relative flex-shrink-0" style="width:64px;height:64px;">
                            <svg viewBox="0 0 36 36" style="width:64px;height:64px;transform:rotate(-90deg);">
                                <circle cx="18" cy="18" r="16" fill="none" stroke="#e9ecef" stroke-width="3"></circle>
                                <circle cx="18" cy="18" r="16" fill="none" stroke="#2e8b57" stroke-width="3"
                                        stroke-dasharray="{{ $pctCobertura }} 100" stroke-linecap="round"></circle>
                            </svg>
                            <span class="position-absolute top-50 start-50 translate-middle fw-bold" style="font-size:.85rem;">{{ $pctCobertura }}%</span>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase fw-bold" style="font-size:.68rem;letter-spacing:.05em;">Cobertura da Agenda</div>
                            <div class="fw-bold text-dark" style="font-size:1.5rem;line-height:1.1;">{{ $qtdCobertos }} <span class="text-muted" style="font-size:1rem;">de {{ $totalOds }} ODS</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-modern border-0 shadow-sm h-100">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary flex-shrink-0" style="width:54px;height:54px;">
                            <i class="bi bi-bullseye fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase fw-bold" style="font-size:.68rem;letter-spacing:.05em;">Objetivos Vinculados</div>
                            <div class="fw-bold text-dark" style="font-size:1.5rem;line-height:1.1;">{{ $totalObjetivosVinculados }}</div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $totalVinculos }} {{ $totalVinculos === 1 ? 'vínculo' : 'vínculos' }} objetivo × ODS</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-modern border-0 shadow-sm h-100">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning flex-shrink-0" style="width:54px;height:54px;">
                            <i class="bi bi-flag fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase fw-bold" style="font-size:.68rem;letter-spacing:.05em;">ODS Não Cobertos</div>
                            <div class="fw-bold text-dark" style="font-size:1.5rem;line-height:1.1;">{{ $totalOds - $qtdCobertos }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════ Coerência: declarado sem objetivo ═══════════ --}}
        @if($declaradosSemObjetivo->isNotEmpty())
            <div class="alert alert-warning border-0 d-flex align-items-start gap-3 mb-4">
                <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
                <div class="small">
                    <strong>{{ $declaradosSemObjetivo->count() }} {{ $declaradosSemObjetivo->count() === 1 ? 'ODS declarado' : 'ODS declarados' }} na aderência do ciclo sem nenhum objetivo estratégico vinculado:</strong>
                    {{ $declaradosSemObjetivo->map(fn ($o) => 'ODS '.$o->num_ods.' ('.$o->nom_ods_abreviado.')')->join(', ', ' e ') }}.
                    Vincule um objetivo a esse ODS em <a href="{{ route('objetivos.index') }}" wire:navigate>Objetivos Estratégicos</a>
                    ou revise a aderência em <a href="{{ route('pei.inaugurar') }}" wire:navigate>Inaugurar e Integrar</a>.
                </div>
            </div>
        @endif

        {{-- ═══════════ Grid dos ODS ═══════════ --}}
        <div class="card card-modern border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="fw-bold mb-0"><i class="bi bi-grid-3x3-gap text-success me-2"></i>Os {{ $totalOds }} Objetivos de Desenvolvimento Sustentável</h5>
                <span class="text-muted small">Clique em um ODS para ver os objetivos estratégicos vinculados</span>
            </div>
            <div class="card-body p-4">
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-start">
                    @foreach($todosOds as $ods)
                        @php
                            $coberto = $ods->objetivos->isNotEmpty();
                            $sel = $odsAtivo === $ods->num_ods;
                        @endphp
                        <button type="button"
                                wire:click="selecionarOds({{ $ods->num_ods }})"
                                class="ods-grid-tile border-0 bg-transparent p-0 text-center position-relative"
                                style="width:96px;opacity:{{ $coberto ? '1' : '.7' }};transition:all .18s ease;{{ $sel ? 'transform:translateY(-4px);' : '' }}"
                                title="ODS {{ $ods->num_ods }} — {{ $ods->nom_ods }}">
                            <div class="position-relative d-inline-block" style="{{ $sel ? 'box-shadow:0 0 0 3px #2e8b57;border-radius:10px;' : '' }}">
                                <x-ods-badge :ods="$ods" size="lg" />
                                @if($coberto)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success border border-2 border-white" style="font-size:.6rem;z-index:2;">
                                        {{ $ods->objetivos->count() }}
                                    </span>
                                @endif
                                @if(isset($odsDeclarados[(int) $ods->num_ods]))
                                    <span class="position-absolute top-0 start-0 translate-middle text-warning" style="z-index:2;font-size:.95rem;text-shadow:0 0 2px #fff,0 0 2px #fff;"
                                          title="Aderência declarada no ciclo (intensidade {{ $odsDeclarados[(int) $ods->num_ods] }})">
                                        <i class="bi bi-star-fill"></i>
                                    </span>
                                @endif
                            </div>
                            <div class="mt-1 fw-semibold text-truncate text-body-emphasis" style="font-size:.7rem;max-width:96px;" title="{{ $ods->nom_ods_abreviado }}">
                                {{ $ods->nom_ods_abreviado }}
                            </div>
                        </button>
                    @endforeach
                </div>

                <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top flex-wrap">
                    <span class="small text-muted"><span class="badge rounded-pill bg-success">&nbsp;</span> Coberto (com objetivos vinculados)</span>
                    <span class="small text-muted"><span class="badge rounded-pill bg-secondary opacity-50">&nbsp;</span> Não coberto</span>
                    <span class="small text-muted"><i class="bi bi-star-fill text-warning"></i> Aderência declarada no ciclo</span>
                    <span class="small text-muted ms-auto"><i class="bi bi-info-circle me-1"></i>O número no canto indica quantos objetivos contribuem para o ODS</span>
                </div>
            </div>
        </div>

        {{-- ═══════════ Detalhamento do ODS selecionado ═══════════ --}}
        @if($detalhe)
            <div class="card card-modern border-0 shadow-sm mb-4 animate-fade-in" style="border-top:4px solid {{ $detalhe['ods']->cod_cor }} !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <x-ods-badge :ods="$detalhe['ods']" size="lg" />
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <h4 class="fw-bold mb-0 text-body-emphasis" style="border-left:5px solid {{ $detalhe['ods']->cod_cor }};padding-left:.6rem;">
                                    ODS {{ $detalhe['ods']->num_ods }} · {{ $detalhe['ods']->nom_ods }}
                                </h4>
                                <button wire:click="selecionarOds({{ $detalhe['ods']->num_ods }})" class="btn btn-sm btn-light rounded-pill">
                                    <i class="bi bi-x-lg"></i> Fechar
                                </button>
                            </div>
                            @if($detalhe['ods']->dsc_ods)
                                <p class="text-muted mb-0 mt-1 small">{{ $detalhe['ods']->dsc_ods }}</p>
                            @endif
                            @if($detalhe['declarado'])
                                <p class="small mb-0 mt-2">
                                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill"><i class="bi bi-star-fill me-1"></i>Aderência declarada · intensidade {{ $detalhe['declarado'] }}</span>
                                    @if($detalhe['contribuicao_declarada'])
                                        <span class="text-muted fst-italic ms-1">{{ $detalhe['contribuicao_declarada'] }}</span>
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>

                    @if(count($detalhe['objetivos']) > 0)
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-link-45deg me-1"></i>Objetivos Estratégicos que contribuem para este ODS
                        </h6>
                        <div class="d-flex flex-column gap-2">
                            @foreach($detalhe['objetivos'] as $obj)
                                @php
                                    $cor = $obj['atingimento'] === null ? '#6c757d'
                                        : ($obj['atingimento'] >= 80 ? '#2e8b57' : ($obj['atingimento'] >= 50 ? '#d97706' : '#dc3545'));
                                @endphp
                                <div class="d-flex align-items-center gap-3 p-3 rounded-3 border bg-light bg-opacity-50">
                                    <div class="flex-grow-1">
                                        <a href="{{ route('objetivos.detalhes', $obj['cod']) }}" wire:navigate class="fw-bold text-dark text-decoration-none hover-primary">
                                            {{ $obj['nome'] }}
                                        </a>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                            <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $obj['perspectiva'] }}</span>
                                            <span class="text-muted small"><i class="bi bi-graph-up me-1"></i>{{ $obj['qtd_kpis'] }} KPI(s)</span>
                                        </div>
                                        @if($obj['contribuicao'])
                                            <p class="text-muted small mb-0 mt-2 fst-italic">
                                                <i class="bi bi-quote me-1"></i>{{ $obj['contribuicao'] }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-center flex-shrink-0" style="width:90px;">
                                        @if($obj['atingimento'] === null && ($obj['tem_indicador'] ?? false))
                                            <div class="fw-semibold text-muted small" title="Nenhum indicador deste objetivo foi medido no ano.">Sem medição</div>
                                            <div class="text-muted" style="font-size:.62rem;">em {{ $ano }}</div>
                                        @elseif($obj['atingimento'] === null)
                                            <div class="fw-semibold text-muted small" title="O objetivo não tem indicador direto nem de iniciativa: não há o que medir.">Sem indicador</div>
                                            <div class="text-muted" style="font-size:.62rem;">sem medição em {{ $ano }}</div>
                                        @else
                                            <div class="fw-bold cor-texto-legivel" style="font-size:1.2rem;--cor-texto:{{ $cor }};">@brazil_percent($obj['atingimento'], 1)</div>
                                            <div class="progress" style="height:6px;">
                                                <div class="progress-bar" style="width:{{ min($obj['atingimento'], 100) }}%;background:{{ $cor }};"></div>
                                            </div>
                                            <div class="text-muted" style="font-size:.62rem;">atingimento {{ $ano }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox d-block fs-3 mb-2 opacity-50"></i>
                            Nenhum objetivo estratégico vinculado a este ODS ainda.
                            <div class="mt-2">
                                <a href="{{ route('objetivos.index') }}" wire:navigate class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="bi bi-plus-lg me-1"></i>Vincular nos Objetivos
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Estado: nenhum ODS vinculado em todo o PEI --}}
        @if($qtdCobertos === 0)
            <div class="alert alert-info border-0 d-flex align-items-start gap-3">
                <i class="bi bi-lightbulb-fill fs-4 text-info flex-shrink-0"></i>
                <div>
                    <h6 class="fw-bold mb-1">Comece a alinhar sua estratégia à Agenda 2030</h6>
                    <p class="mb-2 small">
                        Ainda nenhum objetivo estratégico deste ciclo está vinculado a um ODS. O vínculo é opcional —
                        ao criar ou editar um objetivo, você pode marcar até 3 ODS para os quais ele contribui.
                    </p>
                    <a href="{{ route('objetivos.index') }}" wire:navigate class="btn btn-sm btn-info text-white rounded-pill">
                        <i class="bi bi-bullseye me-1"></i>Ir para Objetivos Estratégicos
                    </a>
                </div>
            </div>
        @endif

    @endif

    <style>
        .ods-grid-tile:hover { transform: translateY(-4px) !important; opacity: 1 !important; }
        .animate-fade-in { animation: odsFadeIn .3s ease-out; }
        @keyframes odsFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</div>
