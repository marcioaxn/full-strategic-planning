<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('planos.index') }}" class="text-decoration-none">Planos de Ação</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Ficha Técnica do Plano</h2>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('planos.entregas', $plano->cod_plano_de_acao) }}" class="btn btn-outline-info rounded-pill px-3">
                    <i class="bi bi-list-check me-1"></i> Entregas
                </a>
                <a href="{{ route('planos.responsaveis', $plano->cod_plano_de_acao) }}" class="btn btn-outline-warning rounded-pill px-3">
                    <i class="bi bi-people me-1"></i> Gestores
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row g-4">
        <!-- Coluna Esquerda: Geral e Entregas -->
        <div class="col-lg-8">
            <!-- Informações Gerais -->
            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <div class="card-header gradient-theme text-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Informações Gerais</h5>
                        <span class="badge bg-white text-primary rounded-pill px-3">{{ $plano->bln_status }}</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <h3 class="fw-bold mb-3">{{ $plano->dsc_plano_de_acao }}</h3>
                    
                    @if($plano->txt_detalhamento)
                        <div class="mb-4 p-3 bg-light rounded border-start border-4 border-info shadow-sm">
                            <label class="text-muted small text-uppercase fw-bold d-block mb-2">
                                <i class="bi bi-justify-left me-1"></i>Detalhamento / Justificativa
                            </label>
                            <div class="text-dark lh-base" style="white-space: pre-line;">{{ $plano->txt_detalhamento }}</div>
                        </div>
                    @endif

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold d-block">Objetivo</label>
                            <p class="mb-0 fw-semibold">
                                <i class="bi bi-bullseye text-primary me-2"></i>
                                {{ $plano->objetivo->nom_objetivo ?? 'N/A' }}
                            </p>
                            <small class="text-muted ps-4">{{ $plano->objetivo->perspectiva->dsc_perspectiva ?? '' }}</small>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small text-uppercase fw-bold d-block">Tipo</label>
                            <p class="mb-0">{{ $plano->tipoExecucao->dsc_tipo_execucao ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small text-uppercase fw-bold d-block">Organização</label>
                            <p class="mb-0">{{ $plano->organizacao->sgl_organizacao ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row g-4 pt-3 border-top">
                        <div class="col-md-3">
                            <label class="text-muted small text-uppercase fw-bold d-block">Início</label>
                            <p class="mb-0 fw-bold"><i class="bi bi-calendar-check me-2"></i>{{ $plano->dte_inicio?->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small text-uppercase fw-bold d-block">Término</label>
                            <p class="mb-0 fw-bold {{ $plano->isAtrasado() ? 'text-danger' : '' }}">
                                <i class="bi bi-calendar-x me-2"></i>{{ $plano->dte_fim?->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small text-uppercase fw-bold d-block">Orçamento</label>
                            <p class="mb-0 fw-mono">R$ @brazil_number($plano->vlr_orcamento_previsto, 2)</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small text-uppercase fw-bold d-block">Vínculos Orç.</label>
                            <small class="d-block">PPA: {{ $plano->cod_ppa ?: '-' }}</small>
                            <small class="d-block">LOA: {{ $plano->cod_loa ?: '-' }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progresso e Entregas -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-check2-all me-2 text-primary"></i>Status de Execução</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Progresso via Entregas</span>
                            <span class="fw-bold text-primary">@brazil_percent($progresso, 1)</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 10px;">
                            <div class="progress-bar gradient-theme" style="width: {{ $progresso }}%"></div>
                        </div>
                    </div>

                    <div class="list-group list-group-flush border-top">
                        @forelse($plano->entregas->sortBy('num_nivel_hierarquico_apresentacao') as $entrega)
                            <div class="list-group-item px-0 py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex">
                                        @if($entrega->bln_status === 'Concluído')
                                            <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                                        @else
                                            <i class="bi bi-circle text-muted me-3 fs-5"></i>
                                        @endif
                                        <div>
                                            <span class="fw-semibold {{ $entrega->bln_status === 'Concluído' ? 'text-decoration-line-through text-muted' : '' }}">
                                                {{ $entrega->dsc_entrega }}
                                            </span>
                                            <small class="d-block text-muted mt-1">{{ $entrega->dsc_periodo_medicao }}</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-dark border rounded-pill">{{ $entrega->bln_status }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-center py-4 text-muted">Nenhuma entrega cadastrada.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Responsáveis e Auditoria -->
        <div class="col-lg-4">
            <!-- Responsáveis -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-primary"></i>Equipe Responsável</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <ul class="list-unstyled mb-0">
                        @forelse($responsaveis as $resp)
                            <li class="d-flex align-items-center mb-3">
                                <div class="avatar-sm-det me-3 gradient-theme text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    {{ substr($resp->name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="fw-semibold d-block small">{{ $resp->name }}</span>
                                    <span class="badge bg-light text-muted border py-1 px-2" style="font-size: 0.65rem;">{{ $resp->dsc_perfil }}</span>
                                </div>
                            </li>
                        @empty
                            <p class="text-muted small text-center py-2">Sem responsáveis atribuídos.</p>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Histórico de Alterações -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Histórico</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="timeline-det">
                        @forelse($auditoria as $audit)
                            <div class="timeline-item-det pb-3 mb-3 border-bottom">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="fw-bold">{{ $audit->user->name ?? 'Sistema' }}</small>
                                    <small class="text-muted">{{ $audit->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    @php
                                        $eventClass = match($audit->event) {
                                            'created' => 'success',
                                            'updated' => 'primary',
                                            'deleted' => 'danger',
                                            default => 'secondary'
                                        };
                                        $eventLabel = match($audit->event) {
                                            'created' => 'Criação',
                                            'updated' => 'Atualização',
                                            'deleted' => 'Exclusão',
                                            default => $audit->event
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $eventClass }} bg-opacity-10 text-{{ $eventClass }} small py-0 px-2 me-2">{{ $eventLabel }}</span>
                                    <small class="text-muted">ID: ...{{ substr($audit->id, -6) }}</small>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small text-center">Sem histórico registrado.</p>
                        @endforelse
                    </div>
                    @if($auditoria->isNotEmpty())
                        <div class="text-center mt-3">
                            <button class="btn btn-link btn-sm text-decoration-none">Ver histórico completo</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .italic { font-style: italic; }
        .fw-mono { font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
        .timeline-item-det:last-child { border-bottom: 0 !important; margin-bottom: 0 !important; }
    </style>
</div>
