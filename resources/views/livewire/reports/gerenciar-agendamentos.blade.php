<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 pb-0">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-calendar-check me-2 text-primary"></i>Agendamentos Ativos</h6>
    </div>
    <div class="card-body pt-2">
        <div class="list-group list-group-flush">
            @forelse($agendamentos as $item)
                <div class="list-group-item border-0 px-0 py-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                            {{ $item->dsc_frequencia }}
                        </span>
                        <div class="form-check form-switch transform-scale-08">
                            <input class="form-check-input" type="checkbox" wire:click="toggleStatus('{{ $item->cod_agendamento }}')" {{ $item->bln_ativo ? 'checked' : '' }} title="Pausar/Ativar">
                        </div>
                    </div>
                    
                    <h6 class="mb-1 fw-bold text-dark">{{ ucfirst($item->dsc_tipo_relatorio) }}</h6>
                    
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">
                                <i class="bi bi-arrow-right-circle me-1"></i>Próx: {{ \Carbon\Carbon::parse($item->dte_proxima_execucao)->format('d/m H:i') }}
                            </small>
                            @if(isset($item->txt_filtros['organizacao_id']))
                                <small class="text-muted d-block text-truncate" style="max-width: 150px; font-size: 0.75rem;">
                                    Org: {{ \App\Models\Organization::find($item->txt_filtros['organizacao_id'])?->sgl_organizacao ?? 'N/A' }}
                                </small>
                            @endif
                        </div>
                        <button wire:click="delete('{{ $item->cod_agendamento }}')" 
                                wire:confirm="Cancelar este agendamento?"
                                class="btn btn-link text-danger p-0 opacity-50 hover-opacity-100" style="font-size: 0.8rem;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-4">
                    <div class="mb-2">
                        <i class="bi bi-calendar-plus text-muted opacity-25" style="font-size: 2rem;"></i>
                    </div>
                    <p class="text-muted small mb-0">Nenhum agendamento configurado.</p>
                </div>
            @endforelse
        </div>
        
        @if($agendamentos->hasPages())
            <div class="mt-2 d-flex justify-content-center">
                {{ $agendamentos->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>
    <style>
        .transform-scale-08 { transform: scale(0.8); transform-origin: right center; }
        .hover-opacity-100:hover { opacity: 1 !important; }
    </style>
</div>