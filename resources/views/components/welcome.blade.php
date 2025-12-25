<div class="card app-surface shadow-sm border-0 mb-4">
    <div class="card-body p-4 p-xl-5 d-flex flex-column gap-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
            <div class="d-flex align-items-center gap-3">
                <div class="welcome-logo bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center">
                    <x-application-logo style="width: 2.25rem; height: 2.25rem;" />
                </div>
                <div>
                    <h2 class="h4 text-body-emphasis mb-1">{{ __('Welcome to your Jetstream + Bootstrap kit!') }}</h2>
                    <p class="text-body-secondary small mb-0">
                        {{ __('Acompanhe a migração para Bootstrap 5, planeje os próximos blocos e acelere sua entrega.') }}
                    </p>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="https://github.com/laravel/jetstream"
                   class="btn btn-outline-primary d-inline-flex align-items-center gap-2"
                   target="_blank"
                   rel="noopener">
                    <i class="bi bi-github"></i>
                    {{ __('Jetstream Repo') }}
                </a>

                <a href="https://github.com/twbs/bootstrap"
                   class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
                   target="_blank"
                   rel="noopener">
                    <i class="bi bi-bootstrap-fill"></i>
                    {{ __('Bootstrap Repo') }}
                </a>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 g-3">
            <div class="col">
                <div class="app-surface-subtle rounded-4 p-4 h-100 d-flex gap-3">
                    <span class="welcome-icon text-primary bg-primary-subtle d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0">
                        <i class="bi bi-journal-text"></i>
                    </span>
                    <div>
                        <h3 class="h6 text-body-emphasis mb-2">
                            <a href="https://laravel.com/docs" class="text-decoration-none link-body-emphasis" target="_blank" rel="noopener">
                                {{ __('Laravel Documentation') }}
                            </a>
                        </h3>
                        <p class="text-body-secondary small mb-3">
                            {{ __('Guia completo para roteamento, migrations, Livewire e ecosistema Laravel 12.') }}
                        </p>
                        <a href="https://laravel.com/docs" class="link-primary fw-semibold small d-inline-flex align-items-center gap-1" target="_blank" rel="noopener">
                            {{ __('Abrir documentação') }}
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="app-surface-subtle rounded-4 p-4 h-100 d-flex gap-3">
                    <span class="welcome-icon text-success bg-success-subtle d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0">
                        <i class="bi bi-play-btn"></i>
                    </span>
                    <div>
                        <h3 class="h6 text-body-emphasis mb-2">
                            <a href="https://laracasts.com" class="text-decoration-none link-body-emphasis" target="_blank" rel="noopener">
                                {{ __('Laracasts') }}
                            </a>
                        </h3>
                        <p class="text-body-secondary small mb-3">
                            {{ __('Videoaulas para acelerar seu aprendizado em Laravel, Livewire e Alpine.') }}
                        </p>
                        <a href="https://laracasts.com" class="link-primary fw-semibold small d-inline-flex align-items-center gap-1" target="_blank" rel="noopener">
                            {{ __('Assistir agora') }}
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="app-surface-subtle rounded-4 p-4 h-100 d-flex gap-3">
                    <span class="welcome-icon text-warning bg-warning-subtle d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0">
                        <i class="bi bi-columns-gap"></i>
                    </span>
                    <div>
                        <h3 class="h6 text-body-emphasis mb-2">
                            <a href="https://getbootstrap.com/docs/5.3/getting-started/introduction/" class="text-decoration-none link-body-emphasis" target="_blank" rel="noopener">
                                {{ __('Bootstrap Components') }}
                            </a>
                        </h3>
                        <p class="text-body-secondary small mb-3">
                            {{ __('Explore cards, tabelas, offcanvas e utilitários para fortalecer o layout responsivo.') }}
                        </p>
                        <a href="https://getbootstrap.com/" class="link-primary fw-semibold small d-inline-flex align-items-center gap-1" target="_blank" rel="noopener">
                            {{ __('Explorar componentes') }}
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="app-surface-subtle rounded-4 p-4 h-100 d-flex gap-3">
                    <span class="welcome-icon text-info bg-info-subtle d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0">
                        <i class="bi bi-lock"></i>
                    </span>
                    <div>
                        <h3 class="h6 text-body-emphasis mb-2">
                            {{ __('Authentication & Security') }}
                        </h3>
                        <p class="text-body-secondary small mb-3">
                            {{ __('Login, registro, verificação de e-mail e 2FA prontos para uso com Jetstream.') }}
                        </p>
                        <a href="{{ route('profile.show') }}" class="link-primary fw-semibold small d-inline-flex align-items-center gap-1">
                            {{ __('Gerenciar perfil') }}
                            <i class="bi bi-arrow-right-short"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
