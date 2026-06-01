<div>
    {{-- Header GPPEI --}}
    <x-module-header
        module="monitorar"
        title="Lições Aprendidas"
        subtitle="Registre aprendizados, problemas e boas práticas dos projetos"
        icon="lightbulb-fill"
        breadcrumb="Lições Aprendidas"
        :projetos="227">
        <x-slot name="actions">
            <button wire:click="novaLicao" class="btn btn-light rounded-pill px-4 fw-bold">
                <i class="bi bi-plus-lg me-2"></i>Nova Lição
            </button>
        </x-slot>
    </x-module-header>

    {{-- Filtro --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <select wire:model.live="planoFiltro" class="form-select">
                        <option value="">Todos os Planos de Ação</option>
                        @foreach($planos as $pl)
                            <option value="{{ $pl->cod_plano_de_acao }}">{{ Str::limit($pl->dsc_plano_de_acao, 60) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <div wire:loading class="spinner-border text-primary spinner-border-sm"></div>
                </div>
            </div>
        </div>
    </div>

    @if($licoes->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-lightbulb fs-1 text-muted opacity-25 d-block mb-3"></i>
                <h5 class="fw-bold">Nenhuma lição aprendida registrada</h5>
                <p class="text-muted small mb-3">Documente aprendizados, problemas e melhorias identificadas durante a execução dos projetos.</p>
                <button wire:click="novaLicao" class="btn btn-primary gradient-theme-btn px-4 rounded-pill">
                    <i class="bi bi-plus-lg me-2"></i>Registrar Primeira Lição
                </button>
            </div>
        </div>
    @else
        @foreach($tipos as $tipo => $meta)
        @if($licoes->has($tipo))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-{{ $meta['color'] }}-subtle border-bottom d-flex align-items-center gap-2 py-3 px-4">
                <i class="bi bi-{{ $meta['icon'] }} text-{{ $meta['color'] }}"></i>
                <h6 class="fw-bold mb-0 text-{{ $meta['color'] }}">{{ $tipo }}</h6>
                <span class="badge bg-{{ $meta['color'] }} ms-auto">{{ $licoes[$tipo]->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($licoes[$tipo] as $licao)
                    <div class="list-group-item px-4 py-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-secondary-subtle text-secondary small">{{ $licao->dsc_categoria }}</span>
                                    <small class="text-muted">{{ $licao->plano?->dsc_plano_de_acao ?? '—' }}</small>
                                </div>
                                <p class="mb-1">{{ $licao->txt_descricao }}</p>
                                @if($licao->txt_recomendacao)
                                    <div class="d-flex align-items-start gap-2 mt-2 p-2 bg-light rounded-2">
                                        <i class="bi bi-arrow-right text-primary flex-shrink-0 mt-1" style="font-size:.75rem;"></i>
                                        <small class="text-dark">{{ $licao->txt_recomendacao }}</small>
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <button wire:click="editar('{{ $licao->cod_licao }}')" class="btn btn-xs btn-outline-primary py-1 px-2">
                                    <i class="bi bi-pencil" style="font-size:.7rem;"></i>
                                </button>
                                <button wire:click="confirmarExclusao('{{ $licao->cod_licao }}')" class="btn btn-xs btn-outline-danger py-1 px-2">
                                    <i class="bi bi-trash" style="font-size:.7rem;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        @endforeach
    @endif

    {{-- Modal --}}
    @if($showModal)
    <div class="modal fade show" tabindex="-1" style="display:block;background:rgba(0,0,0,.5);z-index:1055;" wire:click.self="$set('showModal',false)">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-lightbulb me-2"></i>{{ $licaoEditId ? 'Editar' : 'Nova' }} Lição Aprendida</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal',false)"></button>
                </div>
                <form wire:submit.prevent="salvar">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Plano de Ação <span class="text-danger">*</span></label>
                                <select wire:model="form.cod_plano_de_acao" class="form-select @error('form.cod_plano_de_acao') is-invalid @enderror">
                                    <option value="">Selecione o plano...</option>
                                    @foreach($planos as $pl)
                                        <option value="{{ $pl->cod_plano_de_acao }}">{{ $pl->dsc_plano_de_acao }}</option>
                                    @endforeach
                                </select>
                                @error('form.cod_plano_de_acao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tipo</label>
                                <select wire:model="form.dsc_tipo" class="form-select">
                                    @foreach($tipos as $t => $meta)
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Categoria</label>
                                <select wire:model="form.dsc_categoria" class="form-select">
                                    @foreach($categorias as $c)
                                        <option value="{{ $c }}">{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Descrição <span class="text-danger">*</span></label>
                                <textarea wire:model="form.txt_descricao" class="form-control @error('form.txt_descricao') is-invalid @enderror" rows="4"
                                          placeholder="O que foi aprendido, qual o problema encontrado, qual boas práticas identificadas..."></textarea>
                                @error('form.txt_descricao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Recomendação / Ação Proposta</label>
                                <textarea wire:model="form.txt_recomendacao" class="form-control" rows="2"
                                          placeholder="O que deve ser feito diferente em projetos futuros..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="$set('showModal',false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-5 rounded-pill">
                            <i class="bi bi-check-lg me-2"></i>Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Confirmar Exclusão --}}
    @if($showDelete)
    <div class="modal fade show" tabindex="-1" style="display:block;background:rgba(0,0,0,.5);z-index:1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-body p-5 text-center">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-1 mb-3 d-block"></i>
                    <h5 class="fw-bold">Confirmar Exclusão</h5>
                    <p class="text-muted small">Esta lição aprendida será removida.</p>
                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <button wire:click="$set('showDelete',false)" class="btn btn-light px-4 rounded-pill">Cancelar</button>
                        <button wire:click="excluir" class="btn btn-danger px-4 rounded-pill">Excluir</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
