<div class="app-topbar app-surface border-bottom shadow-sm">
    <div class="container-fluid px-3 py-2 d-flex align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <button type="button"
                    class="btn btn-outline-secondary btn-icon d-lg-none no-border"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#appSidebarOffcanvas"
                    aria-controls="appSidebarOffcanvas"
                    aria-label="{{ __('Open navigation menu') }}">
                <i class="bi bi-list"></i>
            </button>

            <button type="button"
                    class="btn btn-outline-secondary btn-icon d-none d-lg-inline-flex no-border"
                    aria-label="{{ __('Toggle sidebar') }}"
                    data-bs-toggle="tooltip"
                    data-bs-placement="bottom"
                    title="{{ __('Collapse sidebar') }}"
                    @click="$dispatch('app-toggle-sidebar')">
                <i class="bi bi-layout-sidebar-inset"></i>
            </button>

            <span class="fw-semibold text-muted small text-uppercase d-none d-sm-inline">
                {{ config('app.name', 'Laravel') }}
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Session Timer -->
            <div class="session-timer-wrapper d-none d-md-flex align-items-center gap-2"
                 data-bs-toggle="tooltip"
                 data-bs-placement="bottom"
                 title="Tempo restante de sessão">
                <i class="bi bi-clock-history text-body-secondary"></i>
                <span id="sessionTimer" class="session-timer-display text-body-secondary small fw-mono">--:--:--</span>
            </div>

            <button type="button"
                    id="appThemeSwitcher"
                    class="btn btn-outline-secondary btn-iconno-border no-border"
                    aria-label="{{ __('Toggle theme') }}"
                    data-bs-toggle="tooltip"
                    data-bs-placement="bottom"
                    :title="themeLabel"
                    @click="cycleTheme()">
                <i :class="`bi ${themeIcon}`"></i>
            </button>

            @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                <x-dropdown align="right" width="60">
                    <x-slot name="trigger">
                        <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 no-border">
                            {{ Auth::user()->currentTeam->name }}

                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-secondary" style="width: 1rem; height: 1rem;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <h6 class="dropdown-header text-muted text-uppercase small fw-semibold mb-0">
                            {{ __('Manage Team') }}
                        </h6>

                        <x-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                            {{ __('Team Settings') }}
                        </x-dropdown-link>

                        @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                            <x-dropdown-link href="{{ route('teams.create') }}">
                                {{ __('Create New Team') }}
                            </x-dropdown-link>
                        @endcan

                        @if (Auth::user()->allTeams()->count() > 1)
                            <hr class="dropdown-divider">

                            <h6 class="dropdown-header text-muted text-uppercase small fw-semibold mb-0">
                                {{ __('Switch Teams') }}
                            </h6>

                            @foreach (Auth::user()->allTeams() as $team)
                                <x-switchable-team :team="$team" />
                            @endforeach
                        @endif
                    </x-slot>
                </x-dropdown>
            @endif

            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                        <button type="button" class="btn btn-link p-0 border-0 d-inline-flex align-items-center">
                            <img class="rounded-circle object-fit-cover" style="width: 2.5rem; height: 2.5rem;" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 no-border">
                            {{ Auth::user()->name }}

                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-secondary" style="width: 1rem; height: 1rem;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                            </svg>
                        </button>
                    @endif
                </x-slot>

                <x-slot name="content">
                    <h6 class="dropdown-header text-muted text-uppercase small fw-semibold mb-0">
                        {{ __('Manage Account') }}
                    </h6>

                    <x-dropdown-link href="{{ route('profile.show') }}">
                        {{ __('Profile') }}
                    </x-dropdown-link>

                    @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                        <x-dropdown-link href="{{ route('api-tokens.index') }}">
                            {{ __('API Tokens') }}
                        </x-dropdown-link>
                    @endif

                    <hr class="dropdown-divider">

                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf

                        <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</div>
