<div>
    {{-- Header GPPEI --}}
    <x-module-header
        module="inaugurar"
        numero="01"
        title="Inaugurar e Integrar"
        subtitle="Planeje o processo e integre o PEI aos instrumentos de governo"
        icon="flag-fill"
        breadcrumb="Inaugurar e Integrar"
        :gppei="10">
        @if($peiAtivo)
        <x-slot name="actions">
            <span class="badge bg-light text-dark rounded-pill px-3 py-2">
                <i class="bi bi-calendar-range me-1"></i>{{ $peiAtivo->dsc_pei }}
            </span>
        </x-slot>
        @endif
    </x-module-header>

    @if(!$peiAtivo)
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3">
            <i class="bi bi-exclamation-triangle-fill fs-3"></i>
            <div>
                <strong>Nenhum ciclo PEI ativo.</strong>
                <p class="mb-0 small">Selecione ou crie um ciclo PEI para usar este módulo.</p>
            </div>
            <a href="{{ route('pei.ciclos') }}" wire:navigate class="btn btn-warning ms-auto">Ir para Ciclos PEI</a>
        </div>
    @else

    {{-- Abas --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <ul class="nav nav-tabs card-header-tabs gap-1" role="tablist">
                <li class="nav-item">
                    <button wire:click="$set('abaAtiva', 'planejamento')"
                            class="nav-link {{ $abaAtiva === 'planejamento' ? 'active fw-bold' : '' }} d-flex align-items-center gap-2"
                            type="button">
                        <i class="bi bi-clipboard-check"></i>
                        <span>Planejar o Processo</span>
                        @if($inaugurar)
                            <span class="badge bg-success rounded-pill" style="font-size:0.65rem;">✓</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="$set('abaAtiva', 'integracao')"
                            class="nav-link {{ $abaAtiva === 'integracao' ? 'active fw-bold' : '' }} d-flex align-items-center gap-2"
                            type="button">
                        <i class="bi bi-diagram-3"></i>
                        <span>Integração com Instrumentos</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="$set('abaAtiva', 'agenda')"
                            class="nav-link {{ $abaAtiva === 'agenda' ? 'active fw-bold' : '' }} d-flex align-items-center gap-2"
                            type="button">
                        <i class="bi bi-globe-americas"></i>
                        <span>Agenda 2030 (ODS)</span>
                        @if(count($odsAderidos) > 0)
                            <span class="badge bg-success rounded-pill" style="font-size:0.65rem;">{{ count($odsAderidos) }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="$set('abaAtiva', 'calendario')"
                            class="nav-link {{ $abaAtiva === 'calendario' ? 'active fw-bold' : '' }} d-flex align-items-center gap-2"
                            type="button">
                        <i class="bi bi-calendar-event"></i>
                        <span>Calendário de Eventos</span>
                    </button>
                </li>
            </ul>
        </div>

        {{-- ─── ABA 1: Planejar o Processo ─────────────────────────────────── --}}
        @if($abaAtiva === 'planejamento')
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Planejar o Planejamento</h5>
                    <p class="text-muted small mb-0">Defina a equipe, diretrizes da Alta Direção e metodologia do processo de planejamento.</p>
                    <div class="mt-1"><x-gppei-link :page="10" label="Passo 01 — Definir a Demanda" /></div>
                </div>
                <button wire:click="editarInaugurar" class="btn btn-primary gradient-theme-btn px-4">
                    <i class="bi bi-{{ $inaugurar ? 'pencil' : 'plus-lg' }} me-2"></i>
                    {{ $inaugurar ? 'Editar' : 'Preencher' }}
                </button>
            </div>

            @if($inaugurar)
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light rounded-3 h-100">
                            <div class="card-body p-3">
                                <p class="text-muted small text-uppercase fw-bold mb-2"><i class="bi bi-people me-1"></i>Equipe de Planejamento</p>
                                <p class="mb-0 small">{{ $inaugurar->txt_equipe ?: '—' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light rounded-3 h-100">
                            <div class="card-body p-3">
                                <p class="text-muted small text-uppercase fw-bold mb-2"><i class="bi bi-compass me-1"></i>Diretrizes da Alta Direção</p>
                                <p class="mb-0 small">{{ $inaugurar->txt_diretrizes ?: '—' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light rounded-3 h-100">
                            <div class="card-body p-3">
                                <p class="text-muted small text-uppercase fw-bold mb-2"><i class="bi bi-gear me-1"></i>Metodologia</p>
                                <p class="mb-0 small">{{ $inaugurar->txt_metodologia ?: '—' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light rounded-3 h-100">
                            <div class="card-body p-3">
                                <p class="text-muted small text-uppercase fw-bold mb-2"><i class="bi bi-calendar me-1"></i>Início do Processo</p>
                                <p class="mb-0 small fw-bold">{{ $inaugurar->dte_inicio_processo?->format('d/m/Y') ?: '—' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 rounded-3 h-100 {{ $inaugurar->bln_aprovado ? 'bg-success bg-opacity-10 border-success' : 'bg-light' }}">
                            <div class="card-body p-3 d-flex align-items-center gap-2">
                                <i class="bi bi-{{ $inaugurar->bln_aprovado ? 'check-circle-fill text-success' : 'clock text-warning' }} fs-4"></i>
                                <div>
                                    <p class="text-muted small text-uppercase fw-bold mb-0">Aprovação</p>
                                    <p class="mb-0 small fw-bold {{ $inaugurar->bln_aprovado ? 'text-success' : 'text-warning' }}">
                                        {{ $inaugurar->bln_aprovado ? 'Aprovado pela Alta Direção' : 'Aguardando aprovação' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($inaugurar->txt_observacoes)
                    <div class="col-12">
                        <div class="card border-0 bg-light rounded-3">
                            <div class="card-body p-3">
                                <p class="text-muted small text-uppercase fw-bold mb-2"><i class="bi bi-chat-text me-1"></i>Observações</p>
                                <p class="mb-0 small">{{ $inaugurar->txt_observacoes }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-clipboard-plus fs-1 opacity-25 d-block mb-3"></i>
                    <p class="mb-1 fw-semibold">Planejamento do processo ainda não preenchido.</p>
                    <p class="small mb-0">Registre a equipe, diretrizes e metodologia para dar início ao ciclo PEI.</p>
                </div>
            @endif
        </div>
        @endif

        {{-- ─── ABA 2: Integração com Instrumentos ─────────────────────────── --}}
        @if($abaAtiva === 'integracao')
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Integração com Instrumentos de Governo</h5>
                    <p class="text-muted small mb-0">Mapeie pontos de atenção e tarefas de alinhamento com PPA, LOA e Planos Setoriais. A Agenda 2030/ODS possui aba própria.</p>
                    <div class="mt-1"><x-gppei-link :page="14" label="Passo 02 — Integração com Instrumentos" /></div>
                </div>
                <button wire:click="novaIntegracao" class="btn btn-primary gradient-theme-btn px-4">
                    <i class="bi bi-plus-lg me-2"></i>Adicionar
                </button>
            </div>

            @if($integracoes->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-diagram-3 fs-1 opacity-25 d-block mb-3"></i>
                    <p class="mb-1 fw-semibold">Nenhuma integração registrada.</p>
                    <p class="small mb-0">Adicione o mapeamento de interfaces com os instrumentos de governo.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>Instrumento</th>
                                <th>Tipo</th>
                                <th>Intensidade</th>
                                <th>Pontos de Atenção</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($integracoes as $integ)
                            <tr>
                                <td class="fw-semibold">{{ $integ->dsc_instrumento }}</td>
                                <td><span class="badge bg-primary-subtle text-primary">{{ $integ->dsc_tipo_instrumento }}</span></td>
                                <td>
                                    @php
                                        $intClass = match($integ->dsc_intensidade) {
                                            'Alta'  => 'bg-danger-subtle text-danger',
                                            'Media' => 'bg-warning-subtle text-warning',
                                            default => 'bg-success-subtle text-success',
                                        };
                                    @endphp
                                    <span class="badge {{ $intClass }}">{{ $integ->dsc_intensidade }}</span>
                                </td>
                                <td class="small text-muted">{{ Str::limit($integ->txt_pontos_atencao ?? '—', 60) }}</td>
                                <td class="text-end">
                                    <button wire:click="editarIntegracao('{{ $integ->cod_integracao }}')" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button wire:click="confirmarExclusaoIntegracao('{{ $integ->cod_integracao }}')" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @endif

        {{-- ─── ABA 3: Agenda 2030 (ODS) ───────────────────────────────────── --}}
        @if($abaAtiva === 'agenda')
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1"><i class="bi bi-globe-americas text-success me-1"></i>Aderência à Agenda 2030</h5>
                    <p class="text-muted small mb-0">Declare a quais Objetivos de Desenvolvimento Sustentável o PEI da instituição adere (Passo 1 — mapeamento estratégico). É opcional.</p>
                    <div class="mt-1"><x-gppei-link :page="14" label="Integração com Instrumentos de Governo" /></div>
                </div>
                <button wire:click="salvarAgenda" class="btn btn-success px-4">
                    <i class="bi bi-check-lg me-2"></i>Salvar Aderência
                </button>
            </div>

            {{-- Resumo --}}
            <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded-3 bg-light">
                @php $totalOds = $todosOds->count() ?: 18; @endphp
                <div class="position-relative flex-shrink-0" style="width:48px;height:48px;">
                    <svg viewBox="0 0 36 36" style="width:48px;height:48px;transform:rotate(-90deg);">
                        <circle cx="18" cy="18" r="16" fill="none" stroke="#e9ecef" stroke-width="3"></circle>
                        <circle cx="18" cy="18" r="16" fill="none" stroke="#2e8b57" stroke-width="3"
                                stroke-dasharray="{{ round((count($odsAderidos)/$totalOds)*100) }} 100" stroke-linecap="round"></circle>
                    </svg>
                    <span class="position-absolute top-50 start-50 translate-middle fw-bold" style="font-size:.62rem;">{{ count($odsAderidos) }}/{{ $totalOds }}</span>
                </div>
                <div>
                    <p class="mb-0 fw-bold text-dark">{{ count($odsAderidos) }} {{ count($odsAderidos) == 1 ? 'ODS selecionado' : 'ODS selecionados' }}</p>
                    <p class="mb-0 text-muted small">Clique nos ícones abaixo para marcar a aderência institucional.</p>
                </div>
            </div>

            {{-- Grid dos 17 ODS --}}
            <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-start mb-4">
                @foreach($todosOds as $ods)
                    @php $ativo = in_array($ods->num_ods, $odsAderidos); @endphp
                    <button type="button"
                            wire:click="toggleOdsAderencia({{ $ods->num_ods }})"
                            class="border-0 bg-transparent p-0 text-center position-relative"
                            style="width:84px;opacity:{{ $ativo ? '1' : '.4' }};transition:all .18s ease;{{ $ativo ? 'transform:translateY(-3px);' : '' }}"
                            title="ODS {{ $ods->num_ods }} — {{ $ods->nom_ods }}">
                        <div class="position-relative d-inline-block" style="{{ $ativo ? 'box-shadow:0 0 0 3px #2e8b57;border-radius:10px;' : '' }}">
                            <x-ods-badge :ods="$ods" size="lg" />
                            @if($ativo)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-success p-1 border border-2 border-white" style="z-index:2;">
                                    <i class="bi bi-check-lg" style="font-size:.6rem;"></i>
                                </span>
                            @endif
                        </div>
                        <div class="mt-1 fw-semibold text-truncate" style="font-size:.6rem;color:{{ $ods->cod_cor }};max-width:84px;">
                            {{ $ods->nom_ods_abreviado }}
                        </div>
                    </button>
                @endforeach
            </div>

            {{-- Detalhamento por ODS selecionado --}}
            @if(count($odsAderidos) > 0)
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Detalhamento da Aderência</h6>
                <div class="d-flex flex-column gap-2">
                    @foreach($todosOds as $ods)
                        @if(in_array($ods->num_ods, $odsAderidos))
                            <div class="d-flex align-items-center gap-3 p-3 rounded-3 border bg-white">
                                <x-ods-badge :ods="$ods" size="md" />
                                <div class="flex-grow-1">
                                    <div class="fw-bold small text-dark mb-1">ODS {{ $ods->num_ods }} · {{ $ods->nom_ods }}</div>
                                    <input type="text"
                                           wire:model="odsContribuicoes.{{ $ods->num_ods }}"
                                           class="form-control form-control-sm"
                                           placeholder="Como a instituição contribui para este ODS? (opcional)">
                                </div>
                                <div style="width:130px;flex-shrink:0;">
                                    <label class="text-muted small text-uppercase fw-bold" style="font-size:.6rem;">Intensidade</label>
                                    <select wire:model="odsIntensidades.{{ $ods->num_ods }}" class="form-select form-select-sm">
                                        <option value="Alta">Alta</option>
                                        <option value="Media">Média</option>
                                        <option value="Baixa">Baixa</option>
                                    </select>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="text-end mt-3">
                    <button wire:click="salvarAgenda" class="btn btn-success px-4">
                        <i class="bi bi-check-lg me-2"></i>Salvar Aderência
                    </button>
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-globe2 fs-1 opacity-25 d-block mb-3"></i>
                    <p class="mb-1 fw-semibold">Nenhum ODS selecionado.</p>
                    <p class="small mb-0">A aderência à Agenda 2030 é opcional. Marque os ODS aos quais o PEI contribui, se desejar.</p>
                </div>
            @endif
        </div>
        @endif

        {{-- ─── ABA 4: Calendário de Eventos ───────────────────────────────── --}}
        @if($abaAtiva === 'calendario')
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Calendário de Eventos do PEI</h5>
                    <p class="text-muted small mb-0">Planeje reuniões, workshops e oficinas do ciclo de planejamento estratégico.</p>
                    <div class="mt-1"><x-gppei-link :page="140" label="Organizar Calendário de Eventos" /></div>
                </div>
                <button wire:click="novoEvento" class="btn btn-primary gradient-theme-btn px-4">
                    <i class="bi bi-plus-lg me-2"></i>Novo Evento
                </button>
            </div>

            @if($eventos->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-x fs-1 opacity-25 d-block mb-3"></i>
                    <p class="mb-1 fw-semibold">Nenhum evento cadastrado.</p>
                    <p class="small mb-0">Adicione reuniões, workshops e oficinas do ciclo PEI.</p>
                </div>
            @else
                <div class="row g-3">
                    @foreach($eventos as $ev)
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100 {{ $ev->bln_realizado ? 'opacity-75' : '' }}">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div>
                                        <span class="badge bg-secondary-subtle text-secondary small mb-1">{{ $ev->dsc_tipo_evento }}</span>
                                        <h6 class="fw-bold mb-0 {{ $ev->bln_realizado ? 'text-decoration-line-through text-muted' : '' }}">
                                            {{ $ev->dsc_titulo }}
                                        </h6>
                                    </div>
                                    @if($ev->bln_realizado)
                                        <span class="badge bg-success-subtle text-success flex-shrink-0"><i class="bi bi-check2"></i> Realizado</span>
                                    @endif
                                </div>
                                <p class="text-primary fw-bold small mb-1">
                                    <i class="bi bi-calendar-event me-1"></i>{{ $ev->dte_evento->format('d/m/Y') }}
                                </p>
                                @if($ev->dsc_objetivo)
                                    <p class="text-muted small mb-1">{{ Str::limit($ev->dsc_objetivo, 80) }}</p>
                                @endif
                                @if($ev->dsc_participantes)
                                    <p class="text-muted small mb-0"><i class="bi bi-people me-1"></i>{{ Str::limit($ev->dsc_participantes, 60) }}</p>
                                @endif
                            </div>
                            <div class="card-footer bg-transparent border-top py-2 d-flex justify-content-end gap-1">
                                <button wire:click="editarEvento('{{ $ev->cod_evento }}')" class="btn btn-xs btn-outline-primary py-1 px-2 small">
                                    <i class="bi bi-pencil me-1"></i>Editar
                                </button>
                                <button wire:click="confirmarExclusaoEvento('{{ $ev->cod_evento }}')" class="btn btn-xs btn-outline-danger py-1 px-2 small">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endif
    </div>

    {{-- ─── Modal: Planejar o Planejamento ────────────────────────────────── --}}
    @if($showFormInaugurar)
    <div class="modal fade show" tabindex="-1" style="display:block; background:rgba(0,0,0,.5); z-index:1055;" wire:click.self="$set('showFormInaugurar', false)">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-clipboard-check me-2"></i>Planejar o Planejamento</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showFormInaugurar', false)"></button>
                </div>
                <form wire:submit.prevent="salvarInaugurar">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Equipe de Planejamento <span class="text-danger">*</span></label>
                                <textarea wire:model="formInaugurar.txt_equipe" class="form-control @error('formInaugurar.txt_equipe') is-invalid @enderror" rows="3" placeholder="Descreva os membros, cargos e responsabilidades da equipe de planejamento..."></textarea>
                                @error('formInaugurar.txt_equipe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Diretrizes da Alta Direção</label>
                                <textarea wire:model="formInaugurar.txt_diretrizes" class="form-control" rows="3" placeholder="Expectativas, orientações e diretrizes capturadas da Alta Direção..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Metodologia a Adotar</label>
                                <textarea wire:model="formInaugurar.txt_metodologia" class="form-control" rows="2" placeholder="Ex.: GPPEI/MGI 2025, BSC, workshops presenciais..."></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Data de Início</label>
                                <input type="date" wire:model="formInaugurar.dte_inicio_processo" class="form-control @error('formInaugurar.dte_inicio_processo') is-invalid @enderror">
                                @error('formInaugurar.dte_inicio_processo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Data Fim Prevista</label>
                                <input type="date" wire:model="formInaugurar.dte_fim_previsto" class="form-control @error('formInaugurar.dte_fim_previsto') is-invalid @enderror">
                                @error('formInaugurar.dte_fim_previsto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 d-flex align-items-end pb-1">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="formInaugurar.bln_aprovado" id="aprovado">
                                    <label class="form-check-label fw-bold" for="aprovado">Aprovado pela Alta Direção</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Observações</label>
                                <textarea wire:model="formInaugurar.txt_observacoes" class="form-control" rows="2" placeholder="Notas adicionais..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="$set('showFormInaugurar', false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-5 rounded-pill">
                            <i class="bi bi-check-lg me-2"></i>Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ─── Modal: Integração com Instrumentos ────────────────────────────── --}}
    @if($showFormIntegracao)
    <div class="modal fade show" tabindex="-1" style="display:block; background:rgba(0,0,0,.5); z-index:1055;" wire:click.self="$set('showFormIntegracao', false)">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-diagram-3 me-2"></i>{{ $integracaoEditId ? 'Editar Integração' : 'Nova Integração' }}</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showFormIntegracao', false)"></button>
                </div>
                <form wire:submit.prevent="salvarIntegracao">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Instrumento <span class="text-danger">*</span></label>
                                <input type="text" wire:model="formIntegracao.dsc_instrumento" class="form-control @error('formIntegracao.dsc_instrumento') is-invalid @enderror" placeholder="Ex.: PPA 2024-2027">
                                @error('formIntegracao.dsc_instrumento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tipo</label>
                                <select wire:model="formIntegracao.dsc_tipo_instrumento" class="form-select">
                                    @foreach($tiposInstrumento as $t)
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Intensidade</label>
                                <select wire:model="formIntegracao.dsc_intensidade" class="form-select">
                                    @foreach($intensidades as $i)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Pontos de Atenção</label>
                                <textarea wire:model="formIntegracao.txt_pontos_atencao" class="form-control" rows="3" placeholder="Aspectos que o grupo de planejamento deve observar ao integrar este instrumento..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tarefas de Integração</label>
                                <textarea wire:model="formIntegracao.txt_tarefas" class="form-control" rows="3" placeholder="Ações concretas a executar para garantir o alinhamento..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="$set('showFormIntegracao', false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-5 rounded-pill">
                            <i class="bi bi-check-lg me-2"></i>Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ─── Modal: Evento ──────────────────────────────────────────────────── --}}
    @if($showFormEvento)
    <div class="modal fade show" tabindex="-1" style="display:block; background:rgba(0,0,0,.5); z-index:1055;" wire:click.self="$set('showFormEvento', false)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-calendar-event me-2"></i>{{ $eventoEditId ? 'Editar Evento' : 'Novo Evento' }}</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showFormEvento', false)"></button>
                </div>
                <form wire:submit.prevent="salvarEvento">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Título <span class="text-danger">*</span></label>
                                <input type="text" wire:model="formEvento.dsc_titulo" class="form-control @error('formEvento.dsc_titulo') is-invalid @enderror" placeholder="Ex.: Workshop de Análise SWOT">
                                @error('formEvento.dsc_titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Data <span class="text-danger">*</span></label>
                                <input type="date" wire:model="formEvento.dte_evento" class="form-control @error('formEvento.dte_evento') is-invalid @enderror">
                                @error('formEvento.dte_evento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tipo de Evento</label>
                                <select wire:model="formEvento.dsc_tipo_evento" class="form-select">
                                    @foreach($tiposEvento as $te)
                                        <option value="{{ $te }}">{{ $te }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Objetivo</label>
                                <textarea wire:model="formEvento.dsc_objetivo" class="form-control" rows="2" placeholder="Objetivo principal do evento..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Participantes</label>
                                <input type="text" wire:model="formEvento.dsc_participantes" class="form-control" placeholder="Ex.: Equipe de planejamento, Alta Direção, Gestores...">
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="formEvento.bln_realizado" id="realizado">
                                    <label class="form-check-label fw-bold" for="realizado">Evento já realizado</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="$set('showFormEvento', false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-5 rounded-pill">
                            <i class="bi bi-check-lg me-2"></i>Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ─── Modal: Confirmar Exclusão ─────────────────────────────────────── --}}
    @if($showDeleteModal)
    <div class="modal fade show" tabindex="-1" style="display:block; background:rgba(0,0,0,.5); z-index:1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-body p-5 text-center">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-1 mb-3 d-block"></i>
                    <h5 class="fw-bold">Confirmar Exclusão</h5>
                    <p class="text-muted">Esta ação é irreversível. Deseja excluir este registro?</p>
                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <button wire:click="$set('showDeleteModal', false)" class="btn btn-light px-4 rounded-pill">Cancelar</button>
                        <button wire:click="executarExclusao" class="btn btn-danger px-4 rounded-pill">Excluir</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ─── Modal: Sucesso ────────────────────────────────────────────────── --}}
    @if($showSuccessModal)
    <div class="modal fade show" tabindex="-1" style="display:block; background:rgba(0,0,0,.5); z-index:1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-body p-5 text-center">
                    <i class="bi bi-check-circle-fill text-success fs-1 mb-3 d-block"></i>
                    <h5 class="fw-bold">Sucesso!</h5>
                    <p class="text-muted">{{ $successMessage }}</p>
                    <button wire:click="$set('showSuccessModal', false)" class="btn btn-primary gradient-theme-btn px-5 rounded-pill mt-2">Continuar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @endif {{-- /peiAtivo --}}
</div>
