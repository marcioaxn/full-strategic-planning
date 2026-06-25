<div>
    {{-- Header --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" wire:navigate class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Valores Institucionais</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2 mt-1">
                <div class="icon-circle-header gradient-theme-icon"><i class="bi bi-heart-fill"></i></div>
                <h1 class="h3 fw-bold mb-0">Valores Institucionais</h1>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ count($valores) }}</span>
            </div>
            <p class="text-muted mb-0 mt-1">
                @if($peiAtivo)
                    Valores do ciclo <strong>{{ $peiAtivo->dsc_pei }}</strong>{{ $organizacaoNome ? ' · '.$organizacaoNome : '' }}
                @else
                    <span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Nenhum Ciclo PEI ativo encontrado.</span>
                @endif
            </p>
        </div>

        @if($peiAtivo && $organizacaoId)
            <button type="button" class="btn btn-primary gradient-theme-btn px-4 shadow-sm" wire:click="create">
                <i class="bi bi-plus-lg me-1"></i> Novo Valor
            </button>
        @endif
    </div>

    {{-- Flash --}}
    @if (session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span class="flex-grow-1">{{ session('status') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span class="flex-grow-1">{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Seção educativa --}}
    <div class="card border-0 shadow-sm mb-4 educational-card-gradient" x-data="{ expanded: false }">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle bg-white bg-opacity-25">
                        <i class="bi bi-book-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">
                            <i class="bi bi-mortarboard me-2"></i>Entendendo os três conceitos da Identidade Estratégica
                        </h5>
                        <p class="mb-0 text-white-50 small">Valores, Temas Norteadores e Missão/Visão — cada um tem papel distinto</p>
                    </div>
                </div>
                <button @click="expanded = !expanded" class="btn btn-link text-white text-decoration-none p-0" type="button">
                    <i class="bi fs-4" :class="expanded ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
            </div>
        </div>
        <div x-show="expanded" x-transition style="display:none;">
            <div class="card-body p-4 bg-white border-top">

                <div class="row g-4 mb-4">
                    {{-- Valores --}}
                    <div class="col-md-4">
                        <div class="card h-100 border-2 border-primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="icon-circle-mini bg-primary bg-opacity-10 text-primary"><i class="bi bi-heart-fill"></i></div>
                                    <h6 class="fw-bold mb-0 text-primary">Valores Institucionais</h6>
                                </div>
                                <p class="small text-muted mb-2">
                                    <strong>O que são:</strong> Princípios éticos e comportamentais inegociáveis que definem <em>como</em> a organização age — independentemente do ciclo ou do governo de plantão.
                                </p>
                                <p class="small text-muted mb-2">
                                    <strong>Natureza:</strong> Permanentes e transversais. Não mudam a cada ciclo PEI.
                                </p>
                                <p class="small text-muted mb-0">
                                    <strong>Exemplos:</strong> "Integridade", "Transparência", "Inovação", "Respeito ao cidadão".
                                </p>
                            </div>
                            <div class="card-footer bg-primary bg-opacity-10 border-0">
                                <small class="text-primary fw-semibold"><i class="bi bi-check-circle me-1"></i>Esta tela — você está aqui</small>
                            </div>
                        </div>
                    </div>

                    {{-- Temas Norteadores --}}
                    <div class="col-md-4">
                        <div class="card h-100 border-2 border-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="icon-circle-mini bg-warning bg-opacity-10 text-warning"><i class="bi bi-compass-fill"></i></div>
                                    <h6 class="fw-bold mb-0 text-warning">Temas Norteadores</h6>
                                </div>
                                <p class="small text-muted mb-2">
                                    <strong>O que são:</strong> Prioridades estratégicas transversais do <em>ciclo</em> PEI — definem <em>onde</em> concentrar esforços neste período.
                                </p>
                                <p class="small text-muted mb-2">
                                    <strong>Natureza:</strong> Temporários (duram o ciclo). Derivam do diagnóstico ambiental (SWOT/PESTEL).
                                </p>
                                <p class="small text-muted mb-0">
                                    <strong>Exemplos:</strong> "Transformação Digital", "Atendimento ao Cidadão", "Sustentabilidade Fiscal".
                                </p>
                            </div>
                            <div class="card-footer bg-warning bg-opacity-10 border-0">
                                <small class="text-warning fw-semibold"><i class="bi bi-arrow-right-circle me-1"></i>Menu PEI → Temas Norteadores</small>
                            </div>
                        </div>
                    </div>

                    {{-- Missão / Visão --}}
                    <div class="col-md-4">
                        <div class="card h-100 border-2 border-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="icon-circle-mini bg-success bg-opacity-10 text-success"><i class="bi bi-fingerprint"></i></div>
                                    <h6 class="fw-bold mb-0 text-success">Missão e Visão</h6>
                                </div>
                                <p class="small text-muted mb-2">
                                    <strong>O que são:</strong> A razão de existir da organização (Missão) e o estado futuro que ela aspira alcançar (Visão). Junto com os Valores, formam os três pilares da Identidade Estratégica.
                                </p>
                                <p class="small text-muted mb-2">
                                    <strong>Natureza:</strong> Semi-permanentes. Revisados a cada ciclo, mas raramente mudam radicalmente.
                                </p>
                                <p class="small text-muted mb-0">
                                    <strong>Referência:</strong> BSC (Kaplan & Norton) e GPPEI/MGI 2025, p. 44.
                                </p>
                            </div>
                            <div class="card-footer bg-success bg-opacity-10 border-0">
                                <small class="text-success fw-semibold"><i class="bi bi-arrow-right-circle me-1"></i>Menu PEI → Identidade Estratégica</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-primary border-0 small mb-0">
                    <i class="bi bi-lightbulb-fill me-2"></i>
                    <strong>Regra prática para não confundir:</strong>
                    Valores respondem "Como agimos?", Temas Norteadores respondem "Onde focamos agora?", Missão responde "Por que existimos?" e Visão responde "Onde queremos chegar?".
                    São quatro perguntas completamente diferentes — por isso existem como conceitos separados no sistema.
                </div>

            </div>
        </div>
    </div>

    {{-- Conteúdo --}}
    @if(!$peiAtivo)
        <div class="card card-modern border-dashed">
            <div class="card-body p-5 text-center">
                <i class="bi bi-calendar-x fs-1 text-muted opacity-50 d-block mb-3"></i>
                <h5 class="fw-bold">Nenhum ciclo PEI ativo</h5>
                <p class="text-muted mb-3">Selecione ou crie um ciclo PEI para gerenciar os valores institucionais.</p>
                <a href="{{ route('pei.ciclos') }}" wire:navigate class="btn btn-primary gradient-theme-btn">
                    <i class="bi bi-calendar-range me-1"></i> Gerenciar Ciclos PEI
                </a>
            </div>
        </div>
    @elseif(!$organizacaoId)
        <div class="card card-modern border-dashed">
            <div class="card-body p-5 text-center">
                <i class="bi bi-building fs-1 text-muted opacity-50 d-block mb-3"></i>
                <h5 class="fw-bold">Selecione uma organização</h5>
                <p class="text-muted mb-0">Use o seletor de organização no cabeçalho para visualizar e cadastrar os valores da unidade.</p>
            </div>
        </div>
    @elseif(count($valores) === 0)
        <div class="card card-modern border-dashed">
            <div class="card-body p-5 text-center">
                <i class="bi bi-heart fs-1 text-muted opacity-50 d-block mb-3"></i>
                <h5 class="fw-bold">Nenhum valor cadastrado</h5>
                <p class="text-muted mb-3">Comece registrando os princípios que orientam a sua organização.</p>
                <button wire:click="create" class="btn btn-primary gradient-theme-btn">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar o primeiro valor
                </button>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($valores as $valor)
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100 valor-card">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="icon-circle bg-primary bg-opacity-10 text-primary flex-shrink-0"><i class="bi bi-gem"></i></div>
                                    <h6 class="fw-bold mb-0 text-body">{{ $valor->nom_valor }}</h6>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon btn-ghost-secondary rounded-circle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><button class="dropdown-item" wire:click="edit('{{ $valor->cod_valor }}')"><i class="bi bi-pencil me-2"></i>Editar</button></li>
                                        <li><button class="dropdown-item text-danger" wire:click="delete('{{ $valor->cod_valor }}')" wire:confirm="Excluir o valor '{{ $valor->nom_valor }}'? Esta ação é irreversível."><i class="bi bi-trash me-2"></i>Excluir</button></li>
                                    </ul>
                                </div>
                            </div>
                            <p class="text-muted small mb-0">{{ $valor->dsc_valor ?: 'Sem descrição.' }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal de cadastro/edição --}}
    @if($showModal)
    <div class="modal fade show" tabindex="-1" role="dialog" style="display:block; background:rgba(0,0,0,.5); z-index:1055;" wire:click.self="$set('showModal', false)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-circle-mini bg-white bg-opacity-25 text-white"><i class="bi bi-{{ $valorId ? 'pencil-square' : 'plus-circle' }}"></i></div>
                        <h5 class="modal-title fw-bold mb-0">{{ $valorId ? 'Editar Valor' : 'Novo Valor Institucional' }}</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Nome do Valor <span class="text-danger">*</span></label>
                            <input type="text" wire:model="nom_valor" class="form-control form-control-lg @error('nom_valor') is-invalid @enderror" placeholder="Ex.: Transparência, Ética, Inovação">
                            @error('nom_valor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold small text-uppercase text-muted">Descrição</label>
                            <textarea wire:model="dsc_valor" class="form-control @error('dsc_valor') is-invalid @enderror" rows="4" placeholder="Explique o significado deste valor para a organização..."></textarea>
                            @error('dsc_valor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="$set('showModal', false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-5 rounded-pill">
                            <i class="bi bi-check-lg me-2"></i>Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <style>
        .valor-card { border-radius: .875rem; transition: transform .2s ease, box-shadow .2s ease; }
        .valor-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(27,64,142,.12) !important; }
    </style>
</div>
