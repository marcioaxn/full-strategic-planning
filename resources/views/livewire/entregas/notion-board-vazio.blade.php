<div class="container-fluid py-5">
    <div class="card card-modern border-dashed">
        <div class="card-body p-5 text-center">
            <div class="empty-state">
                <div class="empty-state-icon mb-3">
                    <i class="bi bi-list-check fs-1 text-muted"></i>
                </div>
                <h5 class="empty-state-title">{{ __('Nenhum Plano de Ação Selecionado') }}</h5>
                <p class="empty-state-text text-muted">
                    {{ __('Para gerenciar as entregas, você deve primeiro selecionar um Plano de Ação.') }}
                </p>
                <div class="mt-4">
                    <a href="{{ route('planos.index') }}" class="btn btn-primary gradient-theme-btn px-4" wire:navigate>
                        <i class="bi bi-arrow-left me-2"></i>{{ __('Ver Planos de Ação') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
