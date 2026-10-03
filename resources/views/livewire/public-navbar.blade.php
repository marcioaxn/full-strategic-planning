<nav class="navbar navbar-expand-lg fixed-top public-navbar shadow-sm">
    <div class="container-fluid px-4 py-1">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('welcome') }}" wire:navigate>
            <div class="icon-circle-header gradient-theme-icon rounded-circle me-2 shadow-sm pnav-marca-icone">
                <i class="bi bi-diagram-3 text-white"></i>
            </div>
            <div>
                <div class="brand-text-primary text-body lh-1 pnav-marca-titulo">{{ config('app.name', 'Sistema PEI') }}</div>
                <div class="brand-text-secondary text-muted lh-1 pnav-marca-sub">PORTAL DA TRANSPARÊNCIA</div>
            </div>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublic">
            <i class="bi bi-list fs-4"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarPublic">
            {{-- Âncoras de navegação da landing page --}}
            <ul class="navbar-nav mx-auto align-items-center gap-lg-2 py-3 py-lg-0">
                <li class="nav-item"><a class="nav-link fw-medium d-flex align-items-center pnav-alvo" href="#modulos">Módulos</a></li>
                <li class="nav-item"><a class="nav-link fw-medium d-flex align-items-center pnav-alvo" href="#funcionalidades">Funcionalidades</a></li>
                <li class="nav-item"><a class="nav-link fw-medium d-flex align-items-center pnav-alvo" href="{{ route('documentos.gppei') }}" target="_blank">Guia GPPEI</a></li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center gap-3">
                <li class="nav-item">
                    <button type="button"
                            id="guestThemeSwitcher"
                            class="btn btn-icon btn-ghost-secondary rounded-circle"
                            aria-label="Alternar entre tema claro, escuro e o do sistema"
                            @click="cycleTheme()"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            :title="themeLabel">
                        <i :class="`bi ${themeIcon} fs-5`"></i>
                    </button>
                </li>
                <li class="nav-item">
                    <a href="{{ route('login') }}" class="btn btn-premium px-3 shadow-sm d-inline-flex align-items-center pnav-alvo" wire:navigate>
                        <i class="bi bi-person-circle me-2"></i> {{ __('Área Restrita') }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
