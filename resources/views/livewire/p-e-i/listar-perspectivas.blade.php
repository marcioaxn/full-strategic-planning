<div>
    <!-- Header -->
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="icon-circle-header gradient-theme-icon">
                    <i class="bi bi-layers-fill"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">{{ __('Perspectivas BSC') }}</h1>
                <span class="badge-modern badge-count">
            </div>
            <p class="text-muted mb-0">
                {{ __('Gerencie as 4 perspectivas fundamentais do Balanced Scorecard para o ciclo:') }} 
                @if($peiAtivo)
                    <strong>{{ $peiAtivo->dsc_pei }}</strong>
                @else
                    <span class="text-danger fw-bold">{{ __('Sem PEI Ativo') }}</span>
                @endif
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($peiAtivo)
                <button type="button" class="btn btn-primary gradient-theme-btn px-4 shadow-sm" wire:click="create">
                    <i class="bi bi-plus-lg me-1"></i> {{ __('Nova Perspectiva') }}
                </button>
            @endif
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-modern alert-success alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert">
            <div class="icon-circle-mini"><i class="bi bi-check-circle-fill"></i></div>
            <span class="flex-grow-1">{{ session('status') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-modern alert-danger alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert">
            <div class="icon-circle-mini"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <span class="flex-grow-1">{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Mentor de IA --}}
    @if($peiAtivo && $aiEnabled)
        <div class="ai-mentor-wrapper animate-fade-in">
            <button wire:click="pedirAjudaIA" wire:loading.attr="disabled" class="ai-magic-button shadow-sm">
                <span wire:loading.remove wire:target="pedirAjudaIA">
                    <i class="bi bi-robot"></i> {{ __('Sugerir Perspectivas Estratégicas com IA') }}
                </span>
                <span wire:loading wire:target="pedirAjudaIA">
                    <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Analisando Missão e Visão...') }}
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
                                @foreach($aiSuggestion as $sugestao)
                                    <div class="list-group-item d-flex align-items-start justify-content-between p-3 bg-light bg-opacity-25 hover-bg-white transition-all gap-3">
                                        <div class="d-flex align-items-start gap-3 flex-grow-1">
                                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center mt-1" style="width: 24px; height: 24px; flex-shrink: 0;">
                                                {{ $sugestao['ordem'] }}
                                            </span>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $sugestao['nome'] }}</div>
                                                <p class="small text-muted mb-0 mt-1 lh-sm">{{ $sugestao['descricao'] ?? '' }}</p>
                                            </div>
                                        </div>
                                        <button wire:click="aplicarSugestao('{{ $sugestao['nome'] }}', {{ $sugestao['ordem'] }})" 
                                                wire:loading.attr="disabled"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold flex-shrink-0">
                                            <i class="bi bi-plus-lg me-1"></i> {{ __('Adicionar') }}
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="markdown-content">
                                {!! Str::markdown($aiSuggestion) !!}
                            </div>
                        @endif
                        <div class="mt-3 pt-3 border-top d-flex align-items-center gap-2 text-muted small italic">
                            <i class="bi bi-info-circle"></i>
                            {{ __('Clique em "Adicionar" para gravar a sugestão diretamente no seu plano.') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if(!$peiAtivo)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Nenhum PEI ativo encontrado. Configure um PEI antes de gerenciar as perspectivas.
        </div>
    @else
        <!-- Lista de Perspectivas -->
        <div class="card">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-ul me-2"></i>Perspectivas Cadastradas</span>
                    <span class="badge bg-primary">{{ count($perspectivas) }} perspectiva(s)</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if(count($perspectivas) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;" class="text-center">Ordem</th>
                                    <th>Perspectiva</th>
                                    <th style="width: 150px;" class="text-center">Objetivos</th>
                                    <th style="width: 120px;" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($perspectivas as $perspectiva)
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge bg-secondary rounded-pill">
                                                {{ $perspectiva->num_nivel_hierarquico_apresentacao }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="icon-circle-header gradient-theme-icon me-3">
                                                    <i class="bi bi-layers text-white"></i>
                                                </div>
                                                <div>
                                                    <a href="{{ route('pei.perspectivas.detalhes', $perspectiva->cod_perspectiva) }}" wire:navigate class="fw-bold text-dark text-decoration-none hover-primary">
                                                        {{ $perspectiva->dsc_perspectiva }}
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">
                                                {{ $perspectiva->objetivos_count ?? $perspectiva->objetivos()->count() }} objetivo(s)
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('pei.perspectivas.detalhes', $perspectiva->cod_perspectiva) }}" wire:navigate class="btn btn-outline-info" title="Detalhar">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-primary" wire:click="edit('{{ $perspectiva->cod_perspectiva }}')" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" wire:click="confirmDelete('{{ $perspectiva->cod_perspectiva }}')" title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                        <h5 class="text-muted">Nenhuma perspectiva cadastrada</h5>
                        <p class="text-muted mb-3">Comece adicionando as perspectivas do seu Balanced Scorecard</p>
                        <button type="button" class="btn btn-primary gradient-theme-btn px-4 shadow-sm" wire:click="create">
                            <i class="bi bi-plus-lg me-1"></i> Adicionar Perspectiva
                        </button>
                    </div>
                @endif
            </div>
        </div>

    @if($peiAtivo)
        {{-- BSC Perspectives Help Section (Educational Pattern) --}}
        <div class="card card-modern mt-4 border-0 shadow-sm educational-card-gradient">
            <div class="card-body p-4 text-white">
                <div class="row g-4">
                    {{-- Main Explanation --}}
                    <div class="col-12">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="flex-shrink-0">
                                <div class="icon-circle bg-white bg-opacity-25">
                                    <i class="bi bi-lightbulb-fill fs-3 text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-2 text-white">{{ __('O que são as Perspectivas do BSC?') }}</h5>
                                <p class="mb-0 text-white-50" style="line-height: 1.6;">
                                    As <strong>Perspectivas</strong> são as quatro dimensões fundamentais do <em>Balanced Scorecard</em>. Elas permitem que a organização visualize sua estratégia de forma equilibrada, indo além dos indicadores financeiros tradicionais e cobrindo os pilares que sustentam o sucesso a longo prazo.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Perspectives Detail Cards --}}
                    <div class="col-12">
                        <div class="bg-white bg-opacity-10 rounded-3 p-4">
                            <h5 class="fw-bold mb-4 text-white text-center">
                                <i class="bi bi-grid-3x3-gap-fill me-2"></i>
                                Os 4 Pilares da Estratégia
                            </h5>

                            <div class="row g-3">
                                {{-- Aprendizado --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="bg-body rounded-3 p-3 h-100 text-body shadow-sm">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary text-white fw-bold px-2 py-1">1</span>
                                            <h6 class="fw-bold mb-0 small">{{ __('Aprendizado e Crescimento') }}</h6>
                                        </div>
                                        <p class="small mb-2 opacity-90">Foco no capital humano, sistemas, cultura e infraestrutura.</p>
                                        <div class="bg-body-secondary rounded p-2 small">
                                            <i class="bi bi-mortarboard-fill me-1"></i>
                                            <strong>Pergunta-chave:</strong><br>
                                            "Como sustentamos nossa capacidade de mudar e melhorar?"
                                        </div>
                                    </div>
                                </div>

                                {{-- Processos --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="bg-body rounded-3 p-3 h-100 text-body shadow-sm">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary text-white fw-bold px-2 py-1">2</span>
                                            <h6 class="fw-bold mb-0 small">{{ __('Processos Internos') }}</h6>
                                        </div>
                                        <p class="small mb-2 opacity-90">Foco na excelência operacional e nos processos críticos de negócio.</p>
                                        <div class="bg-body-secondary rounded p-2 small">
                                            <i class="bi bi-gear-wide-connected me-1"></i>
                                            <strong>Pergunta-chave:</strong><br>
                                            "Em quais processos devemos ser excelentes para ter sucesso?"
                                        </div>
                                    </div>
                                </div>

                                {{-- Clientes --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="bg-body rounded-3 p-3 h-100 text-body shadow-sm">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary text-white fw-bold px-2 py-1">3</span>
                                            <h6 class="fw-bold mb-0 small">{{ __('Clientes / Usuários') }}</h6>
                                        </div>
                                        <p class="small mb-2 opacity-90">Foco na satisfação, retenção e na proposta de valor para o público-alvo.</p>
                                        <div class="bg-body-secondary rounded p-2 small">
                                            <i class="bi bi-people-fill me-1"></i>
                                            <strong>Pergunta-chave:</strong><br>
                                            "Como o cliente nos enxerga e o que ele espera de nós?"
                                        </div>
                                    </div>
                                </div>

                                {{-- Financeira --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="bg-body rounded-3 p-3 h-100 text-body shadow-sm">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary text-white fw-bold px-2 py-1">4</span>
                                            <h6 class="fw-bold mb-0 small">{{ __('Financeira / Sociedade') }}</h6>
                                        </div>
                                        <p class="small mb-2 opacity-90">Foco nos resultados financeiros e no valor entregue ao cidadão/sociedade.</p>
                                        <div class="bg-body-secondary rounded p-2 small">
                                            <i class="bi bi-cash-stack me-1"></i>
                                            <strong>Pergunta-chave:</strong><br>
                                            "Como devemos ser vistos pelos nossos stakeholders?"
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bottom Advice --}}
                    <div class="col-12">
                        <div class="bg-body rounded-3 p-3 text-body shadow-sm">
                            <div class="d-flex align-items-start gap-2 mb-2">
                                <i class="bi bi-info-circle-fill mt-1 text-primary"></i>
                                <div>
                                    <strong class="small d-block mb-1">Dica de Gestão:</strong>
                                    <p class="mb-0 small opacity-90">
                                        A ordem das perspectivas cria uma relação de causa e efeito. O <strong>Aprendizado</strong> sustenta os <strong>Processos</strong>, que satisfazem os <strong>Clientes</strong>, resultando no sucesso <strong>Financeiro</strong>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

        <!-- Modal de Criação/Edição -->
        @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-{{ $perspectivaId ? 'pencil' : 'plus-circle' }} me-2"></i>
                            {{ $perspectivaId ? 'Editar' : 'Nova' }} Perspectiva
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="dsc_perspectiva" class="form-label">Nome da Perspectiva <span class="text-danger">*</span></label>
                                <input type="text" wire:model="dsc_perspectiva" id="dsc_perspectiva" class="form-control @error('dsc_perspectiva') is-invalid @enderror" placeholder="Ex: Financeira, Clientes, Processos Internos..." required>
                                @error('dsc_perspectiva')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="num_nivel_hierarquico_apresentacao" class="form-label">Ordem de Apresentação <span class="text-danger">*</span></label>
                                <input type="number" wire:model="num_nivel_hierarquico_apresentacao" id="num_nivel_hierarquico_apresentacao" class="form-control @error('num_nivel_hierarquico_apresentacao') is-invalid @enderror" min="1" required>
                                <div class="form-text">Define a posição desta perspectiva no mapa estratégico (1 = topo)</div>
                                @error('num_nivel_hierarquico_apresentacao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">
                                <i class="bi bi-x-lg me-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> {{ $perspectivaId ? 'Atualizar' : 'Salvar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endif

    {{-- Modal de Exclusão --}}
    <x-confirmation-modal wire:model.live="showDeleteModal">
        <x-slot name="title">
            <div class="modal-header-modern">
                <div class="icon-circle-mini modal-icon-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold text-dark">{{ __('Excluir Perspectiva') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Esta ação é irreversível') }}</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="delete-confirmation">
                <p class="mb-2 text-dark">
                    {{ __('Tem certeza que deseja excluir esta perspectiva estratégica?') }}
                </p>
                <div class="alert alert-warning bg-warning-subtle border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção:</strong> Todos os objetivos, indicadores e planos associados a esta perspectiva também serão removidos.
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDeleteModal', false)" wire:loading.attr="disabled" class="btn-modern">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-danger-button wire:click="delete" wire:loading.attr="disabled" class="btn-delete-modern ms-2">
                <span wire:loading.remove wire:target="delete">
                    <i class="bi bi-trash me-1"></i>{{ __('Excluir Agora') }}
                </span>
                <span wire:loading wire:target="delete">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                    {{ __('Excluindo...') }}
                </span>
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
