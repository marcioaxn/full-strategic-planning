{{-- Side Panel de Detalhes da Entrega --}}
<div class="notion-side-panel-backdrop" wire:click="closeDetails"></div>

<div class="notion-side-panel">
    {{-- Header --}}
    <div class="notion-side-panel-header">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                {{-- Breadcrumb --}}
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="text-muted small d-flex align-items-center">
                        <i class="bi bi-journal-text me-1"></i>
                        {{ Str::limit($plano->dsc_plano_de_acao, 40) }}
                    </span>
                    @if($entrega->entregaPai)
                        <i class="bi bi-chevron-right text-muted small" style="font-size: 0.7rem;"></i>
                        <a href="javascript:void(0)" wire:click="openDetails('{{ $entrega->entregaPai->cod_entrega }}')" class="text-muted small text-decoration-none hover-underline">
                            {{ Str::limit($entrega->entregaPai->dsc_entrega, 25) }}
                        </a>
                    @endif
                </div>
                
                {{-- Título --}}
                <h4 class="fw-bold mb-0 notion-title-display {{ $entrega->isConcluida() ? 'text-decoration-line-through text-muted' : '' }}">
                    {{ $entrega->dsc_entrega }}
                </h4>
            </div>
            
            <div class="d-flex gap-1 ms-3">
                @can('update', $plano)
                    <button class="btn btn-icon-notion" wire:click="openEditModal('{{ $entrega->cod_entrega }}')" title="Editar entrega">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                @endcan
                <button class="btn btn-icon-notion text-danger" wire:click="closeDetails" title="Fechar">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Propriedades (Grid) --}}
    <div class="notion-side-panel-section bg-white">
        <div class="notion-property-grid">
            {{-- Status --}}
            <div class="notion-property-row" wire:key="status-row-{{ $entrega->cod_entrega }}-{{ $entrega->updated_at->timestamp }}">
                <div class="notion-property-label">
                    <i class="bi bi-record-circle me-2"></i>Status
                </div>
                <div class="notion-property-value">
                    @can('update', $plano)
                        <div class="dropdown w-100" wire:key="status-dropdown-{{ $entrega->cod_entrega }}" wire:ignore.self>
                            <button class="notion-property-btn dropdown-toggle w-100 text-start" 
                                    type="button" 
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                <span class="notion-badge-status status-{{ Str::slug($entrega->bln_status) }}">
                                    <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i>
                                    {{ $entrega->bln_status }}
                                </span>
                            </button>
                            <ul class="dropdown-menu border-0 shadow-lg p-1" wire:ignore>
                                @foreach(\App\Models\ActionPlan\Entrega::STATUS_OPTIONS as $status)
                                    <li wire:key="opt-status-{{ $status }}">
                                        <button class="dropdown-item rounded-2 small" wire:click="atualizarStatus('{{ $entrega->cod_entrega }}', '{{ $status }}')">
                                            {{ $status }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <span class="notion-badge-status status-{{ Str::slug($entrega->bln_status) }}">
                            {{ $entrega->bln_status }}
                        </span>
                    @endcan
                </div>
            </div>

            {{-- Prioridade --}}
            <div class="notion-property-row" wire:key="prio-row-{{ $entrega->cod_entrega }}-{{ $entrega->updated_at->timestamp }}">
                <div class="notion-property-label">
                    <i class="bi bi-flag me-2"></i>Prioridade
                </div>
                <div class="notion-property-value">
                    @can('update', $plano)
                        @php $prio = $entrega->getPrioridadeInfo(); @endphp
                        <div class="dropdown w-100" wire:key="prio-dropdown-{{ $entrega->cod_entrega }}" wire:ignore.self>
                            <button class="notion-property-btn dropdown-toggle w-100 text-start" 
                                    type="button" 
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                <span class="notion-badge-priority notion-priority-{{ $entrega->cod_prioridade }}">
                                    <i class="bi bi-{{ $prio['icon'] }} me-1"></i>
                                    {{ $prio['label'] }}
                                </span>
                            </button>
                            <ul class="dropdown-menu border-0 shadow-lg p-1" wire:ignore>
                                @foreach(\App\Models\ActionPlan\Entrega::PRIORIDADE_OPTIONS as $key => $info)
                                    <li wire:key="opt-prio-{{ $key }}">
                                        <button class="dropdown-item rounded-2 small" wire:click="atualizarPrioridade('{{ $entrega->cod_entrega }}', '{{ $key }}')">
                                            <i class="bi bi-{{ $info['icon'] }} me-2"></i>{{ $info['label'] }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <span class="notion-badge-priority notion-priority-{{ $entrega->cod_prioridade }}">
                            {{ $entrega->getPrioridadeInfo()['label'] }}
                        </span>
                    @endcan
                </div>
            </div>

            {{-- Prazo --}}
            <div class="notion-property-row">
                <div class="notion-property-label">
                    <i class="bi bi-calendar-event me-2"></i>Prazo
                </div>
                <div class="notion-property-value">
                    @can('update', $plano)
                        <input type="date" 
                               value="{{ $entrega->dte_prazo?->format('Y-m-d') }}"
                               wire:change="atualizarPrazo('{{ $entrega->cod_entrega }}', $event.target.value)"
                               class="notion-inline-input {{ $entrega->isAtrasada() ? 'text-danger fw-bold' : '' }}">
                    @else
                        <span class="small {{ $entrega->isAtrasada() ? 'text-danger fw-bold' : '' }}">
                            {{ $entrega->dte_prazo?->format('d/m/Y') ?? 'Vazio' }}
                        </span>
                    @endcan
                </div>
            </div>

            {{-- Responsáveis --}}
            <div class="notion-property-row align-items-start">
                <div class="notion-property-label mt-1">
                    <i class="bi bi-people me-2"></i>Responsáveis
                </div>
                <div class="notion-property-value">
                    <div x-data="{ open: false }" class="position-relative">
                        @can('update', $plano)
                            <div class="notion-property-btn d-flex flex-wrap gap-1 align-items-center" @click="open = !open">
                                @forelse($entrega->responsaveis as $resp)
                                    <div class="notion-user-tag">
                                        <div class="notion-user-avatar-xs">{{ strtoupper(substr($resp->name, 0, 1)) }}</div>
                                        {{ explode(' ', $resp->name)[0] }}
                                    </div>
                                @empty
                                    <span class="text-muted small">Vazio</span>
                                @endforelse
                                <i class="bi bi-plus-lg ms-auto text-muted small"></i>
                            </div>

                            <div x-show="open" @click.away="open = false" class="notion-dropdown-floating shadow-lg border rounded-3 p-2 mt-1 z-3">
                                <div class="small text-muted mb-2 px-2 fw-bold text-uppercase" style="font-size: 0.6rem;">Atribuir Pessoas</div>
                                @php $respIds = $entrega->responsaveis->pluck('id')->toArray(); @endphp
                                <div class="d-flex flex-column gap-1 overflow-auto" style="max-height: 250px;">
                                    @foreach($usuarios as $usuario)
                                        @php $isChecked = in_array($usuario->id, $respIds); @endphp
                                        <div class="notion-dropdown-item {{ $isChecked ? 'selected' : '' }}"
                                             @click="$wire.atualizarResponsaveis('{{ $entrega->cod_entrega }}', 
                                                {{ json_encode($isChecked ? array_values(array_diff($respIds, [$usuario->id])) : array_values(array_merge($respIds, [$usuario->id]))) }})">
                                            <div class="notion-user-avatar-xs bg-primary text-white">{{ strtoupper(substr($usuario->name, 0, 1)) }}</div>
                                            <span class="flex-grow-1 small text-truncate">{{ $usuario->name }}</span>
                                            @if($isChecked) <i class="bi bi-check2 text-primary"></i> @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($entrega->responsaveis as $resp)
                                    <div class="notion-user-tag readonly">
                                        <div class="notion-user-avatar-xs">{{ strtoupper(substr($resp->name, 0, 1)) }}</div>
                                        {{ $resp->name }}
                                    </div>
                                @empty
                                    <span class="text-muted small">Sem responsáveis</span>
                                @endforelse
                            </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Labels --}}
    <div class="notion-side-panel-section">
        <h6 class="notion-section-header">
            <i class="bi bi-tags me-2"></i>LABELS
            @can('update', $plano)
                <button class="btn btn-plus-section" wire:click="openLabelsModal('{{ $entrega->cod_entrega }}')">
                    <i class="bi bi-plus-lg"></i>
                </button>
            @endcan
        </h6>
        
        <div class="d-flex flex-wrap gap-2">
            @forelse($entrega->labels as $label)
                <span class="notion-badge-label" style="background-color: {{ $label->dsc_cor }}20; color: {{ $label->dsc_cor }}; border: 1px solid {{ $label->dsc_cor }}40;">
                    {{ $label->dsc_label }}
                    @can('update', $plano)
                        <button wire:click="toggleLabel('{{ $entrega->cod_entrega }}', '{{ $label->cod_label }}')" class="btn-remove-tag">
                            <i class="bi bi-x"></i>
                        </button>
                    @endcan
                </span>
            @empty
                <span class="text-muted small fst-italic">Nenhuma etiqueta atribuída</span>
            @endforelse
        </div>
    </div>

    {{-- Anexos --}}
    <div class="notion-side-panel-section">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="notion-section-header mb-0"><i class="bi bi-paperclip me-2"></i>ANEXOS</h6>
            @can('update', $plano)
                <div x-data="{ uploading: false }" 
                     x-on:livewire-upload-start="uploading = true"
                     x-on:livewire-upload-finish="uploading = false"
                     x-on:livewire-upload-error="uploading = false">
                    <label for="upload-anexos" class="btn btn-plus-section">
                        <i class="bi bi-plus-lg"></i>
                    </label>
                    <input type="file" id="upload-anexos" wire:model="anexosUpload" multiple class="d-none">
                    <div x-show="uploading" class="spinner-border spinner-border-sm text-primary ms-2" role="status"></div>
                </div>
            @endcan
        </div>
        
        @if($entrega->anexos->count() > 0)
            <div class="notion-attachment-list">
                @foreach($entrega->anexos as $anexo)
                    <div class="notion-attachment-item">
                        <a href="{{ $anexo->getUrl() }}" target="_blank" class="notion-attachment-link">
                            <i class="bi {{ $anexo->getIcone() }} text-primary fs-5"></i>
                            <div class="ms-2 overflow-hidden">
                                <div class="text-truncate fw-medium" style="max-width: 280px;">{{ $anexo->dsc_nome_arquivo }}</div>
                                <div class="text-muted" style="font-size: 0.65rem;">{{ $anexo->getTamanhoFormatado() }} • {{ $anexo->created_at->format('d/m/Y') }}</div>
                            </div>
                        </a>
                        @can('update', $plano)
                            <button wire:click="excluirAnexo('{{ $anexo->cod_anexo }}')" wire:confirm="Excluir este anexo permanentemente?" class="btn-delete-item">
                                <i class="bi bi-trash3"></i>
                            </button>
                        @endcan
                    </div>
                @endforeach
            </div>
        @else
            <div class="notion-empty-area" onclick="document.getElementById('upload-anexos').click()">
                <i class="bi bi-cloud-arrow-up fs-4 mb-1"></i>
                <span>Arraste ou clique para anexar</span>
            </div>
        @endif
    </div>

    {{-- Comentários --}}
    <div class="notion-side-panel-section bg-light bg-opacity-25">
        <h6 class="notion-section-header mb-3"><i class="bi bi-chat-left-text me-2"></i>COMENTÁRIOS</h6>
        
        @can('update', $plano)
            <div class="notion-comment-input-container mb-4" x-data="{ comentario: '', submitting: false }">
                <textarea x-model="comentario" class="form-control notion-textarea" rows="2" placeholder="Adicionar comentário..."></textarea>
                <div class="d-flex justify-content-end mt-2">
                    <button type="button" class="btn btn-sm btn-primary notion-btn-send"
                            x-bind:disabled="!comentario.trim() || submitting"
                            @click="submitting = true; $wire.dispatch('adicionar-comentario', { entregaId: '{{ $entrega->cod_entrega }}', conteudo: comentario }); comentario = ''; submitting = false;">
                        Enviar
                    </button>
                </div>
            </div>
        @endcan
        
        <div class="notion-comments-thread">
            @forelse($entrega->comentarios as $comentario)
                @include('livewire.entregas.partials.comentario-item', ['comentario' => $comentario, 'level' => 1])
            @empty
                <div class="text-center py-4 opacity-50">
                    <i class="bi bi-chat-dots fs-3 d-block mb-2"></i>
                    <span class="small">Nenhuma conversa iniciada.</span>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Histórico --}}
    <div class="notion-side-panel-section pb-5">
        <h6 class="notion-section-header mb-3"><i class="bi bi-clock-history me-2"></i>HISTÓRICO</h6>
        <div class="notion-history-timeline">
            @foreach($entrega->historico->take(8) as $item)
                @php $acao = $item->getAcaoInfo(); @endphp
                <div class="notion-history-entry">
                    <div class="notion-history-marker bg-{{ $acao['color'] }}"></div>
                    <div class="notion-history-content">
                        <div class="small fw-bold text-dark">{{ $item->getDescricaoLegivel() }}</div>
                        <div class="text-muted" style="font-size: 0.65rem;">
                            {{ $item->usuario->name ?? 'Sistema' }} • {{ $item->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Footer Fixo --}}
    <div class="notion-side-panel-footer">
        @can('update', $plano)
            <div class="d-flex justify-content-between w-100">
                <button class="btn btn-sm btn-notion-secondary" wire:click="{{ $entrega->bln_arquivado ? 'desarquivar' : 'arquivar' }}('{{ $entrega->cod_entrega }}')">
                    <i class="bi bi-archive me-1"></i> {{ $entrega->bln_arquivado ? 'Desarquivar' : 'Arquivar' }}
                </button>
                <button class="btn btn-sm btn-notion-danger" wire:click="excluir('{{ $entrega->cod_entrega }}')" onclick="return confirm('Mover para lixeira?')">
                    <i class="bi bi-trash3 me-1"></i> Excluir
                </button>
            </div>
        @endcan
    </div>
</div>

<style>
    /* Notion Styling Variables */
    :root {
        --notion-text: #37352f;
        --notion-muted: #9b9a97;
        --notion-bg-hover: #efefed;
        --notion-border: #e4e4e4;
    }

    .notion-side-panel {
        position: fixed; top: 0; right: 0; width: 520px; height: 100vh;
        background: white; z-index: 1060; box-shadow: -10px 0 30px rgba(0,0,0,0.1);
        overflow-y: auto; color: var(--notion-text);
    }

    .notion-side-panel-backdrop {
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.2); z-index: 1050; backdrop-filter: blur(2px);
    }

    .notion-side-panel-header { padding: 30px 25px 20px; position: sticky; top: 0; background: white; z-index: 20; border-bottom: 1px solid #f5f5f5; }
    .notion-side-panel-section { padding: 20px 25px; border-bottom: 1px solid #f0f0f0; }
    
    .notion-title-display { font-size: 1.75rem; letter-spacing: -0.02em; line-height: 1.2; }
    .notion-section-header { font-size: 0.65rem; font-weight: 700; color: var(--notion-muted); text-transform: uppercase; margin-bottom: 15px; display: flex; align-items: center; }

    /* Property Grid */
    .notion-property-grid { display: flex; flex-direction: column; gap: 4px; }
    .notion-property-row { display: flex; align-items: center; min-height: 34px; padding: 2px 0; }
    .notion-property-label { width: 140px; color: var(--notion-muted); font-size: 0.85rem; display: flex; align-items: center; }
    .notion-property-value { flex: 1; }

    /* Custom Badges with high contrast */
    .notion-badge-status { padding: 3px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; }
    .notion-badge-priority { padding: 3px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; }
    
    /* Status Colors (Notion Style - High Contrast) */
    .status-nao-iniciado { background-color: #e3e2e0; color: #37352f; border: 1px solid #d1d1d0; }
    .status-em-andamento { background-color: #fdecc8; color: #856404; border: 1px solid #ffe69c; }
    .status-concluido { background-color: #dbeddb; color: #1c4d1c; border: 1px solid #a3cfbb; }
    .status-cancelado { background-color: #ffe2dd; color: #d44020; border: 1px solid #f1aeb5; }
    .status-suspenso { background-color: #d3e5ef; color: #004085; border: 1px solid #b8daff; }

    .notion-priority-urgente { background-color: #ffe2dd; color: #d44020; border: 1px solid #f1aeb5; }
    .notion-priority-alta { background-color: #fdecc8; color: #856404; border: 1px solid #ffe69c; }
    .notion-priority-media { background-color: #dbeddb; color: #1c4d1c; border: 1px solid #a3cfbb; }
    .notion-priority-baixa { background-color: #e3e2e0; color: #5a5a5a; border: 1px solid #dee2e6; }

    /* Form Controls */
    .notion-property-btn { border: none; background: transparent; padding: 4px 8px; border-radius: 4px; transition: background 0.2s; }
    .notion-property-btn:hover { background: var(--notion-bg-hover); }
    .notion-inline-input { border: none; background: transparent; padding: 4px 8px; width: 100%; border-radius: 4px; font-size: 0.85rem; }
    .notion-inline-input:hover { background: var(--notion-bg-hover); }
    .notion-inline-input:focus { outline: none; background: white; box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), 0.2); }

    /* Users & Tags */
    .notion-user-tag { display: inline-flex; align-items: center; gap: 6px; padding: 2px 8px 2px 4px; background: #f1f1f0; border-radius: 12px; font-size: 0.75rem; }
    .notion-user-avatar-xs { width: 18px; height: 18px; border-radius: 50%; font-size: 0.6rem; font-weight: bold; display: flex; align-items: center; justify-content: center; background: #ddd; }
    .notion-badge-label { padding: 2px 10px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; }
    .btn-remove-tag { border: none; background: transparent; margin-left: 4px; padding: 0; color: inherit; opacity: 0.6; }
    .btn-remove-tag:hover { opacity: 1; }

    /* Attachments */
    .notion-attachment-list { display: flex; flex-direction: column; gap: 6px; }
    .notion-attachment-item { display: flex; align-items: center; padding: 8px 12px; border-radius: 8px; transition: background 0.2s; position: relative; }
    .notion-attachment-item:hover { background: var(--notion-bg-hover); }
    .notion-attachment-link { display: flex; align-items: center; flex: 1; text-decoration: none; color: inherit; }
    .btn-delete-item { visibility: hidden; border: none; background: transparent; color: #dc3545; padding: 0 5px; }
    .notion-attachment-item:hover .btn-delete-item { visibility: visible; }

    /* History Timeline */
    .notion-history-timeline { border-left: 2px solid #f0f0f0; margin-left: 10px; padding-left: 20px; }
    .notion-history-entry { position: relative; margin-bottom: 15px; }
    .notion-history-marker { position: absolute; left: -26px; top: 4px; width: 10px; height: 10px; border-radius: 50%; border: 2px solid white; }

    /* Footer and Buttons */
    .notion-side-panel-footer { padding: 20px 25px; border-top: 1px solid #f0f0f0; position: sticky; bottom: 0; background: white; }
    .btn-icon-notion { border: none; background: transparent; width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--notion-muted); transition: all 0.2s; }
    .btn-icon-notion:hover { background: var(--notion-bg-hover); color: var(--notion-text); }
    .btn-notion-secondary { background: #f1f1f0; border: 1px solid #e4e4e4; color: #555; font-weight: 600; border-radius: 6px; }
    .btn-notion-secondary:hover { background: #e4e4e4; }
    .btn-notion-danger { background: #fff; border: 1px solid #f1aeb5; color: #dc3545; font-weight: 600; border-radius: 6px; }
    .btn-notion-danger:hover { background: #dc3545; color: white; }
</style>