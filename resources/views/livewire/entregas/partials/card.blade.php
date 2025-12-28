{{-- Card de Entrega (usado em Kanban e Lista) --}}
<div 
    class="notion-card {{ $entrega->bln_arquivado ? 'opacity-50' : '' }} {{ $entrega->isAtrasada() ? 'border-danger' : '' }}"
    data-entrega-id="{{ $entrega->cod_entrega }}"
    wire:click="openDetails('{{ $entrega->cod_entrega }}')"
    wire:key="card-{{ $entrega->cod_entrega }}"
>
    {{-- Labels --}}
    @if($entrega->labels->count() > 0)
        <div class="d-flex flex-wrap gap-1 mb-2">
            @foreach($entrega->labels->take(3) as $label)
                <span 
                    class="notion-label" 
                    style="background-color: {{ $label->dsc_cor }}20; color: {{ $label->dsc_cor }}"
                >
                    {{ $label->dsc_label }}
                </span>
            @endforeach
            @if($entrega->labels->count() > 3)
                <span class="notion-label" style="background-color: #e3e2e080; color: #6b6b6b">
                    +{{ $entrega->labels->count() - 3 }}
                </span>
            @endif
        </div>
    @endif

    {{-- Título --}}
    <div class="notion-card-title {{ $entrega->isConcluida() ? 'text-decoration-line-through text-muted' : '' }}">
        @if($entrega->dsc_tipo === 'heading')
            <strong>{{ $entrega->dsc_entrega }}</strong>
        @else
            {{ $entrega->dsc_entrega }}
        @endif
    </div>

    {{-- Meta Info --}}
    <div class="notion-card-meta">
        {{-- Prioridade --}}
        @php $prioridadeInfo = $entrega->getPrioridadeInfo(); @endphp
        @if($entrega->cod_prioridade !== 'media')
            <span class="notion-card-badge notion-priority-{{ $entrega->cod_prioridade }}">
                <i class="bi bi-{{ $prioridadeInfo['icon'] }}"></i>
                {{ $prioridadeInfo['label'] }}
            </span>
        @endif

        {{-- Prazo --}}
        @if($entrega->dte_prazo)
            <span class="notion-card-badge {{ $entrega->isAtrasada() ? 'bg-danger text-white' : 'bg-light text-dark' }}">
                <i class="bi bi-calendar-event"></i>
                {{ $entrega->dte_prazo->format('d/m') }}
            </span>
        @endif

        {{-- Sub-entregas --}}
        @if($entrega->subEntregas->count() > 0)
            @php
                $subTotal = $entrega->subEntregas->count();
                $subConcluidas = $entrega->subEntregas->where('bln_status', 'Concluído')->count();
            @endphp
            <span class="notion-card-badge bg-light text-dark">
                <i class="bi bi-check2-square"></i>
                {{ $subConcluidas }}/{{ $subTotal }}
            </span>
        @endif

        {{-- Comentários --}}
        @if($entrega->comentarios_count ?? $entrega->comentarios->count() > 0)
            <span class="notion-card-badge bg-light text-dark">
                <i class="bi bi-chat"></i>
                {{ $entrega->comentarios_count ?? $entrega->comentarios->count() }}
            </span>
        @endif

        {{-- Anexos --}}
        @if($entrega->anexos_count ?? $entrega->anexos->count() > 0)
            <span class="notion-card-badge bg-light text-dark">
                <i class="bi bi-paperclip"></i>
                {{ $entrega->anexos_count ?? $entrega->anexos->count() }}
            </span>
        @endif

        {{-- Spacer --}}
        <div class="flex-grow-1"></div>

        {{-- Responsáveis --}}
        @if($entrega->responsaveis->count() > 0)
            <div class="d-flex align-items-center">
                <div class="notion-avatars-stack d-flex">
                    @foreach($entrega->responsaveis->take(3) as $resp)
                        <div class="notion-card-avatar border border-white" style="margin-left: -8px;" title="{{ $resp->name }}">
                            {{ strtoupper(substr($resp->name, 0, 2)) }}
                        </div>
                    @endforeach
                </div>
                @if($entrega->responsaveis->count() > 3)
                    <span class="small text-muted ms-1">+{{ $entrega->responsaveis->count() - 3 }}</span>
                @endif
            </div>
        @endif
    </div>

    {{-- Indicador de Arquivado --}}
    @if($entrega->bln_arquivado)
        <div class="mt-2">
            <span class="badge bg-secondary">
                <i class="bi bi-archive me-1"></i> Arquivado
            </span>
        </div>
    @endif

    {{-- Indicador de Lixeira --}}
    @if($entrega->trashed())
        <div class="mt-2 d-flex gap-2">
            <button 
                wire:click.stop="restaurar('{{ $entrega->cod_entrega }}')" 
                class="btn btn-sm btn-outline-success"
            >
                <i class="bi bi-arrow-counterclockwise me-1"></i> Restaurar
            </button>
            <button 
                wire:click.stop="excluirPermanente('{{ $entrega->cod_entrega }}')" 
                class="btn btn-sm btn-outline-danger"
                onclick="return confirm('Excluir permanentemente?')"
            >
                <i class="bi bi-trash me-1"></i> Excluir
            </button>
        </div>
    @endif
</div>
