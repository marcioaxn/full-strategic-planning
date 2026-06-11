<div class="container-fluid py-4 animate-fade-in">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb breadcrumb-dots">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" wire:navigate class="text-decoration-none text-muted">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item text-muted">{{ __('Administração') }}</li>
            <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">{{ __('Configuração Agente IA') }}</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-xxl-9">
            <div class="card card-modern border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                <div class="card-header border-0 p-4 bg-primary text-white d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                            <i class="bi bi-robot fs-3 text-white"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold">{{ __('Cérebro de Inteligência Artificial') }}</h4>
                            <p class="mb-0 small text-white-50 opacity-75">{{ __('Gerencie a integração com o Google Gemini para análises estratégicas do PEI.') }}</p>
                        </div>
                    </div>
                    <div>
                        <span class="badge {{ $connectionStatus === 'success' ? 'bg-success' : 'bg-warning' }} rounded-pill px-3 py-2">
                            {{ $connectionStatus === 'success' ? __('Status: Operacional') : __('Status: Pendente') }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-5">

                    @if (session()->has('status'))
                        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row g-5">
                        <div class="col-12 col-lg-5 border-end d-none d-lg-block">
                            <h5 class="fw-bold mb-4">{{ __('O que o Agente faz?') }}</h5>
                            <div class="d-flex flex-column gap-4">
                                <div class="d-flex gap-3">
                                    <i class="bi bi-graph-up-arrow text-primary fs-4"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ __('Análise de Tendências') }}</h6>
                                        <p class="text-muted small mb-0">{{ __('Processa o histórico de indicadores para prever desvios de metas estratégicas.') }}</p>
                                    </div>
                                </div>
                                <div class="d-flex gap-3">
                                    <i class="bi bi-shield-exclamation text-danger fs-4"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ __('Detecção de Riscos') }}</h6>
                                        <p class="text-muted small mb-0">{{ __('Identifica automaticamente padrões em objetivos e entregas com baixo desempenho.') }}</p>
                                    </div>
                                </div>
                                <div class="d-flex gap-3">
                                    <i class="bi bi-lightning-charge text-warning fs-4"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ __('Sugestões de Ação') }}</h6>
                                        <p class="text-muted small mb-0">{{ __('Entrega recomendações técnicas alinhadas à metodologia do Guia PEI/MGI 2025.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-7">
                            <form wire:submit.prevent="save">
                                <div class="mb-5">
                                    <h5 class="fw-bold mb-4">{{ __('Configurações do Agente') }}</h5>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-dark">{{ __('Provedor de IA') }}</label>
                                        <div class="row g-3">
                                            <div class="col-12 col-md-6">
                                                <label class="w-100 cursor-pointer" wire:click="$set('aiProvider', 'gemini-studio')">
                                                    <input type="radio" wire:model="aiProvider" value="gemini-studio" class="btn-check">
                                                    <div class="card h-100 border-2 transition-all {{ $aiProvider === 'gemini-studio' ? 'border-primary bg-primary-subtle' : 'border-light bg-light opacity-75' }} rounded-4 p-3 shadow-sm">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <i class="bi bi-google text-primary fs-5"></i>
                                                            <h6 class="fw-bold mb-0">Google AI Studio</h6>
                                                        </div>
                                                        <p class="mt-2 mb-0 x-small text-muted">{{ __('Rápido para prototipagem. Requer API Key.') }}</p>
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="w-100 cursor-pointer" wire:click="$set('aiProvider', 'vertex-ai')">
                                                    <input type="radio" wire:model="aiProvider" value="vertex-ai" class="btn-check">
                                                    <div class="card h-100 border-2 transition-all {{ $aiProvider === 'vertex-ai' ? 'border-primary bg-primary-subtle' : 'border-light bg-light opacity-75' }} rounded-4 p-3 shadow-sm">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <i class="bi bi-shield-lock-fill text-success fs-5"></i>
                                                            <h6 class="fw-bold mb-0">Google Vertex AI</h6>
                                                        </div>
                                                        <p class="mt-2 mb-0 x-small text-muted">{{ __('Enterprise. Dados não usados para treino. Requer Service Account.') }}</p>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark">{{ __('Modelo Gemini') }}</label>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="w-100 cursor-pointer" wire:click="$set('aiModel', 'gemini-2.5-flash')">
                                                <input type="radio" wire:model="aiModel" value="gemini-2.5-flash" class="btn-check">
                                                <div class="card h-100 border-2 transition-all {{ $aiModel === 'gemini-2.5-flash' ? 'border-primary bg-primary-subtle' : 'border-light bg-light opacity-75' }} rounded-4 p-3 shadow-sm">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="bg-white rounded-3 p-2 shadow-sm border">
                                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                                                <path d="M12 22C12 22 12 17 17 12C22 7 22 7 22 7C22 7 17 7 12 2C7 7 7 7 7 7C7 7 2 7 7 12C12 17 12 22 12 22Z" fill="#4285F4"/>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <h6 class="fw-bold mb-0">Gemini 2.5 Flash</h6>
                                                            <span class="x-small text-success fw-bold">{{ __('Rápido & Inteligente') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="w-100 cursor-pointer" wire:click="$set('aiModel', 'gemini-2.5-pro')">
                                                <input type="radio" wire:model="aiModel" value="gemini-2.5-pro" class="btn-check">
                                                <div class="card h-100 border-2 transition-all {{ $aiModel === 'gemini-2.5-pro' ? 'border-primary bg-primary-subtle' : 'border-light bg-light opacity-75' }} rounded-4 p-3 shadow-sm">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="bg-white rounded-3 p-2 shadow-sm border">
                                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                                                <path d="M12 22C12 22 12 17 17 12C22 7 22 7 22 7C22 7 17 7 12 2C7 7 7 7 7 7C7 7 2 7 7 12C12 17 12 22 12 22Z" fill="#9B72CB"/>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <h6 class="fw-bold mb-0">Gemini 2.5 Pro</h6>
                                                            <span class="x-small text-primary fw-bold">{{ __('Raciocínio Avançado') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    @if($aiProvider === 'gemini-studio')
                                        <div class="form-group mb-4 animate-fade-in">
                                            <label for="aiApiKey" class="form-label fw-bold text-dark mb-2">{{ __('Chave de API do Google AI') }}</label>
                                            <div class="input-group input-group-lg shadow-sm">
                                                <span class="input-group-text bg-white border-end-0">
                                                    <i class="bi bi-key-fill text-primary"></i>
                                                </span>
                                                <input type="password" id="aiApiKey" wire:model.defer="aiApiKey"
                                                       class="form-control border-start-0 ps-0"
                                                       placeholder="sk-...">
                                            </div>
                                            <div class="mt-2 x-small text-primary">
                                                <i class="bi bi-info-circle me-1"></i>{{ __('Obtenha em aistudio.google.com') }}
                                            </div>
                                        </div>
                                    @else
                                        <div class="row g-3 mb-4 animate-fade-in">
                                            <div class="col-12 col-md-8">
                                                <label class="form-label fw-bold">{{ __('GCP Project ID') }}</label>
                                                <input type="text" wire:model.defer="vertexProjectId" class="form-control" placeholder="ex: meu-projeto-gcp">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-bold">{{ __('Location') }}</label>
                                                <input type="text" wire:model.defer="vertexLocation" class="form-control" placeholder="us-central1">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-bold">{{ __('Service Account JSON Key') }}</label>
                                                <textarea wire:model.defer="vertexServiceAccountJson" class="form-control font-monospace x-small" rows="5" placeholder='{ "type": "service_account", ... }'></textarea>
                                                <div class="mt-2 x-small text-success">
                                                    <i class="bi bi-shield-check me-1"></i>{{ __('Seus dados de credenciais são armazenados com criptografia em repouso.') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="d-flex flex-column gap-3 mt-5">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-check-circle"></i>
                                        {{ __('Salvar e Ativar Agente') }}
                                    </button>

                                    <button type="button" wire:click="testConnection" class="btn btn-link text-decoration-none fw-bold" wire:loading.attr="disabled">
                                        <span wire:loading.remove><i class="bi bi-lightning-charge me-1"></i>{{ __('Testar Comunicação agora') }}</span>
                                        <span wire:loading><i class="spinner-border spinner-border-sm me-1"></i>{{ __('Verificando...') }}</span>
                                    </button>
                                </div>
                            </form>

                            @if($connectionStatus)
                                <div class="mt-4 animate-fade-in alert {{ $connectionStatus === 'success' ? 'alert-success' : ($connectionStatus === 'testing' ? 'alert-info' : 'alert-danger') }} border-0 rounded-4 shadow-sm p-4 d-flex align-items-center gap-3">
                                    <div class="flex-shrink-0">
                                        @if($connectionStatus === 'testing')
                                            <div class="spinner-border text-primary" role="status"></div>
                                        @elseif($connectionStatus === 'success')
                                            <div class="bg-success text-white rounded-circle p-2"><i class="bi bi-check-lg fs-4"></i></div>
                                        @else
                                            <div class="bg-danger text-white rounded-circle p-2"><i class="bi bi-exclamation-triangle fs-4"></i></div>
                                        @endif
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $connectionStatus === 'success' ? __('Sucesso!') : ($connectionStatus === 'testing' ? __('Aguarde...') : __('Erro de Conexão')) }}</h6>
                                        <p class="mb-0 small">{{ $connectionMessage }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .animate-fade-in { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .breadcrumb-dots .breadcrumb-item + .breadcrumb-item::before { content: "·"; font-size: 1.5rem; line-height: 1; vertical-align: middle; }
        .x-small { font-size: 0.75rem; }
        .cursor-pointer { cursor: pointer; }
    </style>
</div>
