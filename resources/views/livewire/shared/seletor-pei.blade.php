<div class="nav-item dropdown ms-2">
    <a class="nav-link dropdown-toggle d-flex align-items-center bg-light-subtle rounded-3 px-3 py-2 border shadow-sm"
       href="#" id="navbarDropdownPEI" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-calendar-range me-2 text-success" wire:loading.remove wire:target="selecionar"></i>
        <div class="spinner-border spinner-border-sm text-success me-2" role="status" wire:loading wire:target="selecionar">
            <span class="visually-hidden">Processando...</span>
        </div>
        <div class="d-none d-lg-block">
            <small class="text-muted d-block" style="font-size: 0.7rem; line-height: 1;">Ciclo PEI</small>
            <span class="fw-bold text-dark">
                {{ Session::get('pei_selecionado_periodo', 'Selecione...') }}
            </span>
        </div>
        <div class="d-lg-none">
            <span class="fw-bold text-dark">{{ Session::get('pei_selecionado_periodo', 'PEI') }}</span>
        </div>
    </a>
    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2 mt-2" aria-labelledby="navbarDropdownPEI" style="min-width: 280px; max-height: 400px; overflow-y: auto;">
        <li class="dropdown-header text-uppercase small fw-bold text-muted pb-2 border-bottom mb-2">
            Ciclos de Planejamento
        </li>
        @forelse($peis as $pei)
            @php
                $isAtivo = $pei->isAtivo();
                $periodo = $pei->num_ano_inicio_pei . '-' . $pei->num_ano_fim_pei;
            @endphp
            <li>
                <button type="button"
                        class="dropdown-item d-flex align-items-center py-2 {{ $selecionadoId === $pei->cod_pei ? 'active' : '' }}"
                        wire:click="selecionar('{{ $pei->cod_pei }}')">
                    <div class="icon-circle-mini avatar-pei-sm me-3 {{ $isAtivo ? 'bg-success' : 'bg-secondary' }} bg-opacity-10 {{ $isAtivo ? 'text-success' : 'text-secondary' }} rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.7rem;">
                        <i class="bi {{ $isAtivo ? 'bi-check-circle-fill' : 'bi-clock-history' }}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="d-block fw-semibold">{{ $periodo }}</span>
                        <small class="text-muted d-block text-truncate" style="max-width: 180px;">{{ $pei->dsc_pei }}</small>
                    </div>
                    <div class="ms-2 d-flex flex-column align-items-end">
                        @if($isAtivo)
                            <span class="badge bg-success-subtle text-success" style="font-size: 0.6rem;">ATIVO</span>
                        @elseif($pei->num_ano_fim_pei < now()->year)
                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">ENCERRADO</span>
                        @else
                            <span class="badge bg-info-subtle text-info" style="font-size: 0.6rem;">FUTURO</span>
                        @endif
                        @if($selecionadoId === $pei->cod_pei)
                            <i class="bi bi-check-lg text-white mt-1"></i>
                        @endif
                    </div>
                </button>
            </li>
        @empty
            <li><span class="dropdown-item-text text-muted fst-italic">Nenhum ciclo PEI cadastrado</span></li>
        @endforelse
    </ul>
</div>
