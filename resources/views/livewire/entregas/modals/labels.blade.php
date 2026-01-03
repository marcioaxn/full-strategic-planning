{{-- Modal de Gerenciamento de Labels --}}
@if($showLabelsModal && $labelsEntregaId)
    <div class="modal fade show" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" wire:click.self="closeLabelsModal">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg notion-modal">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-muted small text-uppercase">Labels</h6>
                    <button type="button" class="btn-close small" wire:click="closeLabelsModal"></button>
                </div>
                
                <div class="modal-body p-3">
                    {{-- Lista de Labels Existentes --}}
                    <div class="d-flex flex-column gap-1 mb-3">
                        @php 
                            $entregaAtual = \App\Models\ActionPlan\Entrega::with('labels')->find($labelsEntregaId);
                            $idsSelecionados = $entregaAtual ? $entregaAtual->labels->pluck('cod_label')->toArray() : [];
                        @endphp

                        <div class="small text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.65rem;">Atribuir:</div>
                        @forelse($labels as $label)
                            <div 
                                wire:click="toggleLabel('{{ $labelsEntregaId }}', '{{ $label->cod_label }}')"
                                class="d-flex align-items-center justify-content-between p-2 rounded-2 cursor-pointer notion-label-option"
                                style="background-color: {{ in_array($label->cod_label, $idsSelecionados) ? $label->dsc_cor . '20' : 'transparent' }};"
                            >
                                <span class="notion-label" style="background-color: {{ $label->dsc_cor }}30; color: {{ $label->dsc_cor }};">
                                    {{ $label->dsc_label }}
                                </span>
                                @if(in_array($label->cod_label, $idsSelecionados))
                                    <i class="bi bi-check2 text-primary"></i>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-2 text-muted small fst-italic">
                                Nenhuma label criada.
                            </div>
                        @endforelse
                    </div>

                    {{-- Formulário de Criação --}}
                    <div class="border-top pt-3 mt-2">
                        <div class="small text-muted mb-2 text-uppercase fw-bold" style="font-size: 0.65rem;">Criar Nova Label:</div>
                        <div class="d-flex flex-column gap-2">
                            <input 
                                type="text" 
                                wire:model="novaLabelNome" 
                                class="form-control form-control-sm border-0 bg-light" 
                                placeholder="Nome da label..."
                                wire:keydown.enter="criarLabel"
                            >
                            <div class="d-flex align-items-center gap-2">
                                <input 
                                    type="color" 
                                    wire:model="novaLabelCor" 
                                    class="form-control form-control-color form-control-sm border-0 p-0" 
                                    title="Escolha uma cor"
                                >
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-primary flex-grow-1"
                                    wire:click="criarLabel"
                                    wire:loading.attr="disabled"
                                >
                                    <i class="bi bi-plus-lg"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 p-3 pt-0">
                    <button type="button" class="btn btn-sm btn-light w-100" wire:click="closeLabelsModal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .notion-label-option {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .notion-label-option:hover {
            background-color: #f1f1f0 !important;
        }
        .form-control-color {
            width: 30px;
            height: 30px;
            border-radius: 6px;
        }
    </style>
@endif