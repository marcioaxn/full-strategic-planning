<div class="nav-item dropdown ms-2">
    <a class="nav-link dropdown-toggle d-flex align-items-center bg-light-subtle rounded-3 px-3 py-2 border shadow-sm"
       href="#" id="navbarDropdownAno" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-clock-history me-2 text-primary" wire:loading.remove wire:target="selecionar"></i>
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status" wire:loading wire:target="selecionar">
            <span class="visually-hidden">Processando...</span>
        </div>
        <div class="d-none d-lg-block">
            <small class="text-muted d-block" style="font-size: 0.7rem; line-height: 1;">Ano Ref.</small>
            <span class="fw-bold text-dark">
                {{ $anoSelecionado }}
            </span>
        </div>
        <div class="d-lg-none">
            <span class="fw-bold text-dark">{{ $anoSelecionado }}</span>
        </div>
    </a>
    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2 mt-2" aria-labelledby="navbarDropdownAno" style="min-width: 240px; max-height: 400px; overflow-y: auto;">
        <li class="dropdown-header text-uppercase small fw-bold text-muted pb-2 border-bottom mb-2">
            Selecionar Ano de Referência
        </li>
        @foreach($anosAgrupados as $grupo)
            @php
                $isCurrentPei = ($grupo['pei_id'] ?? null) === $peiSelecionadoId;
            @endphp
            <li>
                <div class="dropdown-header {{ $isCurrentPei ? 'text-primary fw-bold' : 'text-muted' }} bg-light bg-opacity-50 py-1" style="font-size: 0.65rem;">
                    @if($isCurrentPei) <i class="bi bi-star-fill me-1"></i> @endif
                    {{ $grupo['label'] }}
                </div>
            </li>
            @foreach($grupo['anos'] as $ano)
                <li>
                    <button type="button"
                            class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ (int)$anoSelecionado === (int)$ano ? 'active' : '' }}"
                            wire:click="selecionar('{{ $ano }}')">
                        <span class="{{ (int)$anoSelecionado === (int)$ano ? 'fw-bold' : '' }}">
                            {{ $ano }}
                        </span>
                        @if((int)$anoSelecionado === (int)$ano)
                            <i class="bi bi-check-lg ms-3"></i>
                        @endif
                    </button>
                </li>
            @endforeach
            @if(!$loop->last)
                <li><hr class="dropdown-divider opacity-50"></li>
            @endif
        @endforeach
    </ul>
</div>
