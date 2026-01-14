<div>
    {{-- Cabeçalho Interno --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="icon-circle-header gradient-theme-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ __('Objetivos Estratégicos') }}</h1>
                <span class="badge-modern badge-count">
                    {{ $objetivos->count() }}
                </span>
            </div>
            <p class="text-muted mb-0">
                @if($peiAtivo)
                    Gerenciando objetivos para o ciclo: <strong>{{ $peiAtivo->dsc_pei }}</strong>
                @else
                    <span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Nenhum Ciclo PEI Ativo encontrado.</span>
                @endif
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($peiAtivo)
                <button wire:click="create" class="btn btn-primary gradient-theme-btn shadow-sm">
                    <i class="bi bi-plus-lg me-2"></i>Novo Objetivo
                </button>
            @endif
        </div>
    </div>

    @if(!$peiAtivo)
        <div class="alert alert-modern alert-danger shadow-sm border-0 d-flex align-items-center p-4">
            <i class="bi bi-exclamation-octagon fs-2 me-4"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Nenhum Ciclo PEI Ativo</h5>
                <p class="mb-0">Não é possível gerenciar objetivos estratégicos sem um ciclo ativo definido.</p>
            </div>
        </div>
    @else
        {{-- Mentor de IA --}}
        @if($aiEnabled)
            <div class="ai-mentor-wrapper animate-fade-in">
                <button wire:click="pedirAjudaIA" wire:loading.attr="disabled" class="ai-magic-button shadow-sm">
                    <span wire:loading.remove wire:target="pedirAjudaIA">
                        <i class="bi bi-robot"></i> {{ __('Sugerir Objetivos Institucionais com IA') }}
                    </span>
                    <span wire:loading wire:target="pedirAjudaIA">
                        <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Analisando contexto organizacional...') }}
                    </span>
                </button>

                @if($aiSuggestion)
                    <div class="ai-insight-card animate-fade-in">
                        <div class="card-header">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-chat-left-dots-fill text-primary"></i>
                                <h6 class="fw-bold mb-0">{{ __('Sugestões do Mentor IA') }}</h6>
                            </div>
                            <button type="button" class="btn-close small" style="font-size: 0.7rem;" wire:click="$set('aiSuggestion', '')"></button>
                        </div>
                        <div class="card-body">
                            @if(is_array($aiSuggestion))
                                <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                                    @foreach($aiSuggestion as $sug)
                                        <div class="list-group-item d-flex align-items-start justify-content-between p-3 bg-light bg-opacity-25 hover-bg-white transition-all gap-3">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-dark">{{ $sug['nome'] }}</div>
                                            </div>
                                            <button wire:click="aplicarSugestao('{{ $sug['nome'] }}')" 
                                                    class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold flex-shrink-0">
                                                <i class="bi bi-plus-lg me-1"></i> {{ __('Adicionar') }}
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Filtros e Busca --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 bg-light rounded-3">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar por título do objetivo...">
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div wire:loading class="spinner-border text-primary spinner-border-sm mt-2" role="status"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabela de Dados --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4" style="width: 60%">Título do Objetivo Estratégico</th>
                            <th>Unidade</th>
                            <th>Ciclo PEI</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($objetivos as $obj)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark mb-1">{{ $obj->nom_objetivo_estrategico }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-3 rounded-pill">
                                        {{ $obj->organizacao->sgl_organizacao ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <small class="fw-semibold text-primary">{{ $obj->pei->dsc_pei }}</small>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button wire:click="edit('{{ $obj->cod_objetivo_estrategico }}')" class="btn btn-sm btn-icon btn-ghost-primary rounded-circle" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="confirmDelete('{{ $obj->cod_objetivo_estrategico }}')" class="btn btn-sm btn-icon btn-ghost-danger rounded-circle" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-bullseye fs-1 opacity-25 mb-3 d-block"></i>
                                    Nenhum objetivo estratégico encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3">
                {{ $objetivos->links() }}
            </div>
        </div>

        {{-- Strategic Objectives Help Section (Educational Pattern) --}}
        <div class="card card-modern mt-4 border-0 shadow-sm educational-card-gradient animate-fade-in">
            <div class="card-body p-4 text-white">
                <div class="row g-4">
                    {{-- Main Explanation --}}
                    <div class="col-12">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="flex-shrink-0">
                                <div class="icon-circle bg-white bg-opacity-25">
                                    <i class="bi bi-shield-lock-fill fs-3 text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-2 text-white">{{ __('O que são Objetivos Estratégicos Institucionais?') }}</h5>
                                <p class="mb-0 text-white-50" style="line-height: 1.6;">
                                    Diferente dos objetivos das perspectivas do BSC, os <strong>Objetivos Estratégicos Institucionais</strong> são metas de altíssimo nível que representam as grandes prioridades da organização para o ciclo do PEI. Eles aparecem em destaque no topo do Mapa Estratégico e servem como o "norte" para todas as unidades da instituição.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Tips Grid --}}
                    <div class="col-md-6">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3 h-100">
                            <h6 class="fw-bold text-white mb-2"><i class="bi bi-star-fill me-2"></i>Propósito Institucional</h6>
                            <p class="small mb-0 opacity-75">Estes objetivos focam na missão global da organização. Eles devem ser amplos o suficiente para englobar várias frentes de trabalho, mas específicos o suficiente para serem alcançados no período de vigência do PEI.</p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3 h-100">
                            <h6 class="fw-bold text-white mb-2"><i class="bi bi-map-fill me-2"></i>Visualização no Mapa</h6>
                            <p class="small mb-0 opacity-75">No Mapa Estratégico, esses objetivos são exibidos acima das perspectivas, demonstrando que o sucesso nas dimensões do BSC (Financeiro, Clientes, Processos, Aprendizado) é o que permite atingir estas metas institucionais.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Cadastro/Edição --}}
    <div class="modal fade @if($showModal) show @endif" tabindex="-1" style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header gradient-theme text-white border-0 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-{{ $objetivoId ? 'pencil' : 'plus-circle' }} me-2"></i>
                        {{ $objetivoId ? 'Editar Objetivo Estratégico' : 'Novo Objetivo Estratégico' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="resetForm"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4 bg-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase">Título do Objetivo <span class="text-danger">*</span></label>
                                <textarea wire:model="nom_objetivo_estrategico" class="form-control @error('nom_objetivo_estrategico') is-invalid @enderror" rows="3" placeholder="Ex: Ampliar a capilaridade dos serviços públicos..."></textarea>
                                @error('nom_objetivo_estrategico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase">Unidade Organizacional <span class="text-danger">*</span></label>
                                <select wire:model="cod_organizacao" class="form-select @error('cod_organizacao') is-invalid @enderror">
                                    <option value="">Selecione uma unidade...</option>
                                    @foreach($organizacoes as $org)
                                        <option value="{{ $org->cod_organizacao }}">{{ $org->nom_organizacao }} ({{ $org->sgl_organizacao }})</option>
                                    @endforeach
                                </select>
                                @error('cod_organizacao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 p-3">
                        <button type="button" class="btn btn-outline-secondary px-4 rounded-pill fw-bold" wire:click="resetForm">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 gradient-theme-btn rounded-pill fw-bold">
                            <i class="bi bi-check-lg me-1"></i> {{ $objetivoId ? 'Atualizar' : 'Salvar Objetivo' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal de Exclusão --}}
    <x-confirmation-modal wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="icon-circle-mini modal-icon-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold text-dark">{{ __('Excluir Objetivo Estratégico') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Esta ação é irreversível') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation text-start">
                <p class="mb-2 text-dark">
                    {{ __('Tem certeza que deseja excluir este objetivo institucional?') }}
                </p>
                <div class="alert alert-warning bg-warning-subtle border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção:</strong> Esta ação removerá o objetivo do planejamento corporativo.
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="resetForm" wire:loading.attr="disabled" class="btn-modern">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-danger-button wire:click="delete" wire:loading.attr="disabled" class="btn-delete-modern ms-2">
                <span wire:loading.remove wire:target="delete">
                    <i class="bi bi-trash me-1"></i>{{ __('Excluir') }}
                </span>
                <span wire:loading wire:target="delete">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                </span>
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    <style>
        .header-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .loading-opacity {
        .btn-icon:hover { transform: scale(1.1); }
        .modal-content { animation: fadeInUp 0.2s ease-out; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</div>
