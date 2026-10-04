<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" wire:navigate class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('riscos.index') }}" wire:navigate class="text-decoration-none">Gestão de Riscos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Planos de Mitigação</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0">
                <i class="bi bi-shield-plus me-2 text-success"></i>Planos de Mitigação
            </h2>
            <p class="text-muted mb-0">{{ $risco->dsc_titulo }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('riscos.index') }}" wire:navigate class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            @can('update', $risco)
            <button wire:click="create" class="btn btn-success gradient-theme">
                <i class="bi bi-shield-plus me-2"></i>Novo Plano
            </button>
            @endcan
        </div>
    </div>

    <x-secao-educativa
        titulo="O que são os planos de mitigação?"
        subtitulo="O plano do que fazer com um risco antes que ele aconteça."
        icone="shield-plus"
        por-que="Identificar o risco não basta: os de prioridade alta precisam de ações concretas, com responsável e prazo."
        :passos="[
            'Confira no resumo do topo o nível do risco que está sendo tratado.',
            'Clique em Novo Plano e escolha o tipo de resposta: mitigar, evitar, transferir ou aceitar.',
            'Descreva a ação concreta, o responsável, o prazo e, se houver, o custo estimado.',
            'Acompanhe a situação do plano até concluir.',
            'Depois da ação, reavalie a probabilidade e o impacto do risco.',
        ]"
        exemplo="Para o risco “Vazamento de dados pessoais no novo sistema”, a Agência Federal de Benefícios Educacionais (fictícia) escolhe mitigar: teste de invasão antes da implantação, pela equipe de segurança, até junho."
        dica="riscos de probabilidade e impacto altos são prioridade para resposta; os baixos podem apenas ser monitorados."
        referencia="GPPEI p. 93–96 · ISO 31000:2018, seção 6.5">
        Se pode chover no passeio, você pode levar guarda-chuva (<strong>mitigar</strong>), trocar o passeio por um cinema
        (<strong>evitar</strong>), contratar quem resolva por você, como um seguro (<strong>transferir</strong>), ou ir assim mesmo,
        sabendo do risco (<strong>aceitar</strong>). O plano de mitigação registra a resposta escolhida, quem cuida dela e até quando.
    </x-secao-educativa>

    @if (session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Resumo do Risco -->
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light bg-opacity-50">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="icon-shape rounded-3 p-3 me-4 shadow" style="{{ $risco->estiloDoSeloDeNivel() }}">
                        <i class="bi bi-exclamation-triangle fs-3"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">{{ $risco->dsc_titulo }}</h5>
                        <div class="d-flex gap-3">
                            <small class="text-muted"><i class="bi bi-layers me-1"></i> {{ $risco->dsc_categoria }}</small>
                            <small class="text-muted"><i class="bi bi-reception-4 me-1"></i> Nível: <strong>{{ $risco->num_nivel_risco }} ({{ $risco->getNivelRiscoLabel() }})</strong></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Planos -->
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Tipo</th>
                                <th>Ação de Mitigação / Descrição</th>
                                <th>Responsável</th>
                                <th>Prazo</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mitigacoes as $m)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge {{ $m->dsc_tipo === 'Prevenção' ? 'bg-primary' : 'bg-info' }} bg-opacity-10 text-{{ $m->dsc_tipo === 'Prevenção' ? 'primary' : 'info' }} border border-{{ $m->dsc_tipo === 'Prevenção' ? 'primary' : 'info' }} rounded-pill px-3">
                                            {{ $m->dsc_tipo }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span class="fw-semibold text-dark">{{ $m->txt_descricao }}</span>
                                    </td>
                                    <td>
                                        <small class="text-dark"><i class="bi bi-person me-1"></i> {{ $m->responsavel->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small class="fw-bold {{ $m->isAtrasado() ? 'text-danger' : 'text-dark' }}">
                                            <i class="bi bi-calendar-event me-1"></i> {{ $m->dte_prazo?->format('d/m/Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $m->getStatusBadgeClass() }} rounded-pill px-3">{{ $m->dsc_status }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        @can('update', $risco)
                                        <button wire:click="edit('{{ $m->cod_mitigacao }}')" class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-pencil"></i></button>
                                        {{-- wire:confirm: com onclick="return confirm()" o Cancelar não impedia a exclusão. --}}
                                        <button type="button" wire:click="delete('{{ $m->cod_mitigacao }}')" wire:confirm="Remover este plano de mitigação?" class="btn btn-sm btn-outline-danger border-0" title="Excluir"><i class="bi bi-trash"></i></button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-5 text-muted">Nenhum plano de mitigação cadastrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal CRUD -->
    <div class="modal fade @if($showModal) show @endif" tabindex="-1" style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header gradient-theme text-white border-0">
                    <h5 class="modal-title fw-bold">Plano de Mitigação</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Tipo de Plano</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" wire:model="form.dsc_tipo" value="Prevenção" id="t_prev" autocomplete="off">
                                <label class="btn btn-outline-primary" for="t_prev">Prevenção</label>
                                <input type="radio" class="btn-check" wire:model="form.dsc_tipo" value="Contingência" id="t_cont" autocomplete="off">
                                <label class="btn btn-outline-info" for="t_cont">Contingência</label>
                            </div>
                            @error('form.dsc_tipo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Descrição da Ação</label>
                            <textarea wire:model="form.txt_descricao" class="form-control @error('form.txt_descricao') is-invalid @enderror" rows="3" placeholder="O que será feito para mitigar o risco?"></textarea>
                            @error('form.txt_descricao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Responsável</label>
                                <select wire:model="form.cod_responsavel" class="form-select @error('form.cod_responsavel') is-invalid @enderror">
                                    <option value="">Selecione...</option>
                                    @foreach($usuarios as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('form.cod_responsavel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Prazo</label>
                                <input type="date" wire:model="form.dte_prazo" class="form-control @error('form.dte_prazo') is-invalid @enderror">
                                @error('form.dte_prazo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Status</label>
                                <select wire:model="form.dsc_status" class="form-select @error('form.dsc_status') is-invalid @enderror">
                                    <option value="A Fazer">A Fazer</option>
                                    <option value="Em Andamento">Em Andamento</option>
                                    <option value="Concluído">Concluído</option>
                                </select>
                                @error('form.dsc_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Custo Estimado (R$)</label>
                                <input type="number" step="0.01" min="0" wire:model="form.vlr_custo_estimado" class="form-control @error('form.vlr_custo_estimado') is-invalid @enderror" placeholder="Opcional">
                                @error('form.vlr_custo_estimado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-4">Salvar Plano</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>