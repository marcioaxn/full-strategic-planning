<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0 fs-5 fw-semibold"><i class="bi bi-shield-lock me-2"></i>Troca de Senha Obrigatória</h4>
                </div>
                <div class="card-body p-4 bg-white">
                    <div class="alert alert-warning mb-4 d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                        <div>
                            Por motivos de segurança, você precisa alterar sua senha antes de continuar acessando o sistema.
                        </div>
                    </div>

                    <form wire:submit="trocarSenha">
                        <div class="mb-3">
                            <label for="senhaAtual" class="form-label fw-medium">Senha Atual</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                                <input type="password" 
                                       class="form-control @error('senhaAtual') is-invalid @enderror" 
                                       id="senhaAtual" 
                                       wire:model="senhaAtual"
                                       placeholder="Digite sua senha atual"
                                       autofocus>
                                @error('senhaAtual')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="novaSenha" class="form-label fw-medium">Nova Senha</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                <input type="password" 
                                       class="form-control @error('novaSenha') is-invalid @enderror" 
                                       id="novaSenha" 
                                       wire:model="novaSenha"
                                       placeholder="Digite sua nova senha">
                                @error('novaSenha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text text-muted small"><i class="bi bi-info-circle me-1"></i>Mínimo de 8 caracteres.</div>
                        </div>

                        <div class="mb-4">
                            <label for="novaSenha_confirmation" class="form-label fw-medium">Confirmar Nova Senha</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-check-lg"></i></span>
                                <input type="password" 
                                       class="form-control" 
                                       id="novaSenha_confirmation" 
                                       wire:model="novaSenha_confirmation"
                                       placeholder="Confirme sua nova senha">
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2 fw-semibold shadow-sm">
                                <span wire:loading.remove>Alterar Senha e Continuar</span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Processando...
                                </span>
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center border-top pt-3">
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <button type="submit" class="btn btn-link text-decoration-none text-muted btn-sm">
                                <i class="bi bi-box-arrow-right me-1"></i> Sair e trocar depois
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
