<nav class="navbar navbar-expand-lg fixed-top public-navbar shadow-sm">
    <div class="container-fluid px-4 py-2">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="/" wire:navigate>
            <div class="icon-circle-header gradient-theme-icon rounded-circle p-2 me-2 shadow-sm">
                <i class="bi bi-diagram-3 fs-5 text-white"></i>
            </div>
            <div>
                <div class="brand-text-primary text-body lh-1" style="font-size: 1.2rem;">SPS</div>
                <div class="brand-text-secondary small text-muted lh-1" style="font-size: 0.65rem; letter-spacing: 1px;">PORTAL DA TRANSPARÊNCIA</div>
            </div>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublic">
            <i class="bi bi-list fs-4"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarPublic">
            {{-- Âncoras de navegação da landing page --}}
            <ul class="navbar-nav mx-auto align-items-center gap-lg-2 py-3 py-lg-0">
                <li class="nav-item"><a class="nav-link fw-medium" href="#modulos">Módulos</a></li>
                <li class="nav-item"><a class="nav-link fw-medium" href="#funcionalidades">Funcionalidades</a></li>
                <li class="nav-item"><a class="nav-link fw-medium" href="{{ route('documentos.gppei') }}" target="_blank">Guia GPPEI</a></li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center gap-3">
                <li class="nav-item">
                    <button type="button"
                            id="guestThemeSwitcher"
                            class="btn btn-icon btn-ghost-secondary rounded-circle"
                            @click="cycleTheme()"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            :title="themeLabel">
                        <i :class="`bi ${themeIcon} fs-5`"></i>
                    </button>
                </li>
                <li class="nav-item">
                    <a href="{{ route('login') }}" class="btn btn-premium px-4 shadow-sm" wire:navigate>
                        <i class="bi bi-person-circle me-2"></i> {{ __('Área Restrita') }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
