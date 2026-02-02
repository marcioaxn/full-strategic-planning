<div class="nav-item dropdown ms-3">
    <a class="nav-link dropdown-toggle d-flex align-items-center bg-light-subtle rounded-3 px-3 py-2 border shadow-sm" 
       href="#" id="navbarDropdownOrg" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-building me-2 text-primary"></i>
        <div class="d-none d-lg-block">
            <small class="text-muted d-block" style="font-size: 0.7rem; line-height: 1;">Organização Selecionada</small>
            <span class="fw-bold text-dark">
                {{ Session::get('organizacao_selecionada_sgl', 'Selecione...') }}
            </span>
        </div>
        <div class="d-lg-none">
            <span class="fw-bold text-dark">{{ Session::get('organizacao_selecionada_sgl', 'Org.') }}</span>
        </div>
    </a>
    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2 mt-2" aria-labelledby="navbarDropdownOrg" style="min-width: 250px; max-height: 400px; overflow-y: auto;">
        <li class="dropdown-header text-uppercase small fw-bold text-muted pb-2 border-bottom mb-2">
            {{ __('Unidades Organizacionais') }}
        </li>
        @forelse($organizacoes as $org)
            @php 
                $id = is_array($org) ? $org['id'] : $org->cod_organizacao;
                $label = is_array($org) ? $org['label'] : $org->sgl_organizacao . ' - ' . $org->nom_organizacao;
                $sgl = is_array($org) ? explode(' - ', $org['label'])[0] : $org->sgl_organizacao;
                // Remove espaços de indentação para a sigla do avatar
                $sglClean = trim(str_replace([' ', '↳'], '', $sgl));
            @endphp
            <li>
                <button type="button" 
                        class="dropdown-item d-flex align-items-center py-2 {{ $selecionadaId === $id ? 'active' : '' }}" 
                        wire:click="selecionar('{{ $id }}')">
                    <div class="avatar-org-sm me-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem; flex-shrink: 0;">
                        {{ substr($sglClean, 0, 2) }}
                    </div>
                    <div class="text-truncate">
                        <span class="d-block fw-semibold text-truncate" style="max-width: 280px;">{!! $label !!}</span>
                    </div>
                    @if($selecionadaId === $id)
                        <i class="bi bi-check-lg ms-auto {{ $selecionadaId === $id ? 'text-white' : 'text-primary' }}"></i>
                    @endif
                </button>
            </li>
        @empty
            <li><span class="dropdown-item-text text-muted italic">Nenhuma organização vinculada</span></li>
        @endforelse
    </ul>
</div>