<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('audit.index') }}" wire:navigate class="text-decoration-none">Auditoria</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Log #{{ $log->id }}</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-gray-800 mb-0">
                <i class="bi bi-clock-history me-2 text-primary"></i>Detalhes da Auditoria
            </h2>
            <p class="text-muted mb-0">
                Evento: <span class="badge bg-{{ $log->event === 'created' ? 'success' : ($log->event === 'updated' ? 'warning' : 'danger') }}">{{ ucfirst($log->event) }}</span> 
                em {{ $log->created_at->format('d/m/Y H:i:s') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('audit.index') }}" wire:navigate class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Coluna Esquerda: Metadados -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="card-title mb-0 fw-bold">Quem executou?</h6>
                </div>
                <div class="card-body">
                    @if($log->user)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-circle bg-primary text-white me-3" style="width: 40px; height: 40px; display:flex; align-items:center; justify-content:center; border-radius:50%;">
                                {{ substr($log->user->name, 0, 2) }}
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $log->user->name }}</h6>
                                <small class="text-muted">{{ $log->user->email }}</small>
                            </div>
                        </div>
                    @else
                        <div class="text-muted fst-italic mb-3">Usuário Sistema / Não Identificado</div>
                    @endif

                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">IP:</span>
                            <span class="font-monospace">{{ $log->ip_address }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">User Agent:</span>
                            <span class="text-end text-truncate" style="max-width: 150px;" title="{{ $log->user_agent }}">{{ $log->user_agent }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">URL:</span>
                            <span class="text-end text-truncate" style="max-width: 150px;" title="{{ $log->url }}">{{ $log->url }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light py-3">
                    <h6 class="card-title mb-0 fw-bold">O que foi alterado?</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Objeto:</span>
                            <span class="font-monospace text-primary">{{ class_basename($log->auditable_type) }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">ID do Objeto:</span>
                            <span class="font-monospace">{{ $log->auditable_id }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Diff -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h6 class="card-title mb-0 fw-bold">Alterações (Diff)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th class="w-25">Campo</th>
                                    <th class="w-35 text-danger bg-danger bg-opacity-10">Valor Antigo</th>
                                    <th class="w-35 text-success bg-success bg-opacity-10">Valor Novo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $old = $log->old_values ?? [];
                                    $new = $log->new_values ?? [];
                                    $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
                                @endphp

                                @forelse($keys as $key)
                                    <tr>
                                        <td class="fw-bold bg-light font-monospace small">{{ $key }}</td>
                                        <td class="bg-danger bg-opacity-10 text-break">
                                            @if(isset($old[$key]))
                                                {{ is_array($old[$key]) ? json_encode($old[$key]) : $old[$key] }}
                                            @else
                                                <span class="text-muted small fst-italic">null</span>
                                            @endif
                                        </td>
                                        <td class="bg-success bg-opacity-10 text-break">
                                            @if(isset($new[$key]))
                                                {{ is_array($new[$key]) ? json_encode($new[$key]) : $new[$key] }}
                                            @else
                                                <span class="text-muted small fst-italic">null</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            Nenhuma alteração registrada (pode ser um evento sem modificação de dados).
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
