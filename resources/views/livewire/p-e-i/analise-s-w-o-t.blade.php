<div>
    <style>
        @media print {
            body { background: white !important; }
            .navbar, .sidebar, .leads-header, .alert, .btn, .breadcrumb, footer { display: none !important; }
            .card { border: none !important; shadow: none !important; }
            .container-fluid { padding: 0 !important; width: 100% !important; max-width: 100% !important; }
            .main-content { margin: 0 !important; padding: 0 !important; }
            .swot-print-header { display: block !important; margin-bottom: 20px; text-align: center; }
        }
        .swot-print-header { display: none; }
    </style>

    <div class="swot-print-header">
        <h2>Matriz SWOT</h2>
        <p>{{ $organizacaoNome }} - {{ $peiAtivo->dsc_pei ?? '' }}</p>
    </div>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-grid-3x3-gap me-2"></i>Análise SWOT
            </h4>
            <p class="text-muted mb-0">
                Forças, Fraquezas, Oportunidades e Ameaças
                @if($organizacaoNome)
                    - <strong>{{ $organizacaoNome }}</strong>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            @if($organizacaoId && $peiAtivo && $aiEnabled)
                <button wire:click="pedirAjudaIA" wire:loading.attr="disabled" class="btn btn-outline-primary shadow-sm rounded-pill">
                    <span wire:loading.remove wire:target="pedirAjudaIA">
                        <i class="bi bi-robot"></i> Sugerir com IA
                    </span>
                    <span wire:loading wire:target="pedirAjudaIA">
                        <span class="spinner-border spinner-border-sm me-1"></span>
                    </span>
                </button>
            @endif
            @if($organizacaoId && $peiAtivo)
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i> Imprimir
                </button>
                <button wire:click="toggleModoVisualizacao" class="btn {{ $modoVisualizacao ? 'btn-outline-primary' : 'btn-primary' }}">
                    <i class="bi {{ $modoVisualizacao ? 'bi-pencil-square' : 'bi-eye' }} me-1"></i>
                    {{ $modoVisualizacao ? 'Modo Edição' : 'Modo Apresentação' }}
                </button>
            @endif
        </div>
    </div>

    @if(!$peiAtivo)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Nenhum PEI ativo encontrado. Configure um PEI antes de realizar a análise SWOT.
        </div>
    @elseif(!$organizacaoId)
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Selecione uma organização no menu superior para visualizar ou cadastrar a análise SWOT.
        </div>
    @else
        {{-- Seção Educativa: O que é Análise SWOT --}}
        <div class="card border-0 shadow-sm mb-4 educational-card-gradient" x-data="{ expanded: false }">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle bg-white bg-opacity-25">
                            <i class="bi bi-book-fill fs-4 text-white"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1 text-white">
                                <i class="bi bi-mortarboard me-2"></i>{{ __('O que é Análise SWOT?') }}
                            </h5>
                            <p class="mb-0 text-white small">
                                {{ __('Aprenda a identificar Forças, Fraquezas, Oportunidades e Ameaças') }}
                            </p>
                        </div>
                    </div>
                    <button
                        @click="expanded = !expanded"
                        class="btn btn-sm btn-light rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 36px; height: 36px;"
                        :aria-expanded="expanded"
                        aria-label="{{ __('Expandir/Recolher') }}"
                    >
                        <i class="bi" :class="expanded ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                    </button>
                </div>
            </div>

            <div x-show="expanded"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 style="display: none;"
            >
                <div class="card-body p-4 bg-white border-top">
                    <div class="row g-4">
                        {{-- Introdução --}}
                        <div class="col-12">
                            <div class="alert alert-info border-0 d-flex align-items-start gap-3 mb-0">
                                <div class="icon-circle-mini bg-info bg-opacity-10 text-info flex-shrink-0">
                                    <i class="bi bi-lightbulb-fill"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-2">{{ __('Por que fazer Análise SWOT?') }}</h6>
                                    <p class="mb-2 small">
                                        {{ __('SWOT (Strengths, Weaknesses, Opportunities, Threats) ou FOFA em português, é uma ferramenta estratégica que permite mapear:') }}
                                    </p>
                                    <ul class="small mb-0">
                                        <li><strong>Ambiente Interno:</strong> {{ __('O que VOCÊ CONTROLA - suas Forças e Fraquezas') }}</li>
                                        <li><strong>Ambiente Externo:</strong> {{ __('O que o MERCADO/CONTEXTO traz - Oportunidades e Ameaças') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Matriz Visual --}}
                        <div class="col-12">
                            <div class="card border-2 border-secondary">
                                <div class="card-body p-0">
                                    <div class="row g-0">
                                        {{-- Header da Matriz --}}
                                        <div class="col-12 bg-secondary bg-opacity-10 p-3 border-bottom">
                                            <div class="row">
                                                <div class="col-6 text-center">
                                                    <h6 class="fw-bold mb-0 text-success">
                                                        <i class="bi bi-arrow-up-circle-fill me-1"></i>{{ __('FATORES POSITIVOS') }}
                                                    </h6>
                                                </div>
                                                <div class="col-6 text-center border-start">
                                                    <h6 class="fw-bold mb-0 text-danger">
                                                        <i class="bi bi-arrow-down-circle-fill me-1"></i>{{ __('FATORES NEGATIVOS') }}
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Linha Interna --}}
                                        <div class="col-12">
                                            <div class="row g-0">
                                                <div class="col-1 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center border-end p-2">
                                                    <div class="text-center">
                                                        <i class="bi bi-building d-block fs-5 text-primary mb-1"></i>
                                                        <small class="fw-bold text-primary" style="writing-mode: vertical-rl; transform: rotate(180deg);">INTERNO</small>
                                                    </div>
                                                </div>
                                                <div class="col-11">
                                                    <div class="row g-0">
                                                        <div class="col-md-6 p-3 border-end border-bottom bg-success bg-opacity-5">
                                                            <h6 class="fw-bold text-white mb-2">
                                                                <i class="bi bi-shield-fill-check me-1  text-white"></i>{{ __('FORÇAS') }}
                                                            </h6>
                                                            <p class="small  text-white mb-2 fst-italic">{{ __('O que fazemos BEM?') }}</p>
                                                            <ul class="small mb-0 ps-3  text-white">
                                                                <li>Equipe qualificada</li>
                                                                <li>Infraestrutura moderna</li>
                                                                <li>Processos bem definidos</li>
                                                                <li>Boa reputação institucional</li>
                                                            </ul>
                                                        </div>
                                                        <div class="col-md-6 p-3 border-bottom bg-warning bg-opacity-5">
                                                            <h6 class="fw-bold text-dark mb-2">
                                                                <i class="bi bi-exclamation-triangle-fill me-1 text-dark"></i>{{ __('FRAQUEZAS') }}
                                                            </h6>
                                                            <p class="small text-dark mb-2 fst-italic">{{ __('O que nos falta?') }}</p>
                                                            <ul class="small mb-0 ps-3 text-dark">
                                                                <li>Orçamento limitado</li>
                                                                <li>Alta rotatividade de pessoal</li>
                                                                <li>Sistemas desatualizados</li>
                                                                <li>Comunicação interna deficiente</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Linha Externa --}}
                                        <div class="col-12">
                                            <div class="row g-0">
                                                <div class="col-1 bg-info bg-opacity-10 d-flex align-items-center justify-content-center border-end p-2">
                                                    <div class="text-center">
                                                        <i class="bi bi-globe d-block fs-5 text-white mb-1"></i>
                                                        <small class="fw-bold text-info" style="writing-mode: vertical-rl; transform: rotate(180deg);">EXTERNO</small>
                                                    </div>
                                                </div>
                                                <div class="col-11">
                                                    <div class="row g-0">
                                                        <div class="col-md-6 p-3 border-end bg-primary bg-opacity-5">
                                                            <h6 class="fw-bold  text-white mb-2">
                                                                <i class="bi bi-star-fill me-1 text-white"></i>{{ __('OPORTUNIDADES') }}
                                                            </h6>
                                                            <p class="small  text-white mb-2 fst-italic">{{ __('O que o ambiente oferece?') }}</p>
                                                            <ul class="small mb-0 ps-3  text-white">
                                                                <li>Novos editais de financiamento</li>
                                                                <li>Parcerias com universidades</li>
                                                                <li>Demanda crescente por serviços</li>
                                                                <li>Novas tecnologias disponíveis</li>
                                                            </ul>
                                                        </div>
                                                        <div class="col-md-6 p-3 bg-danger bg-opacity-5">
                                                            <h6 class="fw-bold  text-white mb-2">
                                                                <i class="bi bi-shield-x me-1 text-white"></i>{{ __('AMEAÇAS') }}
                                                            </h6>
                                                            <p class="small  text-white mb-2 fst-italic">{{ __('O que pode nos prejudicar?') }}</p>
                                                            <ul class="small mb-0 ps-3  text-white">
                                                                <li>Cortes orçamentários governamentais</li>
                                                                <li>Concorrência de setor privado</li>
                                                                <li>Mudanças na legislação</li>
                                                                <li>Crise econômica nacional</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Cards Detalhados dos Conceitos --}}
                        <div class="col-md-6">
                            <div class="card h-100 border border-success border-opacity-25 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="icon-circle bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-shield-fill-check fs-5"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-dark">{{ __('Forças (Strengths)') }}</h6>
                                    </div>
                                    <p class="small text-dark mb-3">
                                        <strong>{{ __('Definição:') }}</strong> {{ __('Fatores internos positivos que dão vantagem competitiva à organização.') }}
                                    </p>
                                    <div class="bg-light p-3 rounded-3 border mb-3">
                                        <p class="small mb-2 fw-semibold text-dark">{{ __('Perguntas-chave:') }}</p>
                                        <ul class="small mb-0 ps-3 text-dark">
                                            <li>{{ __('O que fazemos melhor que os outros?') }}</li>
                                            <li>{{ __('Quais recursos únicos possuímos?') }}</li>
                                            <li>{{ __('Quais processos são excelentes?') }}</li>
                                        </ul>
                                    </div>
                                    <div class="alert alert-success alert-sm py-2 px-3 mb-0">
                                        <p class="small mb-1 fw-semibold text-dark">{{ __('Exemplo prático:') }}</p>
                                        <p class="small mb-0 fst-italic text-dark">
                                            {{ __('"Equipe técnica com 15 anos de experiência média e certificações internacionais"') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100 border border-warning border-opacity-25 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="icon-circle bg-warning bg-opacity-10 text-warning">
                                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-dark">{{ __('Fraquezas (Weaknesses)') }}</h6>
                                    </div>
                                    <p class="small text-dark mb-3">
                                        <strong>{{ __('Definição:') }}</strong> {{ __('Fatores internos negativos que limitam o desempenho da organização.') }}
                                    </p>
                                    <div class="bg-light p-3 rounded-3 border mb-3">
                                        <p class="small mb-2 fw-semibold text-dark">{{ __('Perguntas-chave:') }}</p>
                                        <ul class="small mb-0 ps-3 text-dark">
                                            <li>{{ __('Onde falhamos com frequência?') }}</li>
                                            <li>{{ __('Quais recursos nos faltam?') }}</li>
                                            <li>{{ __('O que os outros fazem melhor?') }}</li>
                                        </ul>
                                    </div>
                                    <div class="alert alert-warning alert-sm py-2 px-3 mb-0">
                                        <p class="small mb-1 fw-semibold text-dark">{{ __('Exemplo prático:') }}</p>
                                        <p class="small mb-0 fst-italic text-dark">
                                            {{ __('"Sistemas de TI defasados (10+ anos) causando retrabalho e lentidão nos processos"') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100 border border-primary border-opacity-25 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-star-fill fs-5"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-dark">{{ __('Oportunidades (Opportunities)') }}</h6>
                                    </div>
                                    <p class="small text-dark mb-3">
                                        <strong>{{ __('Definição:') }}</strong> {{ __('Fatores externos positivos que podem ser aproveitados pela organização.') }}
                                    </p>
                                    <div class="bg-light p-3 rounded-3 border mb-3">
                                        <p class="small mb-2 fw-semibold text-dark">{{ __('Perguntas-chave:') }}</p>
                                        <ul class="small mb-0 ps-3 text-dark">
                                            <li>{{ __('Que mudanças no mercado nos favorecem?') }}</li>
                                            <li>{{ __('Quais tecnologias emergentes podemos usar?') }}</li>
                                            <li>{{ __('Há novas fontes de financiamento?') }}</li>
                                        </ul>
                                    </div>
                                    <div class="alert alert-primary alert-sm py-2 px-3 mb-0">
                                        <p class="small mb-1 fw-semibold text-dark">{{ __('Exemplo prático:') }}</p>
                                        <p class="small mb-0 fst-italic text-dark">
                                            {{ __('"Governo lançou edital de R$ 50M para digitalização de serviços públicos"') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100 border border-danger border-opacity-25 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="icon-circle bg-danger bg-opacity-10 text-danger">
                                            <i class="bi bi-shield-x fs-5"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-dark">{{ __('Ameaças (Threats)') }}</h6>
                                    </div>
                                    <p class="small text-dark mb-3">
                                        <strong>{{ __('Definição:') }}</strong> {{ __('Fatores externos negativos que podem prejudicar a organização.') }}
                                    </p>
                                    <div class="bg-light p-3 rounded-3 border mb-3">
                                        <p class="small mb-2 fw-semibold text-dark">{{ __('Perguntas-chave:') }}</p>
                                        <ul class="small mb-0 ps-3 text-dark">
                                            <li>{{ __('Que mudanças legais nos afetam?') }}</li>
                                            <li>{{ __('Há concorrentes emergindo?') }}</li>
                                            <li>{{ __('O cenário econômico é desfavorável?') }}</li>
                                        </ul>
                                    </div>
                                    <div class="alert alert-danger alert-sm py-2 px-3 mb-0">
                                        <p class="small mb-1 fw-semibold text-dark">{{ __('Exemplo prático:') }}</p>
                                        <p class="small mb-0 fst-italic text-dark">
                                            {{ __('"Nova lei exige adequação de sistemas até 2026, sob pena de multa de R$ 500K"') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Estratégias de Uso --}}
                        <div class="col-12">
                            <div class="card border-2 border-info">
                                <div class="card-header bg-info bg-opacity-10">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="bi bi-lightbulb-fill me-2 text-info"></i>{{ __('Como usar a Matriz SWOT estrategicamente?') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="d-flex gap-2 align-items-start">
                                                <div class="icon-circle-mini bg-success bg-opacity-10 text-success flex-shrink-0">
                                                    <i class="bi bi-trophy-fill"></i>
                                                </div>
                                                <div>
                                                    <h6 class="small fw-bold mb-1 text-dark">{{ __('Estratégia FO (Forças + Oportunidades)') }}</h6>
                                                    <p class="small text-dark mb-0">{{ __('Use suas forças para aproveitar as oportunidades. Ex: "Equipe qualificada" + "Novo edital" = Candidatar-se ao edital') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex gap-2 align-items-start">
                                                <div class="icon-circle-mini bg-warning bg-opacity-10 text-warning flex-shrink-0">
                                                    <i class="bi bi-tools"></i>
                                                </div>
                                                <div>
                                                    <h6 class="small fw-bold mb-1 text-dark">{{ __('Estratégia FrO (Fraquezas + Oportunidades)') }}</h6>
                                                    <p class="small text-dark mb-0">{{ __('Use oportunidades para corrigir fraquezas. Ex: "Sistemas antigos" + "Edital de TI" = Modernizar infraestrutura') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex gap-2 align-items-start">
                                                <div class="icon-circle-mini bg-primary bg-opacity-10 text-primary flex-shrink-0">
                                                    <i class="bi bi-shield-check"></i>
                                                </div>
                                                <div>
                                                    <h6 class="small fw-bold mb-1 text-dark">{{ __('Estratégia FA (Forças + Ameaças)') }}</h6>
                                                    <p class="small text-dark mb-0">{{ __('Use forças para defender-se de ameaças. Ex: "Boa reputação" mitiga "Concorrência crescente"') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex gap-2 align-items-start">
                                                <div class="icon-circle-mini bg-danger bg-opacity-10 text-danger flex-shrink-0">
                                                    <i class="bi bi-exclamation-octagon-fill"></i>
                                                </div>
                                                <div>
                                                    <h6 class="small fw-bold mb-1 text-dark">{{ __('Estratégia FrA (Fraquezas + Ameaças)') }}</h6>
                                                    <p class="small text-dark mb-0">{{ __('Minimize riscos. Ex: "Orçamento baixo" + "Cortes" = Zona de risco máximo - prioridade de ação!') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Dica Final --}}
                        <div class="col-12">
                            <div class="alert alert-secondary border-0 d-flex align-items-start gap-3 mb-0">
                                <div class="icon-circle-mini bg-secondary bg-opacity-10 text-secondary flex-shrink-0">
                                    <i class="bi bi-stars"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-2 text-dark">{{ __('Dicas Profissionais') }}</h6>
                                    <ul class="small mb-0 ps-3 text-dark">
                                        <li>{{ __('Seja específico e mensurável. Evite generalidades como "equipe boa" - prefira "equipe com 15 anos de experiência média".') }}</li>
                                        <li>{{ __('Envolva diferentes áreas na construção da matriz para ter visões complementares.') }}</li>
                                        <li>{{ __('Revise a SWOT periodicamente (semestral ou anual) - o ambiente muda constantemente.') }}</li>
                                        <li>{{ __('Use o Mentor de IA para obter sugestões personalizadas baseadas na sua organização!') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($aiSuggestion)
            <div class="card border-0 shadow-sm mb-4 animate-fade-in" style="background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-robot me-2"></i>Sugestões do Mentor IA</h6>
                    <button type="button" class="btn-close" style="font-size: 0.7rem;" wire:click="$set('aiSuggestion', '')"></button>
                </div>
                <div class="card-body">
                    @if(is_array($aiSuggestion))
                        <div class="row g-3">
                            @foreach(['Força' => 'forcas', 'Fraqueza' => 'fraquezas', 'Oportunidade' => 'oportunidades', 'Ameaça' => 'ameacas'] as $label => $key)
                                @if(isset($aiSuggestion[$key]) && count($aiSuggestion[$key]) > 0)
                                    <div class="col-md-3">
                                        <div class="small fw-bold text-muted text-uppercase mb-2">{{ $label }}s</div>
                                        <div class="list-group list-group-flush border rounded">
                                            @foreach($aiSuggestion[$key] as $item)
                                                <button type="button" wire:click="adicionarSugerido('{{ $label }}', '{{ $item }}')" class="list-group-item list-group-item-action py-2 px-2 small d-flex justify-content-between align-items-center">
                                                    <span class="text-truncate me-2">{{ $item }}</span>
                                                    <i class="bi bi-plus-circle text-primary"></i>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <span class="spinner-border spinner-border-sm text-primary me-2"></span>
                            <span class="text-muted">Inspirando novas ideias...</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        
        @if($modoVisualizacao)
            <!-- Matriz SWOT 2x2 - Modo Apresentação -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="row g-0">
                        <!-- Cabeçalhos -->
                        <div class="col-6 text-center pb-3 border-end">
                            <h5 class="fw-bold text-success mb-0">POSITIVOS</h5>
                            <small class="text-muted">Ajudam a atingir objetivos</small>
                        </div>
                        <div class="col-6 text-center pb-3">
                            <h5 class="fw-bold text-danger mb-0">NEGATIVOS</h5>
                            <small class="text-muted">Atrapalham a atingir objetivos</small>
                        </div>
                        
                        <div class="col-12"><hr class="my-0"></div>

                        <!-- Linha 1: Interno -->
                        <div class="col-12 py-2 bg-light text-center border-bottom">
                            <span class="badge bg-secondary">AMBIENTE INTERNO</span>
                        </div>

                        <!-- S - Forças -->
                        <div class="col-md-6 border-end border-bottom p-3 bg-success-subtle bg-opacity-10">
                            <h6 class="fw-bold text-success mb-3"><i class="bi bi-plus-circle me-1"></i> FORÇAS (Strengths)</h6>
                            <ul class="list-unstyled mb-0">
                                @forelse($forcas as $item)
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="bi bi-check-circle-fill text-success me-2 mt-1 small"></i>
                                        <span>{{ $item['dsc_item'] }}</span>
                                    </li>
                                @empty
                                    <li class="text-muted fst-italic small">Nenhuma força registrada.</li>
                                @endforelse
                            </ul>
                        </div>

                        <!-- W - Fraquezas -->
                        <div class="col-md-6 border-bottom p-3 bg-danger-subtle bg-opacity-10">
                            <h6 class="fw-bold text-danger mb-3"><i class="bi bi-dash-circle me-1"></i> FRAQUEZAS (Weaknesses)</h6>
                            <ul class="list-unstyled mb-0">
                                @forelse($fraquezas as $item)
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="bi bi-x-circle-fill text-danger me-2 mt-1 small"></i>
                                        <span>{{ $item['dsc_item'] }}</span>
                                    </li>
                                @empty
                                    <li class="text-muted fst-italic small">Nenhuma fraqueza registrada.</li>
                                @endforelse
                            </ul>
                        </div>

                        <!-- Linha 2: Externo -->
                        <div class="col-12 py-2 bg-light text-center border-bottom">
                            <span class="badge bg-secondary">AMBIENTE EXTERNO</span>
                        </div>

                        <!-- O - Oportunidades -->
                        <div class="col-md-6 border-end p-3 bg-primary-subtle bg-opacity-10">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-arrow-up-circle me-1"></i> OPORTUNIDADES (Opportunities)</h6>
                            <ul class="list-unstyled mb-0">
                                @forelse($oportunidades as $item)
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="bi bi-lightbulb-fill text-primary me-2 mt-1 small"></i>
                                        <span>{{ $item['dsc_item'] }}</span>
                                    </li>
                                @empty
                                    <li class="text-muted fst-italic small">Nenhuma oportunidade registrada.</li>
                                @endforelse
                            </ul>
                        </div>

                        <!-- T - Ameaças -->
                        <div class="col-md-6 p-3 bg-warning-subtle bg-opacity-10">
                            <h6 class="fw-bold text-warning-emphasis mb-3"><i class="bi bi-exclamation-triangle-fill me-1"></i> AMEAÇAS (Threats)</h6>
                            <ul class="list-unstyled mb-0">
                                @forelse($ameacas as $item)
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="bi bi-shield-exclamation text-warning-emphasis me-2 mt-1 small"></i>
                                        <span>{{ $item['dsc_item'] }}</span>
                                    </li>
                                @empty
                                    <li class="text-muted fst-italic small">Nenhuma ameaça registrada.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Matriz SWOT 2x2 - Modo Edição -->
            <div class="row g-3">
                <!-- Linha 1: Ambiente Interno -->
                <div class="col-12">
                    <div class="text-center mb-2">
                        <span class="badge bg-secondary px-3 py-2">
                            <i class="bi bi-building me-1"></i> AMBIENTE INTERNO
                        </span>
                    </div>
                </div>

                <!-- Forças (S - Strengths) -->
                <div class="col-md-6">
                    <div class="card border-success h-100">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-plus-circle me-2"></i>
                                <strong>FORÇAS</strong> (Strengths)
                            </span>
                            <button type="button" class="btn btn-sm btn-light" wire:click="create('Força')">
                                <i class="bi bi-plus-lg"></i> Adicionar
                            </button>
                        </div>
                        <div class="card-body p-2">
                            <p class="text-muted small mb-2">Pontos fortes internos que favorecem a organização</p>
                            @forelse($forcas as $item)
                                <div class="card mb-2 border-success-subtle">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <p class="mb-1">{{ $item['dsc_item'] }}</p>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-success-subtle text-success">
                                                        Impacto: {{ $item['num_impacto'] }}/5
                                                    </span>
                                                    @if($item['txt_observacao'])
                                                        <span class="text-muted small" title="{{ $item['txt_observacao'] }}">
                                                            <i class="bi bi-chat-text"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary btn-sm" wire:click="edit('{{ $item['cod_analise'] }}')" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm" wire:click="delete('{{ $item['cod_analise'] }}')" wire:confirm="Tem certeza que deseja excluir este item?" title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    <small>Nenhuma força cadastrada</small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Fraquezas (W - Weaknesses) -->
                <div class="col-md-6">
                    <div class="card border-danger h-100">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-dash-circle me-2"></i>
                                <strong>FRAQUEZAS</strong> (Weaknesses)
                            </span>
                            <button type="button" class="btn btn-sm btn-light" wire:click="create('Fraqueza')">
                                <i class="bi bi-plus-lg"></i> Adicionar
                            </button>
                        </div>
                        <div class="card-body p-2">
                            <p class="text-muted small mb-2">Pontos fracos internos que prejudicam a organização</p>
                            @forelse($fraquezas as $item)
                                <div class="card mb-2 border-danger-subtle">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <p class="mb-1">{{ $item['dsc_item'] }}</p>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-danger-subtle text-danger">
                                                        Impacto: {{ $item['num_impacto'] }}/5
                                                    </span>
                                                    @if($item['txt_observacao'])
                                                        <span class="text-muted small" title="{{ $item['txt_observacao'] }}">
                                                            <i class="bi bi-chat-text"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary btn-sm" wire:click="edit('{{ $item['cod_analise'] }}')" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm" wire:click="delete('{{ $item['cod_analise'] }}')" wire:confirm="Tem certeza que deseja excluir este item?" title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    <small>Nenhuma fraqueza cadastrada</small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Divisor -->
                <div class="col-12">
                    <hr class="my-2">
                    <div class="text-center mb-2">
                        <span class="badge bg-secondary px-3 py-2">
                            <i class="bi bi-globe me-1"></i> AMBIENTE EXTERNO
                        </span>
                    </div>
                </div>

                <!-- Oportunidades (O - Opportunities) -->
                <div class="col-md-6">
                    <div class="card border-primary h-100">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-arrow-up-circle me-2"></i>
                                <strong>OPORTUNIDADES</strong> (Opportunities)
                            </span>
                            <button type="button" class="btn btn-sm btn-light" wire:click="create('Oportunidade')">
                                <i class="bi bi-plus-lg"></i> Adicionar
                            </button>
                        </div>
                        <div class="card-body p-2">
                            <p class="text-muted small mb-2">Fatores externos favoráveis à organização</p>
                            @forelse($oportunidades as $item)
                                <div class="card mb-2 border-primary-subtle">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <p class="mb-1">{{ $item['dsc_item'] }}</p>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-primary-subtle text-primary">
                                                        Impacto: {{ $item['num_impacto'] }}/5
                                                    </span>
                                                    @if($item['txt_observacao'])
                                                        <span class="text-muted small" title="{{ $item['txt_observacao'] }}">
                                                            <i class="bi bi-chat-text"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary btn-sm" wire:click="edit('{{ $item['cod_analise'] }}')" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm" wire:click="delete('{{ $item['cod_analise'] }}')" wire:confirm="Tem certeza que deseja excluir este item?" title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    <small>Nenhuma oportunidade cadastrada</small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Ameaças (T - Threats) -->
                <div class="col-md-6">
                    <div class="card border-warning h-100">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>AMEAÇAS</strong> (Threats)
                            </span>
                            <button type="button" class="btn btn-sm btn-dark" wire:click="create('Ameaça')">
                                <i class="bi bi-plus-lg"></i> Adicionar
                            </button>
                        </div>
                        <div class="card-body p-2">
                            <p class="text-muted small mb-2">Fatores externos desfavoráveis à organização</p>
                            @forelse($ameacas as $item)
                                <div class="card mb-2 border-warning-subtle">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <p class="mb-1">{{ $item['dsc_item'] }}</p>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-warning-subtle text-warning-emphasis">
                                                        Impacto: {{ $item['num_impacto'] }}/5
                                                    </span>
                                                    @if($item['txt_observacao'])
                                                        <span class="text-muted small" title="{{ $item['txt_observacao'] }}">
                                                            <i class="bi bi-chat-text"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary btn-sm" wire:click="edit('{{ $item['cod_analise'] }}')" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm" wire:click="delete('{{ $item['cod_analise'] }}')" wire:confirm="Tem certeza que deseja excluir este item?" title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    <small>Nenhuma ameaça cadastrada</small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Criação/Edição -->
            @if($showModal)
            <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-{{ $itemId ? 'pencil' : 'plus-circle' }} me-2"></i>
                                {{ $itemId ? 'Editar' : 'Adicionar' }} {{ $dsc_categoria }}
                            </h5>
                            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                        </div>
                        <form wire:submit="save">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="dsc_item" class="form-label">Descrição <span class="text-danger">*</span></label>
                                    <textarea wire:model="dsc_item" id="dsc_item" class="form-control @error('dsc_item') is-invalid @enderror" rows="3" placeholder="Descreva o item..." required></textarea>
                                    @error('dsc_item')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="num_impacto" class="form-label">Nível de Impacto <span class="text-danger">*</span></label>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="range" wire:model.live="num_impacto" id="num_impacto" class="form-range flex-grow-1" min="1" max="5" step="1">
                                        <span class="badge bg-secondary fs-6" style="min-width: 40px;">{{ $num_impacto }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted small mt-1">
                                        <span>1 - Muito Baixo</span>
                                        <span>5 - Muito Alto</span>
                                    </div>
                                    @error('num_impacto')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="txt_observacao" class="form-label">Observações</label>
                                    <textarea wire:model="txt_observacao" id="txt_observacao" class="form-control @error('txt_observacao') is-invalid @enderror" rows="2" placeholder="Observações adicionais (opcional)"></textarea>
                                    @error('txt_observacao')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">
                                    <i class="bi bi-x-lg me-1"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> {{ $itemId ? 'Atualizar' : 'Salvar' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        @endif
    @endif
</div>