<div>
    {{-- Cabeçalho Interno --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="icon-circle-header gradient-theme-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ __('Temas Norteadores') }}</h1>
                <span class="badge-modern badge-count">
                    {{ $temas->count() }}
                </span>
            </div>
            <p class="text-muted mb-0">
                @if($peiAtivo)
                    Gerenciando temas para o ciclo: <strong>{{ $peiAtivo->dsc_pei }}</strong>
                @else
                    <span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Nenhum Ciclo PEI Ativo encontrado.</span>
                @endif
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($peiAtivo)
                <button wire:click="create" class="btn btn-primary gradient-theme-btn shadow-sm">
                    <i class="bi bi-plus-lg me-2"></i>Novo Tema
                </button>
            @endif
        </div>
    </div>

    {{-- Seção Educativa: O que são Temas Norteadores --}}
    <div class="card border-0 shadow-sm mb-4 educational-card-gradient" x-data="{ expanded: false }">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle bg-white bg-opacity-25">
                        <i class="bi bi-book-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">
                            <i class="bi bi-mortarboard me-2"></i>{{ __('O que são Temas Norteadores?') }}
                        </h5>
                        <p class="mb-0 text-white-50 small">
                            {{ __('Aprenda a criar temas alinhados à estratégia institucional') }}
                        </p>
                    </div>
                </div>
                <button @click="expanded = !expanded" class="btn btn-link text-white text-decoration-none p-0" type="button">
                    <i class="bi fs-4" :class="expanded ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
            </div>
        </div>

        <div x-show="expanded" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
            <div class="card-body p-4 bg-white border-top">
                {{-- Introdução --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-info-circle me-2"></i>{{ __('O que são Temas Norteadores?') }}
                    </h6>
                    <p class="text-muted mb-3">
                        <strong>Temas Norteadores</strong> (anteriormente Objetivos Estratégicos) são declarações que descrevem <strong>o que a organização pretende alcançar</strong> no médio e longo prazo
                        para cumprir sua missão e realizar sua visão de futuro. Eles traduzem a estratégia em diretrizes concretas.
                    </p>
                    <p class="text-muted mb-0">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        <strong>Por que são importantes?</strong> Temas norteadores conectam a alta gestão (missão/visão) com a execução tática e operacional.
                        São o elo entre "onde queremos chegar" e "como vamos chegar lá".
                    </p>
                </div>

                {{-- Metodologia SMART --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-bullseye me-2"></i>{{ __('Critérios para Bons Temas') }}
                    </h6>
                    <p class="small text-muted mb-3">
                        Um bom tema norteador deve ser claro e direcionador:
                    </p>

                    <div class="row g-3">
                        {{-- S - Specific --}}
                        <div class="col-md-6">
                            <div class="card border-2 border-primary h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-crosshair"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-primary">Clareza e Direção</h6>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        O tema deve deixar claro <strong>"O quê"</strong> a organização busca priorizar.
                                    </p>
                                    <div class="bg-light p-2 rounded">
                                        <p class="x-small mb-1"><strong class="text-danger">❌ Ruim:</strong> "Melhorar coisas"</p>
                                        <p class="x-small mb-0"><strong class="text-success">✅ Bom:</strong> "Promover a transformação digital e inovação nos serviços"</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- R - Relevant --}}
                        <div class="col-md-6">
                            <div class="card border-2 border-info h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icon-circle-mini bg-info bg-opacity-10 text-info">
                                            <i class="bi bi-link-45deg"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-info">Alinhamento Estratégico</h6>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        Está <strong>conectado</strong> com a missão, visão e valores da organização.
                                    </p>
                                    <div class="bg-light p-2 rounded">
                                        <p class="x-small mb-1"><strong class="text-danger">❌ Ruim:</strong> Tema isolado, sem conexão com missão</p>
                                        <p class="x-small mb-0"><strong class="text-success">✅ Bom:</strong> Tema contribui diretamente para realizar a visão institucional</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Hierarquia --}}
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-diagram-3 me-2"></i>{{ __('Níveis de Planejamento') }}
                    </h6>
                    <p class="small text-muted mb-3">
                        O planejamento se organiza em níveis:
                    </p>

                    <div class="d-flex flex-column gap-2">
                        {{-- Estratégico (Topo) --}}
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 border border-danger bg-danger bg-opacity-5">
                            <div class="icon-circle-mini bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-trophy"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0 text-danger">1. Estratégico (Temas Norteadores)</h6>
                                <p class="x-small text-muted mb-0">
                                    <strong>Foco:</strong> Visão de futuro, grandes diretrizes institucionais<br>
                                    <strong>Ex:</strong> "Excelência na Gestão Pública e Transparência"
                                </p>
                            </div>
                            <span class="badge bg-danger">TOPO</span>
                        </div>

                        {{-- Arrow --}}
                        <div class="text-center text-muted">
                            <i class="bi bi-arrow-down-short fs-3"></i>
                            <p class="x-small mb-0">orienta os</p>
                        </div>

                        {{-- Tático (Meio) --}}
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 border border-warning bg-warning bg-opacity-5">
                            <div class="icon-circle-mini bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-flag"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0 text-warning">2. Tático (Objetivos BSC)</h6>
                                <p class="x-small text-muted mb-0">
                                    <strong>Foco:</strong> Objetivos específicos nas perspectivas (Financeira, Clientes, etc.)<br>
                                    <strong>Ex:</strong> "Aumentar a satisfação do cidadão com o atendimento digital"
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <p class="small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Nesta página:</strong> Você gerencia <strong class="text-danger">Temas Norteadores</strong> (nível estratégico institucional).
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!$peiAtivo)
        <div class="alert alert-modern alert-danger shadow-sm border-0 d-flex align-items-center p-4">
            <i class="bi bi-exclamation-octagon fs-2 me-4"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Nenhum Ciclo PEI Ativo</h5>
                <p class="mb-0">Não é possível gerenciar temas norteadores sem um ciclo ativo definido.</p>
            </div>
        </div>
    @else
        {{-- Mentor de IA --}}
        @if($aiEnabled)
            <div class="ai-mentor-wrapper animate-fade-in">
                <button wire:click="pedirAjudaIA" wire:loading.attr="disabled" class="ai-magic-button shadow-sm">
                    <span wire:loading.remove wire:target="pedirAjudaIA">
                        <i class="bi bi-robot"></i> {{ __('Sugerir Temas com IA') }}
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
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar por tema...">
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
                            <th class="ps-4" style="width: 60%">Descrição do Tema Norteador</th>
                            <th>Unidade</th>
                            <th>Ciclo PEI</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($temas as $obj)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark mb-1">{{ $obj->nom_tema_norteador }}</div>
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
                                        <button wire:click="edit('{{ $obj->cod_tema_norteador }}')" class="btn btn-sm btn-icon btn-ghost-primary rounded-circle" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="confirmDelete('{{ $obj->cod_tema_norteador }}')" class="btn btn-sm btn-icon btn-ghost-danger rounded-circle" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-bullseye fs-1 opacity-25 mb-3 d-block"></i>
                                    Nenhum tema norteador encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3">
                {{ $temas->links() }}
            </div>
        </div>
    @endif

    {{-- Modal de Cadastro/Edição --}}
    <div class="modal fade @if($showModal) show @endif" tabindex="-1" style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header gradient-theme text-white border-0 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-{{ $temaId ? 'pencil' : 'plus-circle' }} me-2"></i>
                        {{ $temaId ? 'Editar Tema Norteador' : 'Novo Tema Norteador' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="resetForm"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4 bg-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase">Descrição do Tema <span class="text-danger">*</span></label>
                                <textarea wire:model="nom_tema_norteador" class="form-control @error('nom_tema_norteador') is-invalid @enderror" rows="3" placeholder="Ex: Ampliar a capilaridade dos serviços públicos..."></textarea>
                                @error('nom_tema_norteador') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                            <i class="bi bi-check-lg me-1"></i> {{ $temaId ? 'Atualizar' : 'Salvar Tema' }}
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
                    <h5 class="mb-1 fw-bold text-dark">{{ __('Excluir Tema Norteador') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Esta ação é irreversível') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation text-start">
                <p class="mb-2 text-dark">
                    {{ __('Tem certeza que deseja excluir este tema norteador?') }}
                </p>
                <div class="alert alert-warning bg-warning-subtle border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção:</strong> Esta ação removerá o tema do planejamento corporativo.
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
</div>