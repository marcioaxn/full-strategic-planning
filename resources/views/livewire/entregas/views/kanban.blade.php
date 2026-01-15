{{-- Garantir carregamento do SortableJS --}}
@once
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" defer></script>
@endonce

{{-- View Kanban --}}
<div
    class="notion-kanban"
    x-data="{
        retryCount: 0,
        maxRetries: 20,
        init() {
            this.waitForSortableAndInit();

            document.addEventListener('livewire:navigated', () => {
                this.retryCount = 0;
                this.waitForSortableAndInit();
            });

            Livewire.on('re-init-sortable', () => {
                this.$nextTick(() => {
                    this.setupSortable();
                });
            });
        },
        waitForSortableAndInit() {
            if (typeof Sortable !== 'undefined') {
                this.setupSortable();
            } else if (this.retryCount < this.maxRetries) {
                this.retryCount++;
                setTimeout(() => this.waitForSortableAndInit(), 100);
            } else {
                console.error('SortableJS não carregou após múltiplas tentativas');
            }
        },
        setupSortable() {
            if (typeof Sortable === 'undefined') {
                console.error('SortableJS not loaded');
                return;
            }

            document.querySelectorAll('.notion-kanban-cards').forEach(column => {
                if (column.sortableInstance) {
                    column.sortableInstance.destroy();
                }

                column.sortableInstance = new Sortable(column, {
                    group: 'entregas',
                    animation: 200,
                    ghostClass: 'notion-card-ghost',
                    chosenClass: 'notion-card-chosen',
                    dragClass: 'notion-card-drag',
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    handle: '.notion-card',
                    draggable: '.notion-card',
                    onStart: () => {
                        document.querySelectorAll('.notion-kanban-column').forEach(c => c.classList.add('is-dragging'));
                    },
                    onEnd: (evt) => {
                        document.querySelectorAll('.notion-kanban-column').forEach(c => c.classList.remove('is-dragging'));

                        const entregaId = evt.item.getAttribute('data-entrega-id');
                        const novoStatus = evt.to.getAttribute('data-status');
                        const novaPosicao = evt.newIndex + 1;

                        if (entregaId && novoStatus) {
                            $wire.moverParaStatus(entregaId, novoStatus, novaPosicao);
                        }
                    }
                });
            });
        }
    }"
>
    <style>
        .notion-card-ghost {
            opacity: 0.4;
            background: #ebf5ff !important;
            border: 2px dashed #4361ee !important;
        }
        .notion-card-chosen {
            transform: rotate(2deg);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
        }
        .is-dragging .notion-kanban-cards {
            min-height: 200px;
            background: rgba(0,0,0,0.02);
            border-radius: 8px;
        }
        .cursor-grab { cursor: grab; }
        .cursor-grabbing { cursor: grabbing; }

        /* Oculta o placeholder se houver qualquer card na coluna */
        .notion-kanban-cards:has(.notion-card) .empty-placeholder {
            display: none !important;
        }
        /* Garante que o placeholder apareça se não houver cards */
        .notion-kanban-cards .empty-placeholder {
            display: block;
        }
    </style>

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
            <div class="notion-kanban-cards" data-status="{{ $status }}" wire:key="kanban-col-{{ $status }}">
                {{-- Placeholder fixo para colunas vazias --}}
                <div class="text-center py-4 text-muted small empty-placeholder">
                    <i class="bi bi-inbox opacity-50"></i>
                    <p class="mb-0 mt-1">Nenhuma entrega</p>
                </div>

                @foreach($entregasDoStatus as $entrega)
                    @include('livewire.entregas.partials.card', ['entrega' => $entrega])
                @endforeach
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