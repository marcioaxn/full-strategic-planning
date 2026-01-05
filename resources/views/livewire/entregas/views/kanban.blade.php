{{-- View Kanban --}}
<div 
    class="notion-kanban" 
    x-data="{ 
        init() {
            this.setupSortable();
            // Reinicializa quando o Livewire termina de atualizar o componente
            Livewire.hook('message.processed', (message, component) => {
                this.setupSortable();
            });
        },
        setupSortable() {
            document.querySelectorAll('.notion-kanban-cards').forEach(column => {
                if (column.sortable) return; // Evita múltiplas inicializações
                
                column.sortable = new Sortable(column, {
                    group: 'entregas',
                    animation: 200,
                    ghostClass: 'notion-card-ghost',
                    chosenClass: 'notion-card-chosen',
                    dragClass: 'notion-card-drag',
                    handle: '.notion-card', // Permite arrastar o card inteiro
                    onStart: () => {
                        document.querySelectorAll('.notion-kanban-column').forEach(c => c.classList.add('is-dragging'));
                    },
                    onEnd: (evt) => {
                        document.querySelectorAll('.notion-kanban-column').forEach(c => c.classList.remove('is-dragging'));
                        
                        const entregaId = evt.item.dataset.entregaId;
                        const novoStatus = evt.to.dataset.status;
                        const novaPosicao = evt.newIndex + 1;
                        
                        if (entregaId) {
                            $wire.moverParaStatus(entregaId, novoStatus, novaPosicao);
                        }
                    }
                });
            });
        }
    }"
>
    @foreach(\App\Models\ActionPlan\Entrega::STATUS_OPTIONS as $status)
        @php
            $entregasDoStatus = $entregasPorStatus[$status] ?? collect();
            $statusColor = \App\Models\ActionPlan\Entrega::STATUS_COLORS[$status] ?? '#e3e2e0';
        @endphp
        
        <div class="notion-kanban-column">
            {{-- Cabeçalho da Coluna --}}
            <div class="notion-kanban-header">
                <div class="notion-kanban-title">
                    <span class="notion-status-dot" style="background-color: {{ $statusColor }}"></span>
                    {{ $status }}
                    <span class="notion-kanban-count">({{ $entregasDoStatus->count() }})</span>
                </div>
                @can('update', $plano)
                    <button 
                        wire:click="openQuickAdd('{{ $status }}')" 
                        class="btn btn-sm btn-link text-muted p-0"
                        title="Adicionar"
                    >
                        <i class="bi bi-plus-lg"></i>
                    </button>
                @endcan
            </div>

            {{-- Cards --}}
            <div class="notion-kanban-cards" data-status="{{ $status }}">
                @forelse($entregasDoStatus as $entrega)
                    @include('livewire.entregas.partials.card', ['entrega' => $entrega])
                @empty
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-inbox opacity-50"></i>
                        <p class="mb-0 mt-1">Nenhuma entrega</p>
                    </div>
                @endforelse
            </div>

            {{-- Botão Adicionar (footer) --}}
            @can('update', $plano)
                <div class="notion-add-card" wire:click="openQuickAdd('{{ $status }}')">
                    <i class="bi bi-plus"></i>
                    <span>Adicionar</span>
                </div>
            @endcan
        </div>
    @endforeach
</div>

{{-- SortableJS será carregado via CDN ou npm --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endpush
