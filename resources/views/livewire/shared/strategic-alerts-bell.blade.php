<div class="dropdown">
    <button class="btn btn-icon btn-ghost-secondary position-relative rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell fs-5"></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="padding: 0.35em 0.5em; font-size: 0.6rem;">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0 overflow-hidden" style="width: 320px; border-radius: 16px;">
        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark small text-uppercase">{{ __('Alertas Estratégicos') }}</h6>
            @if($unreadCount > 0)
                <button class="btn btn-link btn-sm p-0 text-decoration-none text-primary small" wire:click="markAllAsRead">
                    {{ __('Marcar como lidas') }}
                </button>
            @endif
        </div>
        
        <div class="max-h-300 overflow-auto" style="max-height: 350px;">
            @forelse($alerts as $alert)
                <div class="p-3 border-bottom hover-bg-light transition-all cursor-pointer @if(!$alert->read_at) bg-primary bg-opacity-5 @endif">
                    <div class="d-flex gap-3">
                        <div class="rounded-circle bg-{{ $alert->type }}-subtle text-{{ $alert->type }} d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi {{ $alert->icon }}"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-bold text-dark small text-truncate">{{ $alert->title }}</div>
                            <p class="small text-muted mb-1 lh-sm" style="font-size: 0.75rem;">{!! $alert->message !!}</p>
                            <div class="text-muted" style="font-size: 0.65rem;">
                                <i class="bi bi-clock me-1"></i>{{ $alert->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-bell-slash fs-2 opacity-25 d-block mb-2"></i>
                    <span class="small">Nenhum alerta recente.</span>
                </div>
            @endforelse
        </div>
        
        <div class="p-2 bg-light text-center border-top">
            <button class="btn btn-link btn-sm text-decoration-none text-muted small w-100 disabled">
                {{ __('Ver todo o histórico') }}
            </button>
        </div>
    </div>
</div>
