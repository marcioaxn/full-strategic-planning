<div class="card border-0 bg-light rounded-3 h-100">
    <div class="card-body p-3">
        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
            <p class="fw-semibold mb-0 small text-dark" style="line-height:1.4;">{{ $ativ->dsc_atividade }}</p>
            <div class="d-flex gap-1 flex-shrink-0">
                <button wire:click="editarAtividade('{{ $ativ->cod_atividade_cadeia_valor }}')" class="btn btn-xs btn-outline-primary py-0 px-1">
                    <i class="bi bi-pencil" style="font-size:.7rem;"></i>
                </button>
                <button wire:click="confirmarExcluirAtividade('{{ $ativ->cod_atividade_cadeia_valor }}')" class="btn btn-xs btn-outline-danger py-0 px-1">
                    <i class="bi bi-trash" style="font-size:.7rem;"></i>
                </button>
            </div>
        </div>

        @if($ativ->perspectiva)
            <span class="badge bg-primary-subtle text-primary small mb-2">{{ $ativ->perspectiva->dsc_perspectiva }}</span>
        @endif

        {{-- Processos internos --}}
        @if($ativ->processos->isNotEmpty())
            <hr class="my-2">
            <p class="x-small text-muted text-uppercase fw-bold mb-1" style="font-size:.65rem;">Processos</p>
            @foreach($ativ->processos as $proc)
                <div class="d-flex align-items-center gap-1 mb-1">
                    <i class="bi bi-arrow-right-short text-muted" style="font-size:.75rem;"></i>
                    <span class="x-small text-dark" style="font-size:.72rem;">{{ Str::limit($proc->dsc_transformacao, 50) }}</span>
                    <button wire:click="editarProcesso('{{ $proc->cod_processo_atividade_cadeia_valor }}')" class="btn btn-xs btn-link p-0 ms-auto text-muted">
                        <i class="bi bi-pencil" style="font-size:.6rem;"></i>
                    </button>
                    <button wire:click="confirmarExcluirProcesso('{{ $proc->cod_processo_atividade_cadeia_valor }}')" class="btn btn-xs btn-link p-0 text-danger">
                        <i class="bi bi-x" style="font-size:.7rem;"></i>
                    </button>
                </div>
            @endforeach
        @endif

        <button wire:click="novoProcesso('{{ $ativ->cod_atividade_cadeia_valor }}')"
                class="btn btn-xs btn-outline-secondary w-100 mt-2 py-1" style="font-size:.7rem;">
            <i class="bi bi-plus me-1"></i>Processo
        </button>
    </div>
</div>
