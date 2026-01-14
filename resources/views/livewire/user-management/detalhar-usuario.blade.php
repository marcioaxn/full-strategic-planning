<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}" wire:navigate class="text-decoration-none">Gestão de Usuários</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-gray-800 mb-0">
                <i class="bi bi-person-circle me-2 text-primary"></i>Perfil do Usuário
            </h2>
            <p class="text-muted mb-0">
                {{ $user->email }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('usuarios.index') }}" wire:navigate class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            <button class="btn btn-primary gradient-theme">
                <i class="bi bi-pencil me-1"></i> Editar
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Coluna Esquerda: Dados Pessoais -->
        <div class="col-lg-4">
            <!-- Card Perfil -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center pt-5">
                    <div class="avatar-circle bg-primary text-white mx-auto mb-3" style="width: 100px; height: 100px; display:flex; align-items:center; justify-content:center; border-radius:50%; font-size:40px;">
                        {{ substr($user->name, 0, 2) }}
                    </div>
                    <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-3">{{ $user->email }}</p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        @if($user->isSuperAdmin())
                            <span class="badge bg-danger">Super Admin</span>
                        @endif
                        @if($user->ativo)
                            <span class="badge bg-success">Ativo</span>
                        @else
                            <span class="badge bg-secondary">Inativo</span>
                        @endif
                    </div>

                    <div class="border-top pt-3 text-start">
                        <small class="text-muted d-block mb-1">Data de Cadastro</small>
                        <p class="fw-medium mb-3">{{ $user->created_at->format('d/m/Y') }}</p>
                        
                        <small class="text-muted d-block mb-1">Último Acesso</small>
                        <p class="fw-medium mb-0">--</p> <!-- Implementar log de login -->
                    </div>
                </div>
            </div>
            
            <!-- Estatísticas Rápidas -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title mb-0 fw-bold">Visão Geral</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Organizações</span>
                        <span class="badge bg-primary rounded-pill">{{ $estatisticas['qtd_organizacoes'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Planos Responsável</span>
                        <span class="badge bg-warning rounded-pill">{{ $estatisticas['qtd_planos'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Abas de Detalhes -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <ul class="nav nav-tabs card-header-tabs" id="userTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="orgs-tab" data-bs-toggle="tab" data-bs-target="#orgs" type="button" role="tab">Organizações</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="planos-tab" data-bs-toggle="tab" data-bs-target="#planos" type="button" role="tab">Planos de Ação</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit" type="button" role="tab">Histórico</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="userTabsContent">
                        <!-- Organizações -->
                        <div class="tab-pane fade show active" id="orgs" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Sigla</th>
                                            <th>Nome</th>
                                            <th>Perfis</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($user->organizacoes as $org)
                                            <tr>
                                                <td class="fw-bold">{{ $org->sgl_organizacao }}</td>
                                                <td>{{ $org->nom_organizacao }}</td>
                                                <td>
                                                    <!-- Listar perfis nesta organização -->
                                                    <!-- Como é pivot complexo, deixar simplificado por enquanto -->
                                                    <span class="badge bg-secondary">Membro</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">
                                                    Nenhuma organização vinculada.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Planos de Ação -->
                        <div class="tab-pane fade" id="planos" role="tabpanel">
                            <div class="alert alert-info py-2 small mb-3">
                                <i class="bi bi-info-circle me-1"></i> Planos onde o usuário atua como Gestor Responsável ou Substituto.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Plano</th>
                                            <th>Status</th>
                                            <th>Prazo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($planosResponsavel as $plano)
                                            <tr>
                                                <td>{{ $plano->dsc_plano_de_acao }}</td>
                                                <td><span class="badge bg-secondary">{{ $plano->bln_status }}</span></td>
                                                <td>{{ $plano->dte_fim ? \Carbon\Carbon::parse($plano->dte_fim)->format('d/m/Y') : '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">
                                                    Nenhum plano sob responsabilidade.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Histórico (Audit) -->
                        <div class="tab-pane fade" id="audit" role="tabpanel">
                            <div class="text-center py-5">
                                <i class="bi bi-clock-history fs-1 text-muted opacity-50 mb-3"></i>
                                <h6 class="text-muted">Logs de Atividade</h6>
                                <p class="small text-muted mb-0">O histórico detalhado de ações deste usuário estará disponível em breve.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
