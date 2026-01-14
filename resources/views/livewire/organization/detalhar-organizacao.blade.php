<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('organizacoes.index') }}" wire:navigate class="text-decoration-none">Organizações</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $organizacao->sgl_organizacao }}</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-gray-800 mb-0">
                <i class="bi bi-building me-2 text-primary"></i>Detalhes da Organização
            </h2>
            <p class="text-muted mb-0">
                {{ $organizacao->nom_organizacao }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('organizacoes.index') }}" wire:navigate class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            <button class="btn btn-primary gradient-theme">
                <i class="bi bi-pencil me-1"></i> Editar
            </button>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-primary bg-opacity-10 me-3">
                            <i class="bi bi-people text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Usuários</h6>
                            <h4 class="card-title mb-0">{{ $estatisticas['qtd_usuarios'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-info bg-opacity-10 me-3">
                            <i class="bi bi-diagram-3 text-info fs-4"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Subunidades</h6>
                            <h4 class="card-title mb-0">{{ $estatisticas['qtd_filhas'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-warning bg-opacity-10 me-3">
                            <i class="bi bi-kanban text-warning fs-4"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Planos de Ação</h6>
                            <h4 class="card-title mb-0">{{ $estatisticas['qtd_planos'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-success bg-opacity-10 me-3">
                            <i class="bi bi-graph-up text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Indicadores</h6>
                            <h4 class="card-title mb-0">{{ $estatisticas['qtd_indicadores'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Coluna Esquerda: Hierarquia e Identidade -->
        <div class="col-lg-4">
            <!-- Hierarquia -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title mb-0 fw-bold">Estrutura Hierárquica</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @if($organizacao->pai)
                            <li class="list-group-item px-0 border-0">
                                <small class="text-muted d-block mb-1">Unidade Superior</small>
                                <a href="{{ route('organizacoes.detalhes', $organizacao->pai->cod_organizacao) }}" wire:navigate class="d-flex align-items-center text-decoration-none">
                                    <i class="bi bi-arrow-90deg-up me-2 text-secondary"></i>
                                    <span class="fw-bold text-dark">{{ $organizacao->pai->sgl_organizacao }}</span>
                                    <span class="text-muted ms-2 small text-truncate">{{ $organizacao->pai->nom_organizacao }}</span>
                                </a>
                            </li>
                        @endif

                        <li class="list-group-item px-0 border-0 bg-light rounded p-2 my-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-house-door-fill me-2 text-primary"></i>
                                <span class="fw-bold text-primary">{{ $organizacao->sgl_organizacao }}</span>
                            </div>
                        </li>

                        @if($organizacao->filhas->isNotEmpty())
                            <li class="list-group-item px-0 border-0">
                                <small class="text-muted d-block mb-2">Subunidades ({{ $organizacao->filhas->count() }})</small>
                                <div class="ps-3 border-start">
                                    @foreach($organizacao->filhas as $filha)
                                        <a href="{{ route('organizacoes.detalhes', $filha->cod_organizacao) }}" wire:navigate class="d-flex align-items-center text-decoration-none mb-2 last:mb-0">
                                            <i class="bi bi-arrow-return-right me-2 text-secondary small"></i>
                                            <span class="fw-medium text-dark small">{{ $filha->sgl_organizacao }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Última Identidade Definida -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title mb-0 fw-bold">Identidade Estratégica</h6>
                </div>
                <div class="card-body">
                    @if($identidade = $organizacao->identidadeEstrategica->first())
                        <div class="mb-3">
                            <small class="text-uppercase text-muted fw-bold x-small">Missão</small>
                            <p class="small mb-0 text-secondary fst-italic">"{{ Str::limit($identidade->dsc_missao, 150) }}"</p>
                        </div>
                        <div>
                            <small class="text-uppercase text-muted fw-bold x-small">Visão</small>
                            <p class="small mb-0 text-secondary fst-italic">"{{ Str::limit($identidade->dsc_visao, 150) }}"</p>
                        </div>
                        <div class="mt-3 pt-2 border-top">
                            <small class="text-muted">Ciclo: {{ $identidade->pei->dsc_pei ?? 'N/A' }}</small>
                        </div>
                    @else
                        <div class="text-center py-3 text-muted">
                            <small>Nenhuma identidade definida recentemente.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Tabs -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <ul class="nav nav-tabs card-header-tabs" id="orgTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">Usuários</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="planos-tab" data-bs-toggle="tab" data-bs-target="#planos" type="button" role="tab">Planos de Ação</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="orgTabsContent">
                        <!-- Usuários Tab -->
                        <div class="tab-pane fade show active" id="users" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($organizacao->usuarios as $user)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle bg-primary text-white me-2" style="width: 32px; height: 32px; display:flex; align-items:center; justify-content:center; border-radius:50%; font-size:12px;">
                                                            {{ substr($user->name, 0, 2) }}
                                                        </div>
                                                        <span class="fw-medium">{{ $user->name }}</span>
                                                    </div>
                                                </td>
                                                <td class="small text-muted">{{ $user->email }}</td>
                                                <td>
                                                    <span class="badge bg-success-subtle text-success">Ativo</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">
                                                    Nenhum usuário vinculado.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Planos Tab -->
                        <div class="tab-pane fade" id="planos" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Plano</th>
                                            <th>Responsável</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($organizacao->planosAcao as $plano)
                                            <tr>
                                                <td>{{ $plano->dsc_plano_acao }}</td>
                                                <td>{{ $plano->responsavel->name ?? 'Não definido' }}</td>
                                                <td><span class="badge bg-secondary">{{ $plano->bln_status }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">
                                                    Nenhum plano de ação vinculado.
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
    </div>
</div>
