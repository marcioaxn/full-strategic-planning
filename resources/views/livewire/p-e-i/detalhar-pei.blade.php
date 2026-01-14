<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('pei.ciclos') }}" wire:navigate class="text-decoration-none">Ciclos Estratégicos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $pei->dsc_pei }}</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-gray-800 mb-0">
                <i class="bi bi-bullseye me-2 text-primary"></i>Detalhes do Ciclo Estratégico
            </h2>
            <p class="text-muted mb-0">Visão 360° do planejamento {{ $pei->num_ano_inicio_pei }} - {{ $pei->num_ano_fim_pei }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pei.ciclos') }}" wire:navigate class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            <button class="btn btn-primary gradient-theme">
                <i class="bi bi-pencil me-1"></i> Editar PEI
            </button>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row g-3 mb-4">
        <!-- Card Vigência -->
        <div class="col-md-6 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle-mini bg-primary bg-opacity-10 me-3">
                            <i class="bi bi-calendar-range text-primary fs-5"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1 small">Vigência</h6>
                            <h5 class="card-title mb-0 fw-bold">{{ $pei->num_ano_inicio_pei }}-{{ $pei->num_ano_fim_pei }}</h5>
                        </div>
                    </div>
                    <div class="progress" style="height: 4px;">
                        @php
                            $totalAnos = $pei->num_ano_fim_pei - $pei->num_ano_inicio_pei + 1;
                            $anosPassados = now()->year - $pei->num_ano_inicio_pei + 1;
                            $porcentagem = min(100, max(0, ($anosPassados / $totalAnos) * 100));
                        @endphp
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $porcentagem }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted" style="font-size: 0.65rem;">{{ number_format($porcentagem, 0) }}% decorrido</small>
                        <small class="text-muted" style="font-size: 0.65rem;">{{ $totalAnos }} anos</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Perspectivas -->
        <div class="col-md-6 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle-mini bg-success bg-opacity-10 me-3">
                            <i class="bi bi-layers text-success fs-5"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1 small">Perspectivas</h6>
                            <h4 class="card-title mb-0 fw-bold">{{ $estatisticas['qtd_perspectivas'] ?? 0 }}</h4>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0" style="font-size: 0.7rem;">Dimensões BSC</p>
                </div>
            </div>
        </div>

        <!-- Card Objetivos BSC -->
        <div class="col-md-6 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle-mini bg-warning bg-opacity-10 me-3">
                            <i class="bi bi-crosshair text-warning fs-5"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1 small">Objetivos BSC</h6>
                            <h4 class="card-title mb-0 fw-bold">{{ $estatisticas['qtd_objetivos_bsc'] ?? 0 }}</h4>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0" style="font-size: 0.7rem;">Vinculados ao mapa</p>
                </div>
            </div>
        </div>

        <!-- Card Objetivos Estratégicos -->
        <div class="col-md-6 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle-mini bg-danger bg-opacity-10 me-3">
                            <i class="bi bi-shield-check text-danger fs-5"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1 small">Objetivos PEI</h6>
                            <h4 class="card-title mb-0 fw-bold">{{ $estatisticas['qtd_objetivos_estrategicos'] ?? 0 }}</h4>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0" style="font-size: 0.7rem;">Metas institucionais</p>
                </div>
            </div>
        </div>

        <!-- Card Valores -->
        <div class="col-md-6 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle-mini bg-info bg-opacity-10 me-3">
                            <i class="bi bi-gem text-info fs-5"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1 small">Valores</h6>
                            <h4 class="card-title mb-0 fw-bold">{{ $estatisticas['qtd_valores'] ?? 0 }}</h4>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0" style="font-size: 0.7rem;">Pilares da cultura</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Coluna Esquerda: Identidade e Informações -->
        <div class="col-lg-8">
            <!-- Identidade Estratégica -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-compass me-2 text-primary"></i>Identidade Estratégica
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($pei->identidadeEstrategica as $identidade)
                        <div class="mb-4">
                            <h6 class="fw-bold text-uppercase text-secondary small mb-2">Missão</h6>
                            <div class="p-3 bg-light rounded border-start border-4 border-primary">
                                <p class="mb-0 fst-italic">"{{ $identidade->dsc_missao }}"</p>
                            </div>
                        </div>
                        <div>
                            <h6 class="fw-bold text-uppercase text-secondary small mb-2">Visão</h6>
                            <div class="p-3 bg-light rounded border-start border-4 border-success">
                                <p class="mb-0 fst-italic">"{{ $identidade->dsc_visao }}"</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-exclamation-circle text-muted fs-1 d-block mb-2"></i>
                            <p class="text-muted">Nenhuma identidade estratégica definida.</p>
                            <a href="{{ route('pei.index') }}" class="btn btn-sm btn-outline-primary">Definir Missão e Visão</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Valores -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-gem me-2 text-info"></i>Valores Organizacionais
                    </h5>
                </div>
                <div class="card-body">
                    @if($pei->valores->count() > 0)
                        <div class="row g-3">
                            @foreach($pei->valores as $valor)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start p-3 border rounded hover-shadow transition-all h-100">
                                        <div class="text-warning me-3 pt-1">
                                            <i class="bi bi-star-fill"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">{{ $valor->nom_valor }}</h6>
                                            @if($valor->dsc_valor)
                                                <p class="small text-muted mb-0">{{ Str::limit($valor->dsc_valor, 120) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Nenhum valor cadastrado.</p>
                            <a href="{{ route('pei.valores') }}" class="btn btn-link btn-sm">Gerenciar Valores</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Ações e Atalhos -->
        <div class="col-lg-4">
            <!-- Próximos Passos (Mentor Estratégico Simplificado) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white py-3" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
                    <h5 class="card-title mb-0 fw-bold text-white">
                        <i class="bi bi-lightbulb me-2"></i>Ações Rápidas
                    </h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('pei.perspectivas') }}" wire:navigate class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                        <div>
                            <i class="bi bi-layers me-2 text-primary"></i> Gerenciar Perspectivas
                        </div>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                    <a href="{{ route('objetivos.index') }}" wire:navigate class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                        <div>
                            <i class="bi bi-crosshair me-2 text-danger"></i> Gerenciar Objetivos
                        </div>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                    <a href="{{ route('indicadores.index') }}" wire:navigate class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                        <div>
                            <i class="bi bi-graph-up me-2 text-success"></i> Gerenciar Indicadores
                        </div>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                    <a href="{{ route('pei.swot') }}" wire:navigate class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                        <div>
                            <i class="bi bi-grid-1x2 me-2 text-warning"></i> Análise SWOT
                        </div>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                </div>
            </div>

            <!-- Informações do Sistema -->
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Status do Ciclo</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Criado em:</span>
                            <span class="fw-medium">{{ $pei->created_at->format('d/m/Y') }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Última atualização:</span>
                            <span class="fw-medium">{{ $pei->updated_at->format('d/m/Y') }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Status:</span>
                            @if($pei->isAtivo())
                                <span class="badge bg-success">Em Andamento</span>
                            @else
                                <span class="badge bg-secondary">Inativo</span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
