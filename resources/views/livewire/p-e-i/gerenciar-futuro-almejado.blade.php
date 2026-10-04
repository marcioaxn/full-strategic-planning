<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">
                        <a href="{{ route('objetivos.index') }}" wire:navigate class="text-decoration-none">Objetivos Estratégicos</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('objetivos.detalhes', $objetivo->cod_objetivo) }}" wire:navigate class="text-decoration-none">{{ Str::limit($objetivo->nom_objetivo, 40) }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Futuro Almejado</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-gray-800 mb-0">
                <i class="bi bi-stars me-2 text-primary"></i>Futuro Almejado
            </h2>
            <p class="text-muted mb-0">{{ $objetivo->nom_objetivo }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('objetivos.detalhes', $objetivo->cod_objetivo) }}" wire:navigate class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar ao Objetivo
            </a>
            @if($podeCriar)
            <button wire:click="create" class="btn btn-primary gradient-theme">
                <i class="bi bi-plus-lg me-1"></i> Adicionar Item
            </button>
            @endif
        </div>
    </div>

    <x-secao-educativa
        titulo="O que é o Futuro Almejado?"
        subtitulo="Dizer com clareza onde o objetivo está hoje e onde precisa estar até uma data."
        icone="stars"
        por-que="Sem um ponto de partida medido, não há como comparar o antes e o depois e saber se o órgão melhorou de verdade."
        :passos="[
            'Clique em Adicionar Item.',
            'Descreva a situação atual, com dados sempre que possível (é a linha de base).',
            'Descreva o futuro desejado em frases simples e concretas.',
            'Informe o indicador de referência, o valor da meta e o horizonte (a data-alvo, em geral o fim do ciclo).',
            'Revise o texto com a área responsável pelo objetivo.',
        ]"
        exemplo="O Departamento Nacional de Defesa Sanitária Vegetal (fictício) registra: hoje, 40% das análises de pragas saem em até 30 dias; até 2030, 90% sairão em até 15 dias. A meta de referência é “90% das análises concluídas em até 15 dias”."
        dica="toda meta precisa de linha de base e deve ser SMART: específica, mensurável, alcançável, relevante e com prazo."
        referencia="GPPEI p. 29, 32, 77–79 e 103 · Doran (1981)">
        É como medir a sua altura hoje e marcar na parede até onde você quer chegar no fim do ano.
        Para cada objetivo, registra-se a <strong>situação atual</strong> (o ponto de partida) e a <strong>situação desejada</strong>.
        O <strong>horizonte</strong> é o prazo para chegar lá, e a <strong>meta de referência</strong> é o número que vai provar se chegamos.
    </x-secao-educativa>

    @if (session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-rocket-takeoff me-2 text-primary"></i>Onde queremos chegar?
            </h5>
            <small class="text-muted">GPPEI/MGI 2025 + SMART (Doran, 1981) — compromisso mensurável e verificável</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th class="ps-4">Situação Atual</th>
                            <th>Futuro Almejado</th>
                            <th>Indicador de Referência</th>
                            <th class="text-center">Meta</th>
                            <th class="text-center">Horizonte</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($futuros as $futuro)
                            <tr>
                                <td class="ps-4 py-3" style="max-width:180px;">
                                    @if($futuro->dsc_situacao_atual)
                                        <span class="text-muted small">{{ $futuro->dsc_situacao_atual }}</span>
                                    @else
                                        <span class="text-muted small fst-italic">Não informado</span>
                                    @endif
                                </td>
                                <td style="max-width:220px;">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-arrow-right-circle-fill text-primary me-2 mt-1 flex-shrink-0"></i>
                                        <span class="small fw-semibold">{{ $futuro->dsc_futuro_almejado }}</span>
                                    </div>
                                </td>
                                <td class="small text-muted">{{ $futuro->dsc_indicador_referencia ?? '—' }}</td>
                                <td class="text-center">
                                    @if($futuro->vlr_referencia_meta !== null)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3">
                                            {{ number_format($futuro->vlr_referencia_meta, 2, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center small">
                                    @if($futuro->dte_horizonte)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2">
                                            {{ $futuro->dte_horizonte->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if($podeEditar)
                                    <button wire:click="edit('{{ $futuro->cod_futuro_almejado }}')" class="btn btn-sm btn-outline-secondary border-0" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @endif
                                    @if($podeExcluir)
                                    <button wire:click="delete('{{ $futuro->cod_futuro_almejado }}')" class="btn btn-sm btn-outline-danger border-0" title="Excluir"
                                            wire:confirm="Confirma a exclusão deste item?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-stars fs-2 d-block mb-2 text-muted opacity-25"></i>
                                    Nenhum futuro almejado cadastrado para este objetivo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header gradient-theme text-white border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-stars me-2"></i>{{ $futuroId ? 'Editar Futuro Almejado' : 'Novo Futuro Almejado' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal',false)"></button>
                </div>
                <form wire:submit="save">
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>SMART:</strong> O futuro almejado deve ser <strong>Específico, Mensurável, Alcançável, Relevante e Temporal</strong>. Um número, prazo e indicador transformam desejo em compromisso verificável.
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Situação Atual (linha de base qualitativa)</label>
                                <textarea wire:model="form.dsc_situacao_atual" class="form-control" rows="2"
                                          placeholder="Ex: Atualmente, 62% dos atendimentos são concluídos no prazo..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Futuro Almejado <span class="text-danger">*</span></label>
                                <textarea wire:model="form.dsc_futuro_almejado"
                                          class="form-control @error('form.dsc_futuro_almejado') is-invalid @enderror" rows="3"
                                          placeholder="Ex: Ao final do ciclo PEI, 90% dos atendimentos serão concluídos no prazo com satisfação ≥ 4..."></textarea>
                                @error('form.dsc_futuro_almejado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Indicador de Referência</label>
                                <input type="text" wire:model="form.dsc_indicador_referencia" class="form-control"
                                       placeholder="Ex: % de atendimentos no prazo">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Valor Meta</label>
                                <input type="number" step="0.01" wire:model="form.vlr_referencia_meta"
                                       class="form-control @error('form.vlr_referencia_meta') is-invalid @enderror"
                                       placeholder="Ex: 90">
                                @error('form.vlr_referencia_meta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Horizonte Temporal</label>
                                <input type="date" wire:model="form.dte_horizonte"
                                       class="form-control @error('form.dte_horizonte') is-invalid @enderror">
                                @error('form.dte_horizonte') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="$set('showModal',false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme rounded-pill px-5">
                            <i class="bi bi-check-lg me-1"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
