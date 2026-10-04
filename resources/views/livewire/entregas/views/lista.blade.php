{{-- View Lista --}}
{{-- Controles de escrita só para quem pode editar a iniciativa ($podeEditar, do
     componente): o perfil Consulta via selects e botões que davam 403. --}}
<div
    class="notion-lista"
    @if($podeEditar)
    x-data="{
        init() {
            this.prepararSortable(0);
        },
        // O SortableJS só era carregado pela visão Kanban: abrindo direto em
        // Lista (ou chegando por navegação interna) dava 'Sortable is not
        // defined' e reordenar não funcionava. Carrega se faltar e espera.
        prepararSortable(tentativa) {
            if (typeof window.Sortable === 'undefined') {
                if (!document.querySelector('script[data-sortablejs]')) {
                    const s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';
                    s.dataset.sortablejs = '1';
                    document.head.appendChild(s);
                }
                if (tentativa < 100) setTimeout(() => this.prepararSortable(tentativa + 1), 100);
                return;
            }
            new Sortable(this.$refs.listaBody, {
                animation: 150,
                handle: '.notion-drag-handle',
                ghostClass: 'notion-row-ghost',
                onEnd: (evt) => {
                    const items = [...evt.to.children].map(el => el.dataset.entregaId).filter(Boolean);
                    $wire.dispatch('reordenar-entregas', { ordem: items });
                }
            });
        }
    }"
    @endif
>
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 notion-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;"></th>
                        <th style="width: 40px;">
                            <i class="bi bi-check2-square text-muted"></i>
                        </th>
                        <th>Título</th>
                        <th style="width: 140px;">Status</th>
                        <th style="width: 100px;">Prioridade</th>
                        <th style="width: 100px;">Prazo</th>
                        <th style="width: 120px;">Responsável</th>
                        <th style="width: 80px;" class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody x-ref="listaBody">
                    @forelse($entregas as $entrega)
                        @php $editavel = $podeEditar && ! $entrega->trashed(); @endphp
                        <tr
                            class="notion-row {{ $entrega->bln_arquivado ? 'opacity-50' : '' }}"
                            data-entrega-id="{{ $entrega->cod_entrega }}"
                            wire:key="row-{{ $entrega->cod_entrega }}"
                        >
                            {{-- Drag Handle --}}
                            @if($editavel)
                                <td class="notion-drag-handle text-muted" style="cursor: grab;">
                                    <i class="bi bi-grip-vertical"></i>
                                </td>
                            @else
                                <td></td>
                            @endif

                            {{-- Checkbox Status --}}
                            <td>
                                @if($editavel)
                                    <div
                                        class="notion-checkbox {{ $entrega->isConcluida() ? 'checked' : '' }}"
                                        wire:click="atualizarStatus('{{ $entrega->cod_entrega }}', '{{ $entrega->isConcluida() ? 'Em Andamento' : 'Concluído' }}')"
                                        title="{{ $entrega->isConcluida() ? 'Marcar como em andamento' : 'Marcar como concluído' }}"
                                    >
                                        @if($entrega->isConcluida())
                                            <i class="bi bi-check-lg"></i>
                                        @endif
                                    </div>
                                @else
                                    <div class="notion-checkbox readonly {{ $entrega->isConcluida() ? 'checked' : '' }}" style="cursor: default;">
                                        @if($entrega->isConcluida())
                                            <i class="bi bi-check-lg"></i>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            {{-- Título --}}
                            <td>
                                <div
                                    class="notion-inline-edit {{ $entrega->isConcluida() ? 'text-decoration-line-through text-muted' : '' }}"
                                    x-data="{ editing: false, title: @js($entrega->dsc_entrega) }"
                                >
                                    {{-- Labels inline --}}
                                    @if($entrega->labels->count() > 0)
                                        <div class="d-flex flex-wrap gap-1 mb-1">
                                            @foreach($entrega->labels->take(2) as $label)
                                                <span class="notion-label cor-texto-legivel" style="background-color: {{ $label->dsc_cor }}20; --cor-texto: {{ $label->dsc_cor }}">
                                                    {{ $label->dsc_label }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <span
                                        x-show="!editing"
                                        @if($editavel) @dblclick="editing = true; $nextTick(() => $refs.titleInput.focus())" @endif
                                        class="cursor-pointer"
                                        wire:click="openDetails('{{ $entrega->cod_entrega }}')"
                                    >
                                        {{ $entrega->dsc_entrega }}
                                    </span>
                                    @if($editavel)
                                        <input
                                            x-show="editing"
                                            x-ref="titleInput"
                                            type="text"
                                            maxlength="500"
                                            x-model="title"
                                            @blur="editing = false; $wire.atualizarTitulo('{{ $entrega->cod_entrega }}', title)"
                                            @keydown.enter="editing = false; $wire.atualizarTitulo('{{ $entrega->cod_entrega }}', title)"
                                            @keydown.escape="editing = false"
                                            class="form-control form-control-sm notion-inline-input"
                                        >
                                    @endif
                                </div>
                            </td>

                            {{-- Status --}}
                            <td>
                                @if($editavel)
                                    <select
                                        wire:change="atualizarStatus('{{ $entrega->cod_entrega }}', $event.target.value)"
                                        class="form-select form-select-sm border-0 notion-status-select"
                                        style="background-color: {{ $entrega->getStatusColor() }}80; width: auto;"
                                    >
                                        @foreach(\App\Models\ActionPlan\Entrega::STATUS_OPTIONS as $status)
                                            <option value="{{ $status }}" {{ $entrega->bln_status === $status ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <span class="badge notion-status-select text-dark" style="background-color: {{ $entrega->getStatusColor() }};">{{ $entrega->bln_status }}</span>
                                @endif
                            </td>

                            {{-- Prioridade --}}
                            <td>
                                @php $prioridadeInfo = $entrega->getPrioridadeInfo(); @endphp
                                @if($editavel)
                                    <select
                                        wire:change="atualizarPrioridade('{{ $entrega->cod_entrega }}', $event.target.value)"
                                        class="form-select form-select-sm border-0 notion-priority-select notion-priority-{{ $entrega->cod_prioridade }}"
                                        style="width: auto;"
                                    >
                                        @foreach(\App\Models\ActionPlan\Entrega::PRIORIDADE_OPTIONS as $key => $info)
                                            <option value="{{ $key }}" {{ $entrega->cod_prioridade === $key ? 'selected' : '' }}>
                                                {{ $info['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <span class="badge notion-priority-select notion-priority-{{ $entrega->cod_prioridade }}">{{ $prioridadeInfo['label'] }}</span>
                                @endif
                            </td>

                            {{-- Prazo --}}
                            <td>
                                @if($editavel)
                                    <input
                                        type="date"
                                        value="{{ $entrega->dte_prazo?->format('Y-m-d') }}"
                                        min="{{ $plano->dte_inicio?->format('Y-m-d') }}"
                                        max="{{ $plano->dte_fim?->format('Y-m-d') }}"
                                        wire:change="atualizarPrazo('{{ $entrega->cod_entrega }}', $event.target.value)"
                                        class="form-control form-control-sm border-0 {{ $entrega->isAtrasada() ? 'text-danger fw-bold' : '' }}"
                                        style="width: auto;"
                                    >
                                @else
                                    <span class="small {{ $entrega->isAtrasada() ? 'text-danger fw-bold' : '' }}">{{ $entrega->dte_prazo?->format('d/m/Y') ?? '—' }}</span>
                                @endif
                            </td>

                            {{-- Responsável: a relação de responsáveis (a mesma do modal de
                                 edição). Com mais de um, o seletor único apagaria os demais:
                                 a troca fica no modal de edição. --}}
                            <td>
                                @if($editavel && $entrega->responsaveis->count() <= 1)
                                    <select
                                        x-on:change="$wire.atualizarResponsaveis('{{ $entrega->cod_entrega }}', $event.target.value ? [$event.target.value] : [])"
                                        class="form-select form-select-sm border-0"
                                        style="width: auto;"
                                    >
                                        <option value="">—</option>
                                        @foreach($usuarios as $usuario)
                                            <option value="{{ $usuario->id }}" {{ $entrega->responsaveis->contains('id', $usuario->id) ? 'selected' : '' }}>
                                                {{ $usuario->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <span class="small">{{ $entrega->responsaveis->pluck('name')->join(', ') ?: '—' }}</span>
                                @endif
                            </td>

                            {{-- Ações --}}
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @if($editavel)
                                            <li>
                                                <button class="dropdown-item" wire:click="openEditModal('{{ $entrega->cod_entrega }}')">
                                                    <i class="bi bi-pencil me-2"></i> Editar
                                                </button>
                                            </li>
                                        @endif
                                        <li>
                                            <button class="dropdown-item" wire:click="openDetails('{{ $entrega->cod_entrega }}')">
                                                <i class="bi bi-eye me-2"></i> Ver Detalhes
                                            </button>
                                        </li>
                                        @if($editavel)
                                            <li><hr class="dropdown-divider"></li>
                                            @if($entrega->bln_arquivado)
                                                <li>
                                                    <button class="dropdown-item" wire:click="desarquivar('{{ $entrega->cod_entrega }}')">
                                                        <i class="bi bi-archive me-2"></i> Desarquivar
                                                    </button>
                                                </li>
                                            @else
                                                <li>
                                                    <button class="dropdown-item" wire:click="arquivar('{{ $entrega->cod_entrega }}')">
                                                        <i class="bi bi-archive me-2"></i> Arquivar
                                                    </button>
                                                </li>
                                            @endif
                                        @endif
                                        @if($entrega->trashed())
                                            @can('delete', $entrega)
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button class="dropdown-item" wire:click="restaurar('{{ $entrega->cod_entrega }}')">
                                                        <i class="bi bi-arrow-counterclockwise me-2"></i> Restaurar
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger" wire:click="confirmDeleteEntrega('{{ $entrega->cod_entrega }}', true)">
                                                        <i class="bi bi-trash me-2"></i> Excluir definitivamente
                                                    </button>
                                                </li>
                                            @endcan
                                        @else
                                            @can('delete', $entrega)
                                            <li>
                                                <button class="dropdown-item text-danger" wire:click="confirmDeleteEntrega('{{ $entrega->cod_entrega }}')">
                                                    <i class="bi bi-trash me-2"></i> Excluir
                                                </button>
                                            </li>
                                            @endcan
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        {{-- Sub-entregas (indentadas) --}}
                        @foreach($entrega->subEntregas as $subEntrega)
                            <tr class="notion-row notion-subrow" data-entrega-id="{{ $subEntrega->cod_entrega }}">
                                <td></td>
                                <td>
                                    <div
                                        class="notion-checkbox {{ $subEntrega->isConcluida() ? 'checked' : '' }}"
                                        @if($editavel) wire:click="atualizarStatus('{{ $subEntrega->cod_entrega }}', '{{ $subEntrega->isConcluida() ? 'Em Andamento' : 'Concluído' }}')" @endif
                                    >
                                        @if($subEntrega->isConcluida())
                                            <i class="bi bi-check-lg"></i>
                                        @endif
                                    </div>
                                </td>
                                <td class="ps-4">
                                    <span class="text-muted me-2">└</span>
                                    <span class="{{ $subEntrega->isConcluida() ? 'text-decoration-line-through text-muted' : '' }}">
                                        {{ $subEntrega->dsc_entrega }}
                                    </span>
                                </td>
                                <td colspan="5"></td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 opacity-25"></i>
                                    <p class="mb-0 mt-2">Nenhuma entrega encontrada.</p>
                                    @can('create', [\App\Models\ActionPlan\Entrega::class, $plano])
                                        <button wire:click="openEditModal" class="btn btn-sm btn-primary mt-3">
                                            <i class="bi bi-plus-lg me-1"></i> Criar primeira entrega
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .notion-table th,
    .notion-table td {
        padding: 8px 12px;
        vertical-align: middle;
    }

    .notion-row:hover {
        background-color: #f7f7f5;
    }

    .notion-subrow {
        background-color: #fafafa;
    }

    .notion-checkbox {
        width: 18px;
        height: 18px;
        border: 2px solid #d4d4d4;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .notion-checkbox:hover {
        border-color: var(--bs-primary);
    }

    .notion-checkbox.checked {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
        color: white;
    }

    .notion-inline-input {
        padding: 2px 6px;
        font-size: inherit;
    }

    .notion-status-select,
    .notion-priority-select {
        font-size: 0.8rem;
        padding: 2px 8px;
        border-radius: 4px;
    }

    .notion-row-ghost {
        opacity: 0.4;
    }
</style>
