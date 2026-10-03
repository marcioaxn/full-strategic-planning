<div>
    {{-- Header GPPEI --}}
    <x-module-header
        module="cadeia-valor"
        numero="02"
        title="Cadeia de Valor"
        subtitle="Macroprocessos e atividades que geram valor público"
        icon="diagram-2-fill"
        breadcrumb="Cadeia de Valor"
        :gppei="24">
        @if($peiAtivo)
        <x-slot name="actions">
            @if($podeExportar)
            <button wire:click="gerarPdf" class="btn btn-light rounded-pill px-3" data-bs-toggle="tooltip" title="Exportar Cadeia de Valor em PDF">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
            </button>
            @endif
            @if($podeCriar)
            <button wire:click="novaAtividade" class="btn btn-light rounded-pill px-4 fw-bold">
                <i class="bi bi-plus-lg me-2"></i>Nova Atividade
            </button>
            @endif
        </x-slot>
        @endif
    </x-module-header>

    <x-secao-educativa
        titulo="O que é a Cadeia de Valor?"
        subtitulo="Um retrato de tudo o que o órgão entrega à sociedade e de como faz isso."
        icone="diagram-2"
        por-que="É a base para entender como o órgão existe e funciona, e para achar gargalos, pontos fortes e pontos fracos antes de definir os objetivos."
        :passos="[
            'Clique em Nova Atividade.',
            'Escolha o tipo: finalística (entrega direta ao cidadão), de gestão (dá direção e controle) ou de suporte (pessoas, TI, orçamento, compras).',
            'Descreva a atividade em poucas palavras, sem deixar dúvida.',
            'Se quiser, detalhe os processos: entrada, transformação e saída.',
            'Revise o diagrama para que fique claro para quem não conhece o órgão.',
        ]"
        exemplo="Na Fundação Nacional de Bibliotecas Públicas (fictícia), as atividades finalísticas são “Fomentar acervos municipais” e “Capacitar bibliotecários”; a de gestão é “Planejar e monitorar a estratégia”; as de suporte são “Gerir pessoas”, “Gerir TI” e “Gerir orçamento e contratos”."
        dica="não tente fazer um diagrama em que “caiba tudo”, mas também não o simplifique a ponto de perder as ligações entre as partes."
        referencia="GPPEI p. 24–25, 34 e 64–65 · Porter (1985)">
        Pense numa padaria: o pão que chega ao cliente é a entrega final, mas por trás dele há comprar farinha, assar e limpar o forno.
        A Cadeia de Valor desenha isso para o órgão público: as atividades <strong>finalísticas</strong> entregam algo diretamente ao cidadão;
        as <strong>de gestão</strong> dão direção e controle; as <strong>de suporte</strong> sustentam tudo, como pessoas, TI, orçamento e compras.
    </x-secao-educativa>

    @if(!$peiAtivo)
        <div class="alert alert-warning border-0 shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Selecione um ciclo PEI para gerenciar a Cadeia de Valor.
        </div>
    @else

    {{--
        Diagrama Visual — um bloco por tipo declarado em
        AtividadeCadeiaValor::TIPOS, montado em CadeiaDeValor::agruparPorTipo().

        Antes havia dois blocos escritos à mão ('Finalística' e 'Suporte'). Com
        eles, acrescentar um tipo fazia a opção aparecer no combo, o cliente
        salvar — e a atividade sumir da tela, porque nenhum bloco a lia.
    --}}
    @foreach($grupos as $grupo)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-{{ $grupo['cor'] }} bg-opacity-10 border-0 py-3 px-4">
            <div class="d-flex align-items-center gap-2">
                <i class="bi {{ $grupo['icone'] }} text-{{ $grupo['cor'] }} fs-5"></i>
                <div>
                    <h6 class="fw-bold mb-0 text-{{ $grupo['cor'] }}">{{ $grupo['titulo'] }}</h6>
                    @if($grupo['ajuda'])
                        <small class="text-muted">{{ $grupo['ajuda'] }}</small>
                    @endif
                </div>
                <span class="badge bg-{{ $grupo['cor'] }} ms-auto">{{ $grupo['itens']->count() }}</span>
            </div>
        </div>
        <div class="card-body p-3">
            @if($grupo['itens']->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="bi {{ $grupo['icone'] }} fs-1 opacity-25 d-block mb-2"></i>
                    <p class="small mb-0">Nenhum item cadastrado em {{ $grupo['titulo'] }}.</p>
                </div>
            @else
                <div class="row g-3">
                    @foreach($grupo['itens'] as $ativ)
                    <div class="col-md-4">
                        @include('livewire.p-e-i.partials.cadeia-card', ['ativ' => $ativ])
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @endforeach

    {{-- Modal: Atividade --}}
    @if($showModalAtividade)
    <div class="modal fade show" tabindex="-1" style="display:block;background:rgba(0,0,0,.5);z-index:1055;" wire:click.self="$set('showModalAtividade',false)">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-diagram-2 me-2"></i>{{ $atividadeEditId ? 'Editar' : 'Nova' }} Atividade</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModalAtividade',false)"></button>
                </div>
                <form wire:submit.prevent="salvarAtividade">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Descrição <span class="text-danger">*</span></label>
                            <textarea wire:model="formAtividade.dsc_atividade" class="form-control @error('formAtividade.dsc_atividade') is-invalid @enderror" rows="3" placeholder="Descreva a atividade..."></textarea>
                            @error('formAtividade.dsc_atividade') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tipo</label>
                                <select wire:model="formAtividade.dsc_tipo" class="form-select">
                                    @foreach($tipos as $t)
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Perspectiva BSC (opcional)</label>
                                <select wire:model="formAtividade.cod_perspectiva" class="form-select">
                                    <option value="">Nenhuma</option>
                                    @foreach($perspectivas as $p)
                                        <option value="{{ $p->cod_perspectiva }}">{{ $p->dsc_perspectiva }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Ordem</label>
                                <input type="number" wire:model="formAtividade.num_ordem" class="form-control" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="$set('showModalAtividade',false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-5 rounded-pill"><i class="bi bi-check-lg me-2"></i>Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Processo --}}
    @if($showModalProcesso)
    <div class="modal fade show" tabindex="-1" style="display:block;background:rgba(0,0,0,.5);z-index:1055;" wire:click.self="$set('showModalProcesso',false)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-arrow-left-right me-2"></i>{{ $processoEditId ? 'Editar' : 'Novo' }} Processo</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModalProcesso',false)"></button>
                </div>
                <form wire:submit.prevent="salvarProcesso">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Entradas</label>
                            <input type="text" wire:model="formProcesso.dsc_entrada" class="form-control" placeholder="Recursos e insumos necessários...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Transformação/Processo <span class="text-danger">*</span></label>
                            <textarea wire:model="formProcesso.dsc_transformacao" class="form-control @error('formProcesso.dsc_transformacao') is-invalid @enderror" rows="3" placeholder="Descreva o processo ou transformação..."></textarea>
                            @error('formProcesso.dsc_transformacao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold small text-uppercase text-muted">Saídas</label>
                            <input type="text" wire:model="formProcesso.dsc_saida" class="form-control" placeholder="Produtos ou resultados gerados...">
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="$set('showModalProcesso',false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-5 rounded-pill"><i class="bi bi-check-lg me-2"></i>Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Confirmar Exclusão --}}
    @if($showDeleteModal)
    <div class="modal fade show" tabindex="-1" style="display:block;background:rgba(0,0,0,.5);z-index:1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-body p-5 text-center">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-1 mb-3 d-block"></i>
                    <h5 class="fw-bold">Confirmar Exclusão</h5>
                    <p class="text-muted small">Esta ação é irreversível.</p>
                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <button wire:click="$set('showDeleteModal',false)" class="btn btn-light px-4 rounded-pill">Cancelar</button>
                        <button wire:click="executarExclusao" class="btn btn-danger px-4 rounded-pill">Excluir</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @endif
</div>
