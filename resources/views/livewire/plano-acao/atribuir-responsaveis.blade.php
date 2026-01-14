<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" wire:navigate class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('planos.index') }}" wire:navigate class="text-decoration-none">Planos de Ação</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('planos.detalhes', $plano->cod_plano_de_acao) }}" wire:navigate class="text-decoration-none">{{ Str::limit($plano->dsc_plano_de_acao, 30) }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Responsáveis</li>
                    </ol>
                </nav>
                <h2 class="h3 fw-bold mb-0 text-gray-800">
                    <i class="bi bi-people-fill me-2 text-primary"></i>Gestores e Responsáveis
                </h2>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-2">PLANO</span>
                    <span class="text-secondary fw-medium">{{ $plano->dsc_plano_de_acao }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('planos.detalhes', $plano->cod_plano_de_acao) }}" wire:navigate class="btn btn-outline-secondary shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar para o Plano
                </a>
            </div>
        </div>
    </x-slot>

    @if (session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Coluna da Esquerda: Adicionar Novo -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus me-2 text-primary"></i>Nova Atribuição</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <form wire:submit.prevent="adicionar">
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Selecionar Usuário</label>
                            <select wire:model="novo_usuario_id" class="form-select @error('novo_usuario_id') is-invalid @enderror">
                                <option value="">Escolha um usuário...</option>
                                @foreach($usuariosDisponiveis as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            @error('novo_usuario_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small text-uppercase fw-bold">Perfil de Gestão</label>
                            @foreach($perfisGestao as $perfil)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" 
                                           wire:model="novo_perfil_id" 
                                           value="{{ $perfil['id'] }}" 
                                           id="perfil_{{ $perfil['id'] }}">
                                    <label class="form-check-label" for="perfil_{{ $perfil['id'] }}">
                                        {{ $perfil['label'] }}
                                    </label>
                                </div>
                            @endforeach
                            @error('novo_perfil_id') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary gradient-theme-btn w-100 py-2 fw-bold">
                            <i class="bi bi-plus-lg me-2"></i>Atribuir ao Plano
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Coluna da Direita: Lista Atual -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-primary"></i>Responsáveis Atuais</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Nome / E-mail</th>
                                    <th>Perfil</th>
                                    <th class="text-end pe-4">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($responsaveis as $resp)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                                    {{ substr($resp->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block">{{ $resp->name }}</span>
                                                    <small class="text-muted">{{ $resp->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $resp->cod_perfil === \App\Models\PerfilAcesso::GESTOR_RESPONSAVEL ? 'bg-primary' : 'bg-info' }} bg-opacity-10 text-{{ $resp->cod_perfil === \App\Models\PerfilAcesso::GESTOR_RESPONSAVEL ? 'primary' : 'info' }} border border-{{ $resp->cod_perfil === \App\Models\PerfilAcesso::GESTOR_RESPONSAVEL ? 'primary' : 'info' }} rounded-pill px-3">
                                                {{ $resp->dsc_perfil }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button wire:click="remover('{{ $resp->id }}')" 
                                                    class="btn btn-sm btn-outline-danger border-0"
                                                    onclick="return confirm('Remover atribuição?')">
                                                <i class="bi bi-person-x fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="bi bi-person-exclamation fs-1 opacity-25 mb-3 d-block"></i>
                                            Nenhum responsável atribuído a este plano.
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