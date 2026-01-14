<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('pei.perspectivas') }}" wire:navigate class="text-decoration-none">Perspectivas BSC</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $perspectiva->dsc_perspectiva }}</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-gray-800 mb-0">
                <i class="bi bi-layers me-2 text-primary"></i>Detalhes da Perspectiva
            </h2>
            <p class="text-muted mb-0">
                {{ $perspectiva->pei->dsc_pei }} • Nível Hierárquico: {{ $perspectiva->num_nivel_hierarquico_apresentacao }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pei.perspectivas') }}" wire:navigate class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            <button class="btn btn-primary gradient-theme">
                <i class="bi bi-pencil me-1"></i> Editar
            </button>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-primary bg-opacity-10 me-3">
                            <i class="bi bi-crosshair text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Objetivos Estratégicos</h6>
                            <h4 class="card-title mb-0">{{ $estatisticas['qtd_objetivos'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-success bg-opacity-10 me-3">
                            <i class="bi bi-graph-up text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Indicadores Vinculados</h6>
                            <h4 class="card-title mb-0">{{ $estatisticas['qtd_indicadores'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-info bg-opacity-10 me-3">
                            <i class="bi bi-pie-chart text-info fs-4"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Desempenho Geral</h6>
                            <h4 class="card-title mb-0">--%</h4>
                        </div>
                    </div>
                    <small class="text-muted">Cálculo de desempenho em breve</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Objetivos -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 fw-bold">Objetivos desta Perspectiva</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Objetivo</th>
                        <th>Indicadores</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($perspectiva->objetivos as $objetivo)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-dark">{{ $objetivo->nom_objetivo }}</span>
                                @if($objetivo->dsc_objetivo)
                                    <p class="small text-muted mb-0">{{ Str::limit($objetivo->dsc_objetivo, 80) }}</p>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary rounded-pill">
                                    {{ $objetivo->indicadores->count() }} KPIs
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">Não iniciado</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="#" class="btn btn-sm btn-icon btn-outline-primary" title="Detalhar Objetivo">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-crosshair fs-1 d-block mb-2 opacity-50"></i>
                                Nenhum objetivo vinculado a esta perspectiva.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
