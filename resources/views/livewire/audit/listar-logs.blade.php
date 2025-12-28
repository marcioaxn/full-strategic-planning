<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Auditoria</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Logs de Auditoria do Sistema</h2>
            </div>
        </div>
    </x-slot>

    <!-- Filtros Avançados -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 bg-light rounded-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Usuário</label>
                    <select wire:model.live="filtroUsuario" class="form-select">
                        <option value="">Todos os usuários</option>
                        @foreach($usuarios as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase">Evento</label>
                    <select wire:model.live="filtroEvento" class="form-select">
                        <option value="">Todos</option>
                        <option value="created">Criação</option>
                        <option value="updated">Alteração</option>
                        <option value="deleted">Exclusão</option>
                        <option value="restored">Restauração</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Módulo / Tabela</label>
                    <select wire:model.live="filtroModel" class="form-select">
                        <option value="">Todos os módulos</option>
                        @foreach($models as $m)
                            <option value="{{ $m }}">{{ str_replace('App\\Models\\', '', $m) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase">De:</label>
                    <input type="date" wire:model.live="dataInicio" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase">Até:</label>
                    <input type="date" wire:model.live="dataFim" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Logs -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-muted text-uppercase" style="font-size: 0.7rem;">
                    <tr>
                        <th class="ps-4">Data / Hora</th>
                        <th>Usuário</th>
                        <th>Módulo</th>
                        <th>Evento</th>
                        <th>IP / Origem</th>
                        <th class="text-end pe-4">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="fw-bold text-dark d-block">{{ $log->created_at->format('d/m/Y') }}</span>
                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs me-2 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 24px; height: 24px; font-size: 0.65rem;">
                                        {{ substr($log->user->name ?? 'S', 0, 1) }}
                                    </div>
                                    <span class="fw-semibold">{{ $log->user->name ?? 'Sistema' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ str_replace('App\\Models\\', '', $log->auditable_type) }}</span>
                                <small class="d-block text-muted" style="font-size: 0.65rem;">ID: ...{{ substr($log->auditable_id, -8) }}</small>
                            </td>
                            <td>
                                @php
                                    $eventClass = match($log->event) {
                                        'created' => 'success',
                                        'updated' => 'primary',
                                        'deleted' => 'danger',
                                        default => 'secondary'
                                    };
                                    $eventLabel = match($log->event) {
                                        'created' => 'Criação',
                                        'updated' => 'Alteração',
                                        'deleted' => 'Exclusão',
                                        default => $log->event
                                    };
                                @endphp
                                <span class="badge bg-{{ $eventClass }} bg-opacity-10 text-{{ $eventClass }} border border-{{ $eventClass }} border-opacity-25 rounded-pill px-3">
                                    {{ $eventLabel }}
                                </span>
                            </td>
                            <td>
                                <small class="fw-mono text-muted">{{ $log->ip_address }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <button wire:click="verDetalhes('{{ $log->id }}')" class="btn btn-sm btn-outline-secondary border-0">
                                    <i class="bi bi-search"></i> Detalhes
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Nenhum registro de auditoria encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top py-3">
            {{ $logs->links() }}
        </div>
    </div>

    <!-- Modal Detalhes do Log -->
    <div class="modal fade @if($showModal) show @endif" tabindex="-1" style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header gradient-theme text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock me-2"></i>Detalhes da Operação</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>
                <div class="modal-body p-0 bg-light">
                    @if($auditSelecionada)
                        <!-- Info Cabeçalho -->
                        <div class="p-4 bg-white border-bottom shadow-sm mb-3">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="small text-muted text-uppercase fw-bold d-block">Data / Hora</label>
                                    <span>{{ $auditSelecionada->created_at->format('d/m/Y H:i:s') }}</span>
                                </div>
                                <div class="col-md-4">
                                    <label class="small text-muted text-uppercase fw-bold d-block">Usuário</label>
                                    <span>{{ $auditSelecionada->user->name ?? 'Sistema' }}</span>
                                </div>
                                <div class="col-md-4">
                                    <label class="small text-muted text-uppercase fw-bold d-block">Endereço IP</label>
                                    <span class="fw-mono">{{ $auditSelecionada->ip_address }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Comparação de Dados -->
                        <div class="p-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-arrow-left-right me-2 text-primary"></i>Alterações nos Campos</h6>
                            <div class="table-responsive rounded-3 border bg-white">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr class="small text-muted">
                                            <th class="ps-3">Atributo</th>
                                            <th>Valor Anterior</th>
                                            <th>Valor Novo</th>
                                        </tr>
                                    </thead>
                                    <tbody style="font-size: 0.8rem;">
                                        @php
                                            $old = $auditSelecionada->old_values;
                                            $new = $auditSelecionada->new_values;
                                            $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
                                        @endphp
                                        @foreach($allKeys as $key)
                                            @if($key !== 'updated_at' && $key !== 'created_at')
                                                <tr>
                                                    <td class="ps-3 fw-bold text-muted" style="width: 25%;">{{ $key }}</td>
                                                    <td class="text-danger bg-danger bg-opacity-5">
                                                        <span class="text-break">{{ is_array($old[$key] ?? '') ? json_encode($old[$key]) : ($old[$key] ?? '-') }}</span>
                                                    </td>
                                                    <td class="text-success bg-success bg-opacity-5">
                                                        <span class="text-break">{{ is_array($new[$key] ?? '') ? json_encode($new[$key]) : ($new[$key] ?? '-') }}</span>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-4 p-3 bg-white rounded-3 border small">
                                <label class="fw-bold text-muted small text-uppercase">URL / Rota:</label>
                                <div class="text-break fw-mono mt-1">{{ $auditSelecionada->url }}</div>
                                
                                <label class="fw-bold text-muted small text-uppercase mt-3 d-block">User Agent:</label>
                                <div class="text-muted mt-1">{{ $auditSelecionada->user_agent }}</div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 p-4 pt-0 bg-light">
                    <button type="button" class="btn btn-secondary px-4" wire:click="$set('showModal', false)"> Fechar Visualização</button>
                </div>
            </div>
        </div>
    </div>
</div>