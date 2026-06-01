<div>
    {{-- Header --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" wire:navigate class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Perfis de Acesso</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2 mt-1">
                <div class="icon-circle-header gradient-theme-icon"><i class="bi bi-shield-lock-fill"></i></div>
                <h1 class="h3 fw-bold mb-0">Gestão de Perfis de Acesso</h1>
            </div>
        </div>
        <a href="{{ route('usuarios.index') }}" wire:navigate class="btn btn-outline-primary rounded-pill px-4">
            <i class="bi bi-people me-2"></i>Gerenciar Usuários
        </a>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Cards dos Perfis --}}
    <div class="row g-3 mb-4">
        @foreach($perfis as $perfil)
        @php
            $meta = $this->perfisDescricao[$perfil->dsc_perfil] ?? ['icon' => 'person-badge', 'color' => 'secondary', 'desc' => $perfil->dsc_permissao ?? 'Perfil personalizado.', 'flag' => '—'];
        @endphp
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="icon-circle-mini bg-{{ $meta['color'] }} bg-opacity-10 text-{{ $meta['color'] }}">
                            <i class="bi bi-{{ $meta['icon'] }}"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">{{ $perfil->dsc_perfil }}</h6>
                            <code class="x-small text-muted" style="font-size:.65rem;">{{ $meta['flag'] }}</code>
                        </div>
                    </div>
                    <p class="text-muted small mb-2" style="line-height:1.5;">{{ $meta['desc'] }}</p>
                    <span class="badge bg-{{ $meta['color'] }}-subtle text-{{ $meta['color'] }}">
                        <i class="bi bi-people me-1"></i>{{ $perfil->usuarios_count }} {{ $perfil->usuarios_count == 1 ? 'usuário' : 'usuários' }}
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Matriz de Permissões --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h6 class="fw-bold mb-0"><i class="bi bi-grid-3x3 me-2 text-primary"></i>Matriz de Permissões por Funcionalidade</h6>
            <small class="text-muted">Visão consolidada do que cada perfil pode fazer em cada módulo do sistema.</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 small text-uppercase text-muted">Funcionalidade</th>
                            @foreach(array_keys($this->matriz['perfis']) as $nomePerfil)
                                <th class="text-center small text-uppercase text-muted" style="font-size:.7rem;">{{ $nomePerfil }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->matriz['funcionalidades'] as $i => $func)
                        <tr>
                            <td class="ps-4 fw-semibold small">{{ $func }}</td>
                            @foreach($this->matriz['perfis'] as $permissoes)
                            @php
                                $nivel = $permissoes[$i];
                                $cfg = match($nivel) {
                                    'T' => ['label' => 'Total', 'class' => 'bg-success text-white', 'icon' => 'check-all'],
                                    'E' => ['label' => 'Edição', 'class' => 'bg-primary text-white', 'icon' => 'pencil'],
                                    'L' => ['label' => 'Leitura', 'class' => 'bg-info-subtle text-info', 'icon' => 'eye'],
                                    default => ['label' => 'Sem acesso', 'class' => 'bg-light text-muted', 'icon' => 'dash'],
                                };
                            @endphp
                            <td class="text-center">
                                <span class="badge {{ $cfg['class'] }}" data-bs-toggle="tooltip" title="{{ $cfg['label'] }}" style="min-width:62px;">
                                    <i class="bi bi-{{ $cfg['icon'] }} me-1"></i>{{ $cfg['label'] }}
                                </span>
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-2 px-4">
                <div class="d-flex flex-wrap gap-3 small text-muted">
                    <span><span class="badge bg-success text-white">Total</span> CRUD completo</span>
                    <span><span class="badge bg-primary text-white">Edição</span> Cria e edita</span>
                    <span><span class="badge bg-info-subtle text-info">Leitura</span> Apenas visualiza</span>
                    <span><span class="badge bg-light text-muted">Sem acesso</span> Bloqueado</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Impersonação --}}
    <div class="card border-0 shadow-sm border-start border-4 border-warning">
        <div class="card-header bg-warning bg-opacity-10 border-bottom py-3 px-4">
            <h6 class="fw-bold mb-0 text-warning-emphasis"><i class="bi bi-person-bounding-box me-2"></i>Assumir Identidade (Impersonação)</h6>
            <small class="text-muted">Visualize o sistema como outro usuário para diagnóstico. Toda ação fica registrada em log e exibe um banner de aviso.</small>
        </div>
        <div class="card-body p-4">
            <div class="alert alert-warning border-0 small d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-info-circle-fill"></i>
                Ao assumir uma identidade, você navegará com as permissões do usuário selecionado. Use o botão "Encerrar impersonação" no topo para retornar.
            </div>

            <div class="input-group mb-3" style="max-width:420px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="buscaUsuario" class="form-control border-start-0 ps-0" placeholder="Buscar usuário por nome ou e-mail...">
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Usuário</th>
                            <th>E-mail</th>
                            <th class="text-center">Situação</th>
                            <th class="text-end pe-4">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $u)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $u->profile_photo_url }}" alt="" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                                    <span class="fw-semibold">{{ $u->name }}</span>
                                    @if($u->adm)<span class="badge bg-danger-subtle text-danger ms-1">Admin</span>@endif
                                </div>
                            </td>
                            <td class="text-muted small">{{ $u->email }}</td>
                            <td class="text-center">
                                @if($u->ativo)
                                    <span class="badge bg-success-subtle text-success">Ativo</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('impersonate.start', $u->id) }}"
                                   class="btn btn-sm btn-outline-warning rounded-pill px-3"
                                   onclick="return confirm('Assumir a identidade de {{ $u->name }}?');">
                                    <i class="bi bi-person-bounding-box me-1"></i>Assumir
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">Nenhum usuário encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $usuarios->links() }}</div>
        </div>
    </div>
</div>
