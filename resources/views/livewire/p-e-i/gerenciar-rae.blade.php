<div>
    {{-- Header GPPEI --}}
    <x-module-header
        module="monitorar"
        numero="03"
        title="Revisão e Avaliação da Estratégia"
        subtitle="Documente as revisões periódicas do ciclo de gestão (RAE)"
        icon="arrow-repeat"
        breadcrumb="RAE"
        :gppei="138">
        @if($peiAtivo && $organizacaoId)
        <x-slot name="actions">
            <button wire:click="novaRae" class="btn btn-light rounded-pill px-4 fw-bold">
                <i class="bi bi-plus-lg me-2"></i>Nova RAE
            </button>
        </x-slot>
        @endif
    </x-module-header>

    @if(!$peiAtivo || !$organizacaoId)
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3">
            <i class="bi bi-exclamation-triangle-fill fs-3"></i>
            <div>
                <strong>Selecione uma organização e um ciclo PEI</strong> para registrar e consultar as RAEs.
            </div>
        </div>
    @else

    {{-- Orientação --}}
    <div class="alert alert-info border-0 shadow-sm mb-4 small">
        <i class="bi bi-info-circle me-2"></i>
        A <strong>RAE (Revisão e Avaliação da Estratégia)</strong> é realizada periodicamente para avaliar o progresso dos objetivos, identificar desvios e definir encaminhamentos. Documente cada reunião de revisão aqui.
    </div>

    @if($raes->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-arrow-repeat fs-1 text-muted opacity-25 d-block mb-3"></i>
                <h5 class="fw-bold">Nenhuma RAE registrada</h5>
                <p class="text-muted small mb-3">Registre a primeira revisão e avaliação da estratégia para documentar o ciclo de gestão.</p>
                <button wire:click="novaRae" class="btn btn-primary gradient-theme-btn px-4">
                    <i class="bi bi-plus-lg me-2"></i>Registrar Primeira RAE
                </button>
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach($raes as $rae)
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ $rae->dsc_tipo_reuniao }}</span>
                                    <h6 class="fw-bold mb-0 mt-1">
                                        Ref.: {{ $rae->dte_referencia->format('M/Y') }}
                                        @if($rae->dte_reuniao)
                                            <span class="text-muted fw-normal small ms-2">— Reunião: {{ $rae->dte_reuniao->format('d/m/Y') }}</span>
                                        @endif
                                    </h6>
                                </div>
                                @if($rae->num_progresso_geral !== null)
                                    <div class="d-flex align-items-center gap-2 ms-3">
                                        <div class="progress" style="width:80px;height:8px;">
                                            <div class="progress-bar {{ $rae->num_progresso_geral >= 70 ? 'bg-success' : ($rae->num_progresso_geral >= 40 ? 'bg-warning' : 'bg-danger') }}"
                                                 style="width:{{ $rae->num_progresso_geral }}%"></div>
                                        </div>
                                        <span class="small fw-bold">{{ number_format($rae->num_progresso_geral, 1) }}%</span>
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <button wire:click="gerarPdf('{{ $rae->cod_rae }}')"
                                        class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                        data-bs-toggle="tooltip" title="Baixar PDF desta RAE">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                </button>
                                <button wire:click="editarRae('{{ $rae->cod_rae }}')" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="bi bi-pencil me-1"></i>Editar
                                </button>
                                <button wire:click="confirmarExclusao('{{ $rae->cod_rae }}')" class="btn btn-sm btn-outline-danger rounded-pill px-2">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            @if($rae->txt_destaques_positivos)
                            <div class="col-md-4">
                                <div class="card border-0 bg-success-subtle h-100 rounded-3">
                                    <div class="card-body p-3">
                                        <p class="fw-bold small text-success text-uppercase mb-2">
                                            <i class="bi bi-check-circle me-1"></i>Destaques Positivos
                                        </p>
                                        <p class="small text-dark mb-0" style="white-space:pre-line;">{{ $rae->txt_destaques_positivos }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($rae->txt_problemas_identificados)
                            <div class="col-md-4">
                                <div class="card border-0 bg-danger-subtle h-100 rounded-3">
                                    <div class="card-body p-3">
                                        <p class="fw-bold small text-danger text-uppercase mb-2">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Problemas Identificados
                                        </p>
                                        <p class="small text-dark mb-0" style="white-space:pre-line;">{{ $rae->txt_problemas_identificados }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($rae->txt_encaminhamentos)
                            <div class="col-md-4">
                                <div class="card border-0 bg-primary-subtle h-100 rounded-3">
                                    <div class="card-body p-3">
                                        <p class="fw-bold small text-primary text-uppercase mb-2">
                                            <i class="bi bi-arrow-right-circle me-1"></i>Encaminhamentos
                                        </p>
                                        <p class="small text-dark mb-0" style="white-space:pre-line;">{{ $rae->txt_encaminhamentos }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @if(!empty($rae->json_participantes))
                        <div class="mt-3 pt-3 border-top">
                            <p class="text-muted small mb-1"><i class="bi bi-people me-1"></i><strong>Participantes:</strong></p>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($rae->json_participantes as $p)
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $p }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    {{-- Modal: Criar/Editar RAE --}}
    @if($showModal)
    <div class="modal fade show" tabindex="-1" style="display:block;background:rgba(0,0,0,.5);z-index:1055;" wire:click.self="$set('showModal',false)">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-arrow-repeat me-2"></i>{{ $raeEditId ? 'Editar' : 'Nova' }} RAE
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal',false)"></button>
                </div>
                <form wire:submit.prevent="salvarRae">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Período de Referência <span class="text-danger">*</span></label>
                                <input type="date" wire:model="form.dte_referencia" class="form-control @error('form.dte_referencia') is-invalid @enderror">
                                @error('form.dte_referencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Data da Reunião</label>
                                <input type="date" wire:model="form.dte_reuniao" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tipo</label>
                                <select wire:model="form.dsc_tipo_reuniao" class="form-select">
                                    @foreach($tiposReuniao as $t)
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Progresso Geral (%)</label>
                                <div class="input-group">
                                    <input type="number" wire:model.live="form.num_progresso_geral" class="form-control" min="0" max="100" step="0.1" placeholder="Ex: 67.5">
                                    <span class="input-group-text">%</span>
                                </div>
                                @if($form['num_progresso_geral'] !== '')
                                <div class="progress mt-1" style="height:4px;">
                                    <div class="progress-bar {{ $form['num_progresso_geral'] >= 70 ? 'bg-success' : ($form['num_progresso_geral'] >= 40 ? 'bg-warning' : 'bg-danger') }}"
                                         style="width:{{ min(100, max(0, $form['num_progresso_geral'])) }}%"></div>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Participantes</label>
                                <input type="text" wire:model="form.participantes_raw" class="form-control" placeholder="Nome1, Nome2, Cargo...">
                                <small class="text-muted">Separe por vírgula</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">
                                    <i class="bi bi-check-circle text-success me-1"></i>Destaques Positivos
                                </label>
                                <textarea wire:model="form.txt_destaques_positivos" class="form-control" rows="3"
                                          placeholder="O que avançou bem? Quais metas foram atingidas? Iniciativas bem-sucedidas..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">
                                    <i class="bi bi-exclamation-triangle text-danger me-1"></i>Problemas Identificados
                                </label>
                                <textarea wire:model="form.txt_problemas_identificados" class="form-control" rows="3"
                                          placeholder="Quais indicadores estão abaixo da meta? Iniciativas com atraso? Causas dos desvios..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">
                                    <i class="bi bi-arrow-right-circle text-primary me-1"></i>Encaminhamentos
                                </label>
                                <textarea wire:model="form.txt_encaminhamentos" class="form-control" rows="3"
                                          placeholder="Decisões tomadas, responsáveis, prazos para ação corretiva..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="$set('showModal',false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-5 rounded-pill">
                            <i class="bi bi-check-lg me-2"></i>Salvar RAE
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Confirmar Exclusão --}}
    @if($showDelete)
    <div class="modal fade show" tabindex="-1" style="display:block;background:rgba(0,0,0,.5);z-index:1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-body p-5 text-center">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-1 mb-3 d-block"></i>
                    <h5 class="fw-bold">Confirmar Exclusão</h5>
                    <p class="text-muted">Esta RAE será removida permanentemente.</p>
                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <button wire:click="$set('showDelete',false)" class="btn btn-light px-4 rounded-pill">Cancelar</button>
                        <button wire:click="excluir" class="btn btn-danger px-4 rounded-pill">Excluir</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @endif
</div>
