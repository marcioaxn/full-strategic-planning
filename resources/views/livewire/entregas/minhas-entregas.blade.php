<div>
    {{-- Header --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" wire:navigate class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Minhas Entregas</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2 mt-1">
                <div class="icon-circle-header gradient-theme-icon"><i class="bi bi-person-check-fill"></i></div>
                <h1 class="h3 fw-bold mb-0">Minhas Entregas</h1>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($totalAtrasadas > 0)
                <span class="badge bg-danger rounded-pill px-3 py-2">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $totalAtrasadas }} atrasada{{ $totalAtrasadas > 1 ? 's' : '' }}
                </span>
            @endif
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                {{ $totalPendente }} pendente{{ $totalPendente !== 1 ? 's' : '' }}
            </span>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce="busca" class="form-control border-start-0 ps-0" placeholder="Buscar entrega...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filtroStatus" class="form-select">
                        <option value="">Todos os status</option>
                        @foreach($statusOptions as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filtroPrioridade" class="form-select">
                        <option value="">Todas as prioridades</option>
                        @foreach($prioridades as $k => $v)
                            <option value="{{ $k }}">{{ $v['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-center">
                    <div wire:loading class="spinner-border text-primary spinner-border-sm"></div>
                </div>
            </div>
        </div>
    </div>

    @if($entregasAgrupadas->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-person-check fs-1 text-success opacity-50 d-block mb-3"></i>
                <h5 class="fw-bold text-success">Tudo em dia!</h5>
                <p class="text-muted">Nenhuma entrega pendente atribuída a você.</p>
            </div>
        </div>
    @else
        @foreach($entregasAgrupadas as $planoId => $entregas)
        @php $plano = $entregas->first()?->planoDeAcao; @endphp
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-list-task text-primary"></i>
                    <div>
                        <h6 class="fw-bold mb-0">{{ $plano?->dsc_plano_de_acao ?? 'Plano não identificado' }}</h6>
                        @if($plano?->objetivo)
                            <small class="text-muted">{{ $plano->objetivo->nom_objetivo }}</small>
                        @endif
                    </div>
                    <a href="{{ route('planos.entregas', $planoId) }}" wire:navigate class="btn btn-sm btn-outline-info ms-auto rounded-pill px-3">
                        <i class="bi bi-arrow-right me-1"></i>Ver Plano
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($entregas as $entrega)
                    @php
                        $atrasada  = $entrega->dte_prazo && $entrega->dte_prazo->isPast();
                        $priorCor  = match($entrega->cod_prioridade) { 'alta' => 'danger', 'urgente' => 'dark', 'baixa' => 'success', default => 'warning' };
                        $statusCor = match($entrega->bln_status) { 'Em Andamento' => 'primary', 'Concluído' => 'success', 'Cancelado' => 'secondary', default => 'light' };
                    @endphp
                    <div class="list-group-item px-4 py-3 {{ $atrasada ? 'bg-danger bg-opacity-5' : '' }}">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-{{ $statusCor }} {{ $statusCor === 'light' ? 'text-dark border' : '' }} small">{{ $entrega->bln_status }}</span>
                                    <span class="badge bg-{{ $priorCor }}-subtle text-{{ $priorCor }} small">{{ $entrega->cod_prioridade }}</span>
                                    @if($atrasada)
                                        <span class="badge bg-danger small"><i class="bi bi-exclamation-triangle me-1"></i>Atrasada</span>
                                    @endif
                                </div>
                                <p class="fw-semibold mb-1">{{ $entrega->dsc_entrega }}</p>
                                @if($entrega->dte_prazo)
                                    <small class="{{ $atrasada ? 'text-danger fw-bold' : 'text-muted' }}">
                                        <i class="bi bi-calendar me-1"></i>{{ $entrega->dte_prazo->format('d/m/Y') }}
                                        @if($atrasada) — {{ $entrega->dte_prazo->diffForHumans() }} @endif
                                    </small>
                                @endif
                            </div>
                            <a href="{{ route('planos.entregas', $planoId) }}" wire:navigate class="btn btn-xs btn-outline-secondary py-1 px-2 flex-shrink-0">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    @endif
</div>
