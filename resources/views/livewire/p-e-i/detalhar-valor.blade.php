<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('pei.valores') }}" wire:navigate class="text-decoration-none">Valores Organizacionais</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $valor->nom_valor }}</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-gray-800 mb-0">
                <i class="bi bi-gem me-2 text-warning"></i>Detalhes do Valor
            </h2>
            <p class="text-muted mb-0">
                {{ $valor->organizacao->nom_organizacao }} • {{ $valor->pei->dsc_pei }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pei.valores') }}" wire:navigate class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            <!-- Botão de editar pode ser implementado depois ou redirecionar para modal de edição -->
        </div>
    </div>

    <div class="row g-4">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-circle bg-warning bg-opacity-10 me-3">
                            <i class="bi bi-star-fill text-warning fs-3"></i>
                        </div>
                        <h3 class="fw-bold mb-0 text-body-emphasis">{{ $valor->nom_valor }}</h3>
                    </div>

                    <h6 class="text-uppercase text-muted small fw-bold mb-2">Descrição</h6>
                    <div class="p-4 bg-light rounded border-start border-4 border-warning">
                        @if($valor->dsc_valor)
                            <p class="mb-0 fs-5 text-secondary" style="line-height: 1.6;">{{ $valor->dsc_valor }}</p>
                        @else
                            <p class="mb-0 text-muted fst-italic">Nenhuma descrição cadastrada.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Como este valor é vivido (Placeholder) -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-heart-pulse me-2 text-danger"></i>Como vivemos este valor?
                    </h5>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-3 opacity-50">
                        <i class="bi bi-people fs-1 text-muted"></i>
                    </div>
                    <h6 class="text-muted mb-2">Evidências de Cultura</h6>
                    <p class="small text-muted mb-0">
                        Em breve, você poderá visualizar exemplos práticos, histórias e métricas qualitativas de como este valor é aplicado no dia a dia.
                    </p>
                </div>
            </div>
        </div>

        <!-- Coluna Lateral -->
        <div class="col-lg-4">
            <!-- Estatísticas de Uso -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="card-title mb-0 fw-bold">Impacto Estratégico</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-3">
                        <div>
                            <small class="text-muted d-block">Objetivos Relacionados</small>
                            <span class="fw-bold fs-5">0</span>
                        </div>
                        <i class="bi bi-crosshair text-muted fs-4"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Planos de Ação</small>
                            <span class="fw-bold fs-5">0</span>
                        </div>
                        <i class="bi bi-kanban text-muted fs-4"></i>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 text-center">
                    <small class="text-muted">Dados de vínculo em breve</small>
                </div>
            </div>

            <!-- Metadados -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <ul class="list-unstyled mb-0 font-monospace small">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Criado em:</span>
                            <span>{{ $valor->created_at->format('d/m/Y') }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Última edição:</span>
                            <span>{{ $valor->updated_at->format('d/m/Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
