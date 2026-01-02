@props(['items' => []])

<!-- Desktop Sidebar -->
<aside class="app-sidebar app-surface border-end d-none d-lg-flex flex-column"
       :class="{'is-collapsed': sidebarCollapsed}">
    <!-- Header -->
    <div class="app-sidebar-header d-flex align-items-center justify-content-between gap-2 p-3">
        <a class="app-brand d-flex align-items-center gap-2 text-decoration-none text-reset"
           href="{{ route('dashboard') }}"
           wire:navigate>
            <x-application-mark class="app-brand-mark flex-shrink-0" />
            <span class="sidebar-text fw-semibold text-uppercase small mb-0">{{ config('app.name', 'Laravel') }}</span>
        </a>
    </div>

    <!-- Navigation -->
    <div class="app-sidebar-scroll flex-grow-1 overflow-auto px-2 pb-4">
        <nav class="nav nav-pills flex-column gap-1" id="sidebarAccordion">
            @foreach ($items as $item)
                @if(isset($item['single']) && $item['single'])
                    {{-- Item Único (Dashboard) --}}
                    @php
                        $isActive = request()->routeIs($item['route']);
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       wire:navigate
                       class="nav-link-modern d-flex align-items-center gap-2 py-2 px-3 {{ $isActive ? 'active gradient-theme-nav' : '' }}"
                       data-bs-toggle="tooltip"
                       data-bs-placement="right"
                       title="{{ $item['label'] }}">
                        <i class="bi bi-{{ $item['icon'] }} fs-5 flex-shrink-0"></i>
                        <span class="sidebar-text">{{ $item['label'] }}</span>
                    </a>
                @elseif(isset($item['children']))
                    {{-- Grupo com Filhos (Accordion) --}}
                    @php
                        $groupId = $item['id'] ?? 'group-' . Str::slug($item['label']);
                        // Verifica se algum filho está ativo para abrir o grupo
                        $hasActiveChild = false;
                        foreach ($item['children'] as $child) {
                            if (request()->routeIs($child['route'])) {
                                $hasActiveChild = true;
                                break;
                            }
                        }
                    @endphp
                    
                    <div class="nav-group mt-2">
                        <button class="nav-link-modern w-100 d-flex align-items-center justify-content-between gap-2 py-2 px-3 border-0 bg-transparent {{ $hasActiveChild ? 'text-primary' : '' }}"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#{{ $groupId }}"
                                aria-expanded="{{ $hasActiveChild ? 'true' : 'false' }}"
                                aria-controls="{{ $groupId }}"
                                data-tooltip-group="true"
                                data-bs-placement="right"
                                title="{{ $item['label'] }}">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-{{ $item['icon'] }} fs-5 flex-shrink-0"></i>
                                <span class="sidebar-text fw-semibold">{{ $item['label'] }}</span>
                            </div>
                            <i class="bi bi-chevron-down transition-transform fs-7 {{ $hasActiveChild ? 'rotate-180' : '' }}"></i>
                        </button>
                        
                        <div id="{{ $groupId }}" 
                             class="collapse {{ $hasActiveChild ? 'show' : '' }}" 
                             data-bs-parent="#sidebarAccordion">
                            <div class="nav flex-column gap-1 ps-3 py-1">
                                @foreach($item['children'] as $child)
                                    @php
                                        $isChildActive = request()->routeIs($child['route']);
                                    @endphp
                                    <a href="{{ route($child['route']) }}"
                                       wire:navigate
                                       class="nav-link-modern d-flex align-items-center gap-2 py-2 px-3 {{ $isChildActive ? 'active gradient-theme-nav' : '' }}"
                                       data-bs-toggle="tooltip"
                                       data-bs-placement="right"
                                       title="{{ $child['label'] }}">
                                        <i class="bi bi-{{ $child['icon'] }} fs-6 flex-shrink-0"></i>
                                        <span class="sidebar-text small">{{ $child['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>
    </div>

    <!-- User Footer -->
    <div class="app-sidebar-footer p-3">
        <div class="user-card-modern">
            <div class="user-card-inner">
                <div class="user-avatar-wrapper">
                    <img src="{{ Auth::user()->profile_photo_url }}"
                         alt="{{ Auth::user()->name }}"
                         class="user-avatar">
                    <div class="user-status-indicator"></div>
                </div>

                <div class="sidebar-text user-info-wrapper">
                    <p class="user-name"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="{{ Auth::user()->name }}">
                        {{ Auth::user()->name }}
                    </p>
                    <p class="user-email"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="{{ Auth::user()->email }}">
                        {{ Auth::user()->email }}
                    </p>
                </div>
            </div>

            <a href="{{ route('profile.show') }}"
               wire:navigate
               class="sidebar-text user-profile-link"
               data-bs-toggle="tooltip"
               data-bs-placement="top"
               title="{{ __('View profile') }}">
                <i class="bi bi-person-circle me-1"></i>
                <span>{{ __('View profile') }}</span>
                <i class="bi bi-arrow-right ms-auto"></i>
            </a>
        </div>
    </div>
</aside>

<!-- Scripts -->
<script>
    document.addEventListener('livewire:navigated', () => {
        initTooltips();
    });

    document.addEventListener('DOMContentLoaded', () => {
        initTooltips();
    });

    function initTooltips() {
        // Tooltips padrão (elementos com data-bs-toggle="tooltip")
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].map(tooltipTriggerEl => {
            const existing = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
            if (existing) existing.dispose();
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                container: 'body',
                trigger: 'hover',
                fallbackPlacements: ['right', 'top', 'bottom']
            });
        });

        // Tooltips para botões de grupo (usam data-tooltip-group porque data-bs-toggle já é usado para collapse)
        const groupTooltipList = document.querySelectorAll('[data-tooltip-group="true"]');
        [...groupTooltipList].map(el => {
            const existing = bootstrap.Tooltip.getInstance(el);
            if (existing) existing.dispose();
            return new bootstrap.Tooltip(el, {
                container: 'body',
                trigger: 'hover',
                placement: el.getAttribute('data-bs-placement') || 'right',
                fallbackPlacements: ['right', 'top', 'bottom']
            });
        });
    }
</script>

<!-- Mobile Offcanvas -->
<div class="offcanvas offcanvas-start offcanvas-modern d-lg-none"
     tabindex="-1"
     id="appSidebarOffcanvas"
     aria-labelledby="appSidebarLabel">
    <div class="offcanvas-header-modern">
        <div class="d-flex align-items-center gap-2">
            <x-application-mark class="offcanvas-brand-mark" />
            <h5 class="offcanvas-title mb-0" id="appSidebarLabel">{{ config('app.name', 'Laravel') }}</h5>
        </div>
        <button type="button" class="btn-close-modern" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="offcanvas-body-modern flex-grow-1 overflow-auto">
        <!-- User Info -->
        <div class="offcanvas-user-card mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="user-avatar-wrapper-mobile">
                    <img src="{{ Auth::user()->profile_photo_url }}"
                         alt="{{ Auth::user()->name }}"
                         class="user-avatar-mobile">
                    <div class="user-status-indicator"></div>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <p class="user-name-mobile mb-0 fw-bold text-truncate" title="{{ Auth::user()->name }}">
                        {{ Auth::user()->name }}
                    </p>
                    <p class="user-email-mobile mb-0 small text-muted text-truncate" title="{{ Auth::user()->email }}">
                        {{ Auth::user()->email }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Global Selectors Mobile -->
        <div class="offcanvas-selectors mb-4 d-flex flex-column gap-2">
            <div class="selector-wrapper-mobile">
                @livewire('shared.seletor-organizacao', key('mobile-org-selector'))
            </div>
            <div class="selector-wrapper-mobile">
                @livewire('shared.seletor-pei', key('mobile-pei-selector'))
            </div>
            <div class="selector-wrapper-mobile">
                @livewire('shared.seletor-ano', key('mobile-ano-selector'))
            </div>
        </div>

        <!-- Navigation Mobile -->
        <nav class="nav nav-pills flex-column gap-1 mb-4" id="mobileAccordion">
            @foreach ($items as $item)
                @if(isset($item['single']) && $item['single'])
                    @php
                        $isActive = request()->routeIs($item['route']);
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       wire:navigate
                       class="nav-link-modern-mobile d-flex align-items-center gap-2 py-2 px-3 {{ $isActive ? 'active gradient-theme-nav' : '' }}"
                       data-bs-dismiss="offcanvas">
                        <i class="bi bi-{{ $item['icon'] }} fs-5 flex-shrink-0"></i>
                        <span class="fw-medium">{{ $item['label'] }}</span>
                    </a>
                @elseif(isset($item['children']))
                    @php
                        $groupId = ($item['id'] ?? 'mobile-' . Str::slug($item['label'])) . '-mobile';
                        $hasActiveChild = false;
                        foreach ($item['children'] as $child) {
                            if (request()->routeIs($child['route'])) {
                                $hasActiveChild = true;
                                break;
                            }
                        }
                    @endphp
                    
                    <div class="nav-group-mobile mb-1">
                        <button class="nav-link-modern-mobile w-100 d-flex align-items-center justify-content-between gap-2 py-2 px-3 border-0 bg-transparent {{ $hasActiveChild ? 'text-primary fw-bold' : '' }}"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#{{ $groupId }}"
                                aria-expanded="{{ $hasActiveChild ? 'true' : 'false' }}">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-{{ $item['icon'] }} fs-5 flex-shrink-0"></i>
                                <span class="fw-bold">{{ $item['label'] }}</span>
                            </div>
                            <i class="bi bi-chevron-down transition-transform fs-7 {{ $hasActiveChild ? 'rotate-180' : '' }}"></i>
                        </button>
                        
                        <div id="{{ $groupId }}" 
                             class="collapse {{ $hasActiveChild ? 'show' : '' }}"
                             data-bs-parent="#mobileAccordion">
                            <div class="nav flex-column gap-1 ps-4 mt-1 border-start ms-3">
                                @foreach($item['children'] as $child)
                                    @php
                                        $isChildActive = request()->routeIs($child['route']);
                                    @endphp
                                    <a href="{{ route($child['route']) }}"
                                       wire:navigate
                                       class="nav-link-modern-mobile d-flex align-items-center gap-2 py-2 px-3 {{ $isChildActive ? 'active gradient-theme-nav' : '' }}"
                                       data-bs-dismiss="offcanvas">
                                        <i class="bi bi-{{ $child['icon'] }} fs-6 flex-shrink-0"></i>
                                        <span class="small">{{ $child['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>
    </div>

    <!-- Footer Fixo do Offcanvas -->
    <div class="offcanvas-footer-modern p-4 border-top">
        <div class="d-grid gap-2">
            <a href="{{ route('profile.show') }}"
               wire:navigate
               class="btn btn-profile-modern gradient-theme-btn py-2"
               data-bs-dismiss="offcanvas">
                <i class="bi bi-person-circle me-2"></i>{{ __('Manage profile') }}
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-logout-modern w-100 py-2">
                    <i class="bi bi-box-arrow-right me-2"></i>{{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* ========== Desktop Sidebar Styles ========== */

/* Accordion/Group Styles */
.transition-transform {
    transition: transform 0.2s ease;
}

.rotate-180 {
    transform: rotate(180deg);
}

[data-bs-theme="dark"] .nav-group button:not(.active):hover {
    background: rgba(255, 255, 255, 0.05) !important;
}

/* Hide chevron when sidebar collapsed */
.app-sidebar.is-collapsed .bi-chevron-down {
    display: none;
}

/* Section Dividers - REMOVIDO EM FAVOR DO ACCORDION */

/* Navigation Links */
.nav-link-modern {
    border-radius: 10px;
    transition: all 0.2s ease;
    color: var(--bs-body-color);
    text-decoration: none;
    font-weight: 500;
    position: relative;
    cursor: pointer;
}

.nav-link-modern:hover {
    background: rgba(var(--bs-primary-rgb), 0.1);
    color: var(--bs-primary);
    transform: translateX(2px);
}

.nav-link-modern:hover i {
    color: var(--bs-primary);
}

.nav-link-modern.active {
    /* Gradient handled by global .gradient-theme-nav class */
    color: white;
}

.nav-link-modern.active i {
    color: white;
}

.nav-link-modern.active:hover {
    /* Keep gradient background on hover for active items */
    background: linear-gradient(135deg, var(--theme-primary), var(--theme-primary-light)) !important;
    transform: translateX(0);
    color: white !important;
}

/* Ensure icons stay white on hover for active items - IMPORTANT */
.nav-link-modern.active:hover i {
    color: white !important;
}

/* Centralize icons when sidebar is collapsed */
.app-sidebar.is-collapsed .nav-link-modern {
    justify-content: center;
    padding-left: 0 !important;
    padding-right: 0 !important;
}

.app-sidebar.is-collapsed .nav-link-modern:hover {
    transform: translateX(0);
}

.app-sidebar.is-collapsed .sidebar-text {
    display: none; /* Hide text in collapsed mode */
}

/* Ensure chevron is visible in collapsed mode if it doesn't have sidebar-text class */
.app-sidebar.is-collapsed .bi-chevron-down {
    display: block !important;
}

/* Submenu indentation in collapsed mode doesn't work well, so we hide submenus or handle differently */
/* For now, just hiding submenus when collapsed is the safest approach without complex popovers */
/* .app-sidebar.is-collapsed .collapse {
    display: none !important;
} REMOVED TO ALLOW ACCORDION TO OPEN */

/* .app-sidebar.is-collapsed .nav-group > button .bi-chevron-down {
    display: none;
} REMOVED TO SHOW CHEVRON IN COLLAPSED MODE */


[data-bs-theme="dark"] .nav-link-modern {
    color: rgba(255, 255, 255, 0.85);
}

[data-bs-theme="dark"] .nav-link-modern:hover {
    background: rgba(var(--bs-primary-rgb), 0.15);
    color: var(--bs-primary);
}

[data-bs-theme="dark"] .nav-link-modern:hover i {
    color: var(--bs-primary);
}

[data-bs-theme="dark"] .nav-link-modern.active {
    /* Gradient handled by global .gradient-theme-nav class */
    color: white;
}

[data-bs-theme="dark"] .nav-link-modern.active i {
    color: white;
}

[data-bs-theme="dark"] .nav-link-modern.active:hover {
    /* Keep gradient background on hover for active items */
    background: inherit !important;
    color: white !important;
}

[data-bs-theme="dark"] .nav-link-modern.active:hover i {
    color: white !important;
}

/* User Card in Footer */
.user-card-modern {
    background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.05), rgba(var(--bs-primary-rgb), 0.02));
    border: 1px solid var(--bs-border-color);
    border-radius: 14px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.user-card-modern:hover {
    border-color: var(--bs-primary);
    box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.15);
}

[data-bs-theme="dark"] .user-card-modern {
    background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.1), rgba(var(--bs-primary-rgb), 0.05));
    border-color: rgba(255, 255, 255, 0.1);
}

[data-bs-theme="dark"] .user-card-modern:hover {
    border-color: var(--bs-primary);
    box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.25);
}

.user-card-inner {
    padding: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar-wrapper {
    position: relative;
    flex-shrink: 0;
}

.user-avatar {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid var(--bs-border-color);
    transition: all 0.2s ease;
}

.user-card-modern:hover .user-avatar {
    border-color: var(--bs-primary);
}

.user-status-indicator {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 10px;
    height: 10px;
    background: var(--bs-success);
    border: 2px solid var(--bs-body-bg);
    border-radius: 50%;
}

[data-bs-theme="dark"] .user-status-indicator {
    background: #75b798;
}

.user-info-wrapper {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.user-name {
    font-weight: 600;
    font-size: 0.9375rem;
    color: var(--bs-body-color);
    margin-bottom: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    line-height: 1.3;
}

[data-bs-theme="dark"] .user-name {
    color: rgba(255, 255, 255, 0.95);
}

.user-email {
    font-size: 0.75rem;
    color: var(--bs-secondary);
    margin-bottom: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    line-height: 1.3;
}

[data-bs-theme="dark"] .user-email {
    color: rgba(255, 255, 255, 0.6);
}

.user-profile-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 0.875rem;
    background: rgba(var(--bs-primary-rgb), 0.05);
    border-top: 1px solid var(--bs-border-color);
    color: var(--bs-primary);
    text-decoration: none;
    font-size: 0.8125rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.user-profile-link:hover {
    background: rgba(var(--bs-primary-rgb), 0.1);
    color: var(--bs-primary);
}

[data-bs-theme="dark"] .user-profile-link {
    background: rgba(var(--bs-primary-rgb), 0.1);
    border-top-color: rgba(255, 255, 255, 0.1);
    color: #6ea8fe;
}

[data-bs-theme="dark"] .user-profile-link:hover {
    background: rgba(var(--bs-primary-rgb), 0.15);
    color: #9ec5fe;
}

/* ========== Mobile Offcanvas Styles ========== */

.offcanvas-selectors .nav-item.dropdown {
    width: 100%;
}

.offcanvas-selectors .nav-link.dropdown-toggle {
    width: 100%;
    justify-content: flex-start;
}

.offcanvas-selectors .dropdown-menu {
    position: static !important;
    float: none !important;
    width: 100%;
    margin-top: 0.5rem !important;
    box-shadow: none !important;
    border: 1px solid var(--bs-border-color) !important;
    transform: none !important;
}

.offcanvas-modern {
    border-right: 1px solid var(--bs-border-color);
}

[data-bs-theme="dark"] .offcanvas-modern {
    background: rgba(0, 0, 0, 0.95);
    border-right-color: rgba(255, 255, 255, 0.1);
}

.offcanvas-header-modern {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--bs-border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

[data-bs-theme="dark"] .offcanvas-header-modern {
    border-bottom-color: rgba(255, 255, 255, 0.1);
}

.offcanvas-brand-mark {
    width: 32px;
    height: 32px;
}

.offcanvas-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--bs-body-color);
}

[data-bs-theme="dark"] .offcanvas-title {
    color: rgba(255, 255, 255, 0.95);
}

.btn-close-modern {
    background: transparent;
    border: none;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    color: var(--bs-body-color);
    transition: all 0.2s ease;
    cursor: pointer;
}

.btn-close-modern:hover {
    background: rgba(var(--bs-danger-rgb), 0.1);
    color: var(--bs-danger);
}

.offcanvas-body-modern {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
}

.offcanvas-footer-modern {
    background: var(--bs-body-bg);
}

/* User Card Mobile */
.offcanvas-user-card {
    background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.05), rgba(var(--bs-primary-rgb), 0.02));
    border: 1px solid var(--bs-border-color);
    border-radius: 14px;
    padding: 1rem;
}

[data-bs-theme="dark"] .offcanvas-user-card {
    background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.1), rgba(var(--bs-primary-rgb), 0.05));
    border-color: rgba(255, 255, 255, 0.1);
}

.user-avatar-wrapper-mobile {
    position: relative;
    flex-shrink: 0;
}

.user-avatar-mobile {
    width: 3rem;
    height: 3rem;
    border-radius: 14px;
    object-fit: cover;
    border: 2px solid var(--bs-border-color);
}

[data-bs-theme="dark"] .user-avatar-mobile {
    border-color: rgba(255, 255, 255, 0.2);
}

.user-name-mobile {
    font-weight: 600;
    font-size: 1rem;
    color: var(--bs-body-color);
    margin-bottom: 0.125rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

[data-bs-theme="dark"] .user-name-mobile {
    color: rgba(255, 255, 255, 0.95);
}

.user-email-mobile {
    font-size: 0.8125rem;
    color: var(--bs-secondary);
    margin-bottom: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    line-height: 1.3;
}

[data-bs-theme="dark"] .user-email-mobile {
    color: rgba(255, 255, 255, 0.6);
}

/* Navigation Mobile */
.nav-link-modern-mobile {
    border-radius: 10px;
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
    color: var(--bs-body-color);
    text-decoration: none;
    font-weight: 500;
}

.nav-link-modern-mobile:hover {
    background: rgba(var(--bs-primary-rgb), 0.1);
    color: var(--bs-primary);
}

.nav-link-modern-mobile:hover i {
    color: var(--bs-primary);
}

.nav-link-modern-mobile.active {
    /* Gradient handled by global .gradient-theme-nav class */
    color: white;
}

.nav-link-modern-mobile.active i {
    color: white;
}

.nav-link-modern-mobile.active:hover {
    /* Keep gradient background on hover for active items */
    background: inherit !important;
    color: white !important;
}

/* Ensure icons stay white on hover for active items - IMPORTANT */
.nav-link-modern-mobile.active:hover i {
    color: white !important;
}

[data-bs-theme="dark"] .nav-link-modern-mobile {
    color: rgba(255, 255, 255, 0.85);
}

[data-bs-theme="dark"] .nav-link-modern-mobile:hover {
    background: rgba(var(--bs-primary-rgb), 0.15);
    color: var(--bs-primary);
}

[data-bs-theme="dark"] .nav-link-modern-mobile:hover i {
    color: var(--bs-primary);
}

[data-bs-theme="dark"] .nav-link-modern-mobile.active {
    /* Gradient handled by global .gradient-theme-nav class */
    color: white;
}

[data-bs-theme="dark"] .nav-link-modern-mobile.active i {
    color: white;
}

[data-bs-theme="dark"] .nav-link-modern-mobile.active:hover {
    /* Keep gradient background on hover for active items */
    background: inherit !important;
    color: white !important;
}

[data-bs-theme="dark"] .nav-link-modern-mobile.active:hover i {
    color: white !important;
}

/* Action Buttons */
.offcanvas-actions {
    margin-top: auto;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.btn-profile-modern {
    /* Gradient handled by global .gradient-theme-btn class */
    border-radius: 10px;
    font-weight: 600;
    padding: 0.75rem 1.25rem;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-profile-modern:hover {
    color: white;
}

[data-bs-theme="dark"] .btn-profile-modern {
    /* Gradient handled by global .gradient-theme-btn class */
}

.btn-logout-modern {
    background: transparent;
    border: 2px solid var(--bs-danger);
    color: var(--bs-danger);
    border-radius: 10px;
    font-weight: 600;
    padding: 0.75rem 1.25rem;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-logout-modern:hover {
    background: var(--bs-danger);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(var(--bs-danger-rgb), 0.3);
}

[data-bs-theme="dark"] .btn-logout-modern {
    border-color: #ea868f;
    color: #ea868f;
}

[data-bs-theme="dark"] .btn-logout-modern:hover {
    background: #dc3545;
    border-color: #dc3545;
    color: white;
}
</style>