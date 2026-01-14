<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('pei.index') }}" wire:navigate class="text-decoration-none">Identidade Estratégica</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-gray-800 mb-0">
                <i class="bi bi-compass me-2 text-primary"></i>Identidade Estratégica Detalhada
            </h2>
            <p class="text-muted mb-0">
                {{ $identidade->organizacao->nom_organizacao }} • {{ $identidade->pei->dsc_pei }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pei.index') }}" wire:navigate class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            <a href="{{ route('pei.index') }}" wire:navigate class="btn btn-primary gradient-theme">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Coluna Esquerda: Missão e Visão -->
        <div class="col-lg-8">
            <!-- Missão -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                            <i class="bi bi-bullseye text-primary fs-5"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold">Missão</h5>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="p-4 bg-light rounded border-start border-4 border-primary">
                        <p class="mb-0 fs-5 fst-italic text-secondary">"{{ $identidade->dsc_missao }}"</p>
                    </div>
                    <div class="mt-3 text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        A missão define a razão de existir da organização e seu propósito fundamental.
                    </div>
                </div>
            </div>

            <!-- Visão -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-success bg-opacity-10 p-2 rounded-circle">
                            <i class="bi bi-eye text-success fs-5"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold">Visão de Futuro</h5>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="p-4 bg-light rounded border-start border-4 border-success">
                        <p class="mb-0 fs-5 fst-italic text-secondary">"{{ $identidade->dsc_visao }}"</p>
                    </div>
                    <div class="mt-3 text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        A visão estabelece onde a organização quer chegar no longo prazo.
                    </div>
                </div>
            </div>

            <!-- Valores -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-warning bg-opacity-10 p-2 rounded-circle">
                            <i class="bi bi-star text-warning fs-5"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold">Valores Organizacionais</h5>
                    </div>
                </div>
                <div class="card-body">
                    @if($valores->count() > 0)
                        <div class="row g-3">
                            @foreach($valores as $valor)
                                <div class="col-md-6">
                                    <div class="h-100 p-3 border rounded bg-white hover-shadow transition-all">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-check-circle-fill text-warning me-2"></i>
                                            <h6 class="fw-bold mb-0">{{ $valor->nom_valor }}</h6>
                                        </div>
                                        <p class="text-muted small mb-0 ps-4">{{ $valor->dsc_valor }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-exclamation-circle fs-4 mb-2 d-block"></i>
                            Nenhum valor cadastrado para esta identidade.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Metadados e Histórico -->
        <div class="col-lg-4">
            <!-- Informações do Registro -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="card-title mb-0 fw-bold">Informações do Registro</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 font-monospace small">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">ID:</span>
                            <span title="{{ $identidade->cod_missao_visao_valores }}">{{ Str::limit($identidade->cod_missao_visao_valores, 8) }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Criado em:</span>
                            <span>{{ $identidade->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Atualizado em:</span>
                            <span>{{ $identidade->updated_at->format('d/m/Y H:i') }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Organização:</span>
                            <span>{{ Str::limit($identidade->organizacao->nom_organizacao, 20) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Histórico de Alterações -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light py-3">
                    <h6 class="card-title mb-0 fw-bold">Histórico de Alterações</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        @forelse($identidade->audits()->latest()->take(5)->get() as $audit)
                            <li class="list-group-item px-3 py-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold text-dark">{{ $audit->user->name ?? 'Sistema' }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ $audit->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="text-muted mb-1">
                                    <span class="badge bg-{{ $audit->event == 'created' ? 'success' : 'primary' }} bg-opacity-10 text-{{ $audit->event == 'created' ? 'success' : 'primary' }} border border-opacity-10">
                                        {{ ucfirst($audit->event) }}
                                    </span>
                                </div>
                                @if($audit->event == 'updated')
                                    <div class="mt-2 bg-light p-2 rounded border" style="font-size: 0.75rem;">
                                        @foreach($audit->old_values as $key => $val)
                                            @if(in_array($key, ['dsc_missao', 'dsc_visao']))
                                                <div class="mb-1">
                                                    <strong>{{ $key == 'dsc_missao' ? 'Missão' : 'Visão' }}:</strong> alterado
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4 text-muted">
                                Nenhuma alteração registrada.
                            </li>
                        @endforelse
                    </ul>
                    @if($identidade->audits()->count() > 5)
                        <div class="card-footer bg-white text-center p-2">
                            <a href="#" class="small text-decoration-none">Ver histórico completo</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
