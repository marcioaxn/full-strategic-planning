<div>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            <i class="bi bi-gear-fill me-2"></i>{{ __('Configurações do Sistema') }}
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            @if (session()->has('status'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Cartão de Inteligência Artificial -->
            <div class="card card-modern border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                            <i class="bi bi-robot fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">{{ __('Inteligência Artificial (IA)') }}</h5>
                            <p class="text-muted small mb-0">{{ __('Configure o assistente virtual do sistema SEAE.') }}</p>
                        </div>
                        <div class="ms-auto form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="aiEnabledSwitch" wire:model.live="aiEnabled" style="width: 3em; height: 1.5em;">
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <div class="@if(!$aiEnabled) opacity-50 pe-none @endif" style="transition: opacity 0.3s ease;">
                        
                        <div class="alert alert-info border-0 bg-info-subtle d-flex align-items-center gap-3 mb-4">
                            <i class="bi bi-lightbulb-fill fs-4 text-info"></i>
                            <div class="small">
                                <strong>Por que ativar?</strong>
                                <p class="mb-0">A IA ajuda os gestores sugerindo Objetivos, Indicadores e Identidade Estratégica com base no contexto da organização, economizando tempo e aumentando a qualidade do planejamento.</p>
                            </div>
                        </div>

                        <div class="row g-4">
                            <!-- Provedor -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">{{ __('Provedor de IA') }}</label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="card h-100 border-2 cursor-pointer {{ $aiProvider === 'gemini' ? 'border-primary bg-primary bg-opacity-10' : 'border-light' }}" style="cursor: pointer;">
                                            <div class="card-body text-center p-3">
                                                <input type="radio" wire:model.live="aiProvider" value="gemini" class="d-none">
                                                <i class="bi bi-google fs-2 mb-2 {{ $aiProvider === 'gemini' ? 'text-primary' : 'text-muted' }}"></i>
                                                <div class="fw-bold {{ $aiProvider === 'gemini' ? 'text-primary' : 'text-dark' }}">Google Gemini</div>
                                                <div class="small text-muted">Disponível</div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="card h-100 border-2 cursor-pointer {{ $aiProvider === 'openai' ? 'border-primary bg-primary bg-opacity-10' : 'border-light' }}" style="cursor: pointer;">
                                            <div class="card-body text-center p-3">
                                                <input type="radio" wire:model.live="aiProvider" value="openai" class="d-none">
                                                <i class="bi bi-cpu fs-2 mb-2 {{ $aiProvider === 'openai' ? 'text-primary' : 'text-muted' }}"></i>
                                                <div class="fw-bold {{ $aiProvider === 'openai' ? 'text-primary' : 'text-dark' }}">OpenAI (GPT-4)</div>
                                                <div class="small text-muted text-success">Disponível</div>
                                            </div>
                                        </label>
                                    </div>
                                    @error('aiProvider') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- API Key -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">{{ __('Chave da API (API Key)') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-key"></i></span>
                                    <input type="{{ $showKey ? 'text' : 'password' }}" 
                                           wire:model="aiApiKey" 
                                           class="form-control @error('aiApiKey') is-invalid @enderror" 
                                           placeholder="Cole sua chave de API aqui"
                                           {{ !$aiEnabled ? 'disabled' : '' }}>
                                    <button class="btn btn-outline-secondary" type="button" wire:click="$toggle('showKey')">
                                        <i class="bi bi-eye{{ $showKey ? '-slash' : '' }}"></i>
                                    </button>
                                    <button class="btn btn-dark px-3" type="button" wire:click="testConnection" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="testConnection">
                                            {{ __('Testar') }}
                                        </span>
                                        <span wire:loading wire:target="testConnection">
                                            <span class="spinner-border spinner-border-sm"></span>
                                        </span>
                                    </button>
                                </div>
                                @error('aiApiKey') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                
                                {{-- Resultado do Teste --}}
                                @if($connectionStatus)
                                    <div class="mt-2 animate-fade-in">
                                        @if($connectionStatus === 'success')
                                            <div class="alert alert-success py-2 small d-flex align-items-center gap-2">
                                                <i class="bi bi-check-circle-fill"></i> {{ $connectionMessage }}
                                            </div>
                                        @elseif($connectionStatus === 'error')
                                            <div class="alert alert-danger py-2 small d-flex align-items-center gap-2">
                                                <i class="bi bi-x-circle-fill"></i> {{ $connectionMessage }}
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <div class="form-text mt-2">
                                    @if($aiProvider === 'gemini')
                                        Obtenha sua chave gratuitamente no <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-decoration-none fw-bold">Google AI Studio <i class="bi bi-box-arrow-up-right ms-1"></i></a>.
                                    @endif
                                    A chave é armazenada de forma criptografada no banco de dados.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light border-top p-4 d-flex justify-content-end">
                    <button wire:click="saveAiSettings" class="btn btn-primary gradient-theme-btn px-4 py-2 fw-bold">
                        <span wire:loading.remove wire:target="saveAiSettings">
                            <i class="bi bi-check-lg me-2"></i>{{ __('Salvar Configurações') }}
                        </span>
                        <span wire:loading wire:target="saveAiSettings">
                            <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Salvando...') }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Outras configurações futuras podem vir aqui -->

        </div>
    </div>
</div>
