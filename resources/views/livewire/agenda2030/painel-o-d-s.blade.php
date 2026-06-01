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

        {{-- ═══════════ KPIs de cobertura ═══════════ --}}
        @php $totalOds = $todosOds->count() ?: 18; $pctCobertura = round(($qtdCobertos / $totalOds) * 100); @endphp
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

        {{-- ═══════════ Grid dos 17 ODS ═══════════ --}}
        <div class="card card-modern border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="fw-bold mb-0"><i class="bi bi-grid-3x3-gap text-success me-2"></i>Os 17 Objetivos de Desenvolvimento Sustentável</h5>
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
                                style="width:96px;opacity:{{ $coberto ? '1' : '.4' }};transition:all .18s ease;{{ $sel ? 'transform:translateY(-4px);' : '' }}"
                                title="ODS {{ $ods->num_ods }} — {{ $ods->nom_ods }}">
                            <div class="position-relative d-inline-block" style="{{ $sel ? 'box-shadow:0 0 0 3px #2e8b57;border-radius:10px;' : '' }}">
                                <x-ods-badge :ods="$ods" size="lg" />
                                @if($coberto)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success border border-2 border-white" style="font-size:.6rem;z-index:2;">
                                        {{ $ods->objetivos->count() }}
                                    </span>
                                @endif
                            </div>
                            <div class="mt-1 fw-semibold text-truncate" style="font-size:.62rem;color:{{ $ods->cod_cor }};max-width:96px;">
                                {{ $ods->nom_ods_abreviado }}
                            </div>
                        </button>
                    @endforeach
                </div>

                <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top flex-wrap">
                    <span class="small text-muted"><span class="badge rounded-pill bg-success">&nbsp;</span> Coberto (com objetivos vinculados)</span>
                    <span class="small text-muted"><span class="badge rounded-pill bg-secondary opacity-50">&nbsp;</span> Não coberto</span>
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
                                <h4 class="fw-bold mb-0" style="color:{{ $detalhe['ods']->cod_cor }};">
                                    ODS {{ $detalhe['ods']->num_ods }} · {{ $detalhe['ods']->nom_ods }}
                                </h4>
                                <button wire:click="selecionarOds({{ $detalhe['ods']->num_ods }})" class="btn btn-sm btn-light rounded-pill">
                                    <i class="bi bi-x-lg"></i> Fechar
                                </button>
                            </div>
                            @if($detalhe['ods']->dsc_ods)
                                <p class="text-muted mb-0 mt-1 small">{{ $detalhe['ods']->dsc_ods }}</p>
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
                                    $cor = $obj['atingimento'] >= 80 ? '#2e8b57' : ($obj['atingimento'] >= 50 ? '#d97706' : '#dc3545');
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
                                        <div class="fw-bold" style="font-size:1.2rem;color:{{ $cor }};">@brazil_percent($obj['atingimento'], 1)</div>
                                        <div class="progress" style="height:6px;">
                                            <div class="progress-bar" style="width:{{ min($obj['atingimento'], 100) }}%;background:{{ $cor }};"></div>
                                        </div>
                                        <div class="text-muted" style="font-size:.62rem;">atingimento {{ $ano }}</div>
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
