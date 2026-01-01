<div class="nav-item dropdown ms-2">
    <a class="nav-link dropdown-toggle d-flex align-items-center bg-light-subtle rounded-3 px-3 py-2 border shadow-sm"
       href="#" id="navbarDropdownAno" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-clock-history me-2 text-primary"></i>
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
    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2 mt-2" aria-labelledby="navbarDropdownAno" style="max-height: 300px; overflow-y: auto;">
        <li class="dropdown-header text-uppercase small fw-bold text-muted pb-2 border-bottom mb-2">
            Selecionar Ano
        </li>
        @foreach($anos as $ano)
            <li>
                <button type="button"
                        class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ (int)$anoSelecionado === (int)$ano ? 'active' : '' }}"
                        wire:click="selecionar('{{ $ano }}')">
                    <span class="fw-semibold">{{ $ano }}</span>
                    @if((int)$anoSelecionado === (int)$ano)
                        <i class="bi bi-check-lg ms-3"></i>
                    @endif
                </button>
            </li>
        @endforeach
    </ul>
</div>