<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="session-lifetime" content="{{ config('session.lifetime') }}">
        <meta data-update-uri="{{ url('/livewire/update') }}">
        <meta name="route-login" content="{{ route('login') }}">
        <meta name="route-logout" content="{{ route('logout') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/_0403eb0b-de95-4131-87cd-5c705ae95535.png') }}" />
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/_0403eb0b-de95-4131-87cd-5c705ae95535.png') }}" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap Icons CDN (Failsafe) -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <!-- Styles / Scripts -->
        @vite(['resources/scss/app.scss', 'resources/js/app.js'])
        @livewireStyles

        <!-- Global Theme Gradient Classes -->
        <style>
            /* ========== GLOBAL THEME GRADIENT CLASSES ========== */
            /* These classes use CSS variables defined by the theme system */

            /* Primary gradient for buttons, icons, and main elements */
            .gradient-theme {
                background: linear-gradient(135deg, var(--theme-primary), var(--theme-primary-light)) !important;
            }

            /* Gradient for active navigation items */
            .gradient-theme-nav {
                background: linear-gradient(135deg, var(--theme-primary), var(--theme-primary-light)) !important;
                color: white !important;
                box-shadow: 0 4px 12px rgba(var(--theme-primary-rgb), 0.25);
                border: none !important;
            }

            .gradient-theme-nav:hover {
                box-shadow: 0 6px 16px rgba(var(--theme-primary-rgb), 0.35);
                color: white !important;
            }

            .gradient-theme-nav:hover i {
                color: white !important;
            }

            /* Premium Button Standard */
            .gradient-theme-btn {
                background: linear-gradient(135deg, var(--theme-primary) 0%, var(--theme-primary-light) 100%) !important;
                border: none !important;
                color: white !important;
                font-weight: 600 !important;
                padding: 0.5rem 1.25rem;
                border-radius: 50px !important;
                box-shadow: 0 4px 15px rgba(var(--theme-primary-rgb), 0.3);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            .gradient-theme-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(var(--theme-primary-rgb), 0.4);
                filter: brightness(1.1);
                color: white !important;
            }

            .gradient-theme-btn:active {
                transform: translateY(0);
            }

            /* Gradient for icons and badges */
            .gradient-theme-icon {
                background: linear-gradient(135deg, var(--theme-primary), var(--theme-primary-light)) !important;
                color: white !important;
                box-shadow: 0 2px 10px rgba(var(--theme-primary-rgb), 0.2);
            }

            /* Gradient for large header sections */
            .gradient-theme-header {
                background: linear-gradient(135deg, var(--theme-primary) 0%, var(--theme-primary-light) 100%);
                box-shadow: 0 8px 32px rgba(var(--theme-primary-rgb), 0.2);
            }

            /* Dark mode - same gradients work for both modes */
            [data-bs-theme="dark"] .gradient-theme,
            [data-bs-theme="dark"] .gradient-theme-nav,
            [data-bs-theme="dark"] .gradient-theme-btn,
            [data-bs-theme="dark"] .gradient-theme-icon,
            [data-bs-theme="dark"] .gradient-theme-header {
                /* Variables already adapt to dark mode, no changes needed */
            }

            [data-bs-theme="dark"] .gradient-theme-header {
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            }

            /* ========== SESSION TIMER STYLES ========== */
            .session-timer-wrapper {
                padding: 0.375rem 0.75rem;
                background: rgba(var(--bs-body-color-rgb), 0.05);
                border-radius: 8px;
                transition: all 0.3s ease;
            }

            [data-bs-theme="dark"] .session-timer-wrapper {
                background: rgba(255, 255, 255, 0.05);
            }

            .session-timer-display {
                font-family: 'Courier New', Courier, monospace;
                font-size: 0.875rem;
                letter-spacing: 0.05em;
                min-width: 65px;
                text-align: center;
                transition: all 0.3s ease;
            }

            .session-timer-display.text-warning {
                color: var(--bs-warning) !important;
                font-weight: 600 !important;
            }

            .session-timer-display.text-danger {
                color: var(--bs-danger) !important;
                font-weight: 700 !important;
                animation: pulse-danger 1.5s ease-in-out infinite;
            }

            @keyframes pulse-danger {
                0%, 100% {
                    opacity: 1;
                }
                50% {
                    opacity: 0.6;
                }
            }

            .session-timer-wrapper:hover {
                background: rgba(var(--bs-primary-rgb), 0.1);
            }

            [data-bs-theme="dark"] .session-timer-wrapper:hover {
                background: rgba(var(--bs-primary-rgb), 0.15);
            }
        </style>
    </head>
    <body class="app-body bg-body-tertiary"
          x-data="appLayout()"
          x-init="init()"
          x-on:app-toggle-sidebar.window="toggleSidebar()"
          x-on:app-open-sidebar.window="openSidebar()"
          x-on:app-close-sidebar.window="closeSidebar()">
        <x-banner />

        @php
            $appNavigation = [
                // Principal
                [
                    'label' => __('Dashboard'),
                    'route' => 'dashboard',
                    'icon' => 'speedometer2',
                    'single' => true
                ],

                // Grupo: Planejamento (conforme especificação do usuário)
                [
                    'label' => __('Planejamento'),
                    'icon' => 'compass',
                    'id' => 'nav-planejamento',
                    'children' => [
                        [
                            'label' => __('Ciclos PEI'),
                            'route' => 'pei.ciclos',
                            'icon' => 'calendar-range'
                        ],
                        [
                            'label' => __('Identidade Estratégica'),
                            'route' => 'pei.index',
                            'icon' => 'clipboard-data'
                        ],
                        [
                            'label' => __('Análise SWOT'),
                            'route' => 'pei.swot',
                            'icon' => 'grid-3x3-gap'
                        ],
                        [
                            'label' => __('Análise PESTEL'),
                            'route' => 'pei.pestel',
                            'icon' => 'globe2'
                        ],
                        [
                            'label' => __('Perspectivas'),
                            'route' => 'pei.perspectivas',
                            'icon' => 'layers'
                        ],
                        [
                            'label' => __('Objetivos'),
                            'route' => 'objetivos.index',
                            'icon' => 'bullseye'
                        ],
                        [
                            'label' => __('Objetivos Estratégicos'),
                            'route' => 'objetivos-estrategicos.index',
                            'icon' => 'shield-check'
                        ],
                        [
                            'label' => __('Planos de Ação'),
                            'route' => 'planos.index',
                            'icon' => 'list-task'
                        ],
                        [
                            'label' => __('Gerenciar Entregas'),
                            'route' => 'entregas.index',
                            'icon' => 'check2-all'
                        ],
                        [
                            'label' => __('Graus de Satisfação'),
                            'route' => 'graus-satisfacao.index',
                            'icon' => 'palette'
                        ],
                        [
                            'label' => __('Indicadores'),
                            'route' => 'indicadores.index',
                            'icon' => 'graph-up'
                        ],
                        [
                            'label' => __('Riscos'),
                            'route' => 'riscos.index',
                            'icon' => 'exclamation-triangle'
                        ],
                        [
                            'label' => __('Mapa Estratégico'),
                            'route' => 'pei.mapa',
                            'icon' => 'map'
                        ],
                    ]
                ],

                // Grupo: Gestão (apenas Auditoria e Relatórios)
                [
                    'label' => __('Gestão'),
                    'icon' => 'gear',
                    'id' => 'nav-gestao',
                    'children' => [
                        [
                            'label' => __('Auditoria'),
                            'route' => 'audit.index',
                            'icon' => 'shield-lock',
                            'can' => 'isSuperAdmin'
                        ],
                        [
                            'label' => __('Relatórios'),
                            'route' => 'relatorios.index',
                            'icon' => 'file-earmark-bar-graph'
                        ],
                    ]
                ],

                // Grupo: Administração
                [
                    'label' => __('Administração'),
                    'icon' => 'sliders',
                    'id' => 'nav-admin',
                    'can' => 'isSuperAdmin',
                    'children' => [
                        [
                            'label' => __('Organizações'),
                            'route' => 'organizacoes.index',
                            'icon' => 'building'
                        ],
                        [
                            'label' => __('Usuários'),
                            'route' => 'usuarios.index',
                            'icon' => 'people'
                        ],
                        [
                            'label' => __('Configurações'),
                            'route' => 'admin.configuracoes',
                            'icon' => 'gear-wide-connected'
                        ],
                    ]
                ],
            ];

            // Filtro de navegação por permissão (recursivo)
            $filterNavigation = function($items) use (&$filterNavigation) {
                return array_filter(array_map(function($item) use ($filterNavigation) {
                    // Verifica permissão do item pai
                    if (isset($item['can']) && !auth()->user()->{$item['can']}()) {
                        return null;
                    }
                    
                    // Filtra filhos recursivamente
                    if (isset($item['children'])) {
                        $item['children'] = $filterNavigation($item['children']);
                        // Se ficou sem filhos após filtro, remove o pai (opcional, mas bom pra admin vazio)
                        if (empty($item['children'])) {
                            return null;
                        }
                    }
                    
                    return $item;
                }, $items));
            };

            $appNavigation = $filterNavigation($appNavigation);
        @endphp

        <div class="app-shell d-flex min-vh-100">
            @include('layouts.partials.sidebar', ['items' => $appNavigation])

            {{-- Mentor Toast Container --}}
            <div id="mentor-toast-container" class="mentor-toast-container" x-data="{ 
                notifications: [],
                addNotification(data) {
                    const id = Date.now();
                    const duration = 7000; // ~7 segundos (6s + 15%)
                    
                    const notification = { 
                        id, 
                        title: data.title || 'Aviso do Mentor',
                        message: data.message || '',
                        icon: data.icon || 'bi-info-circle',
                        type: data.type || 'success',
                        remaining: duration,
                        duration: duration,
                        paused: false,
                        startTime: Date.now()
                    };
                    
                    this.notifications.push(notification);
                    
                    // Iniciar cronômetro para esta notificação
                    this.startTimer(id);
                },
                startTimer(id) {
                    const timer = setInterval(() => {
                        const n = this.notifications.find(n => n.id === id);
                        if (!n) {
                            clearInterval(timer);
                            return;
                        }
                        
                        if (!n.paused) {
                            n.remaining -= 100;
                            if (n.remaining <= 0) {
                                this.removeNotification(id);
                                clearInterval(timer);
                            }
                        }
                    }, 100);
                },
                removeNotification(id) {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }
            }" @mentor-notification.window="addNotification($event.detail)">
                <template x-for="n in notifications" :key="n.id">
                    <div class="mentor-toast" 
                         :class="n.type || 'success'" 
                         x-show="true" 
                         x-transition:leave="toast-fade-out"
                         @mouseenter="n.paused = true"
                         @mouseleave="n.paused = false">
                        
                        <div class="mentor-toast-icon educational-card-gradient">
                            <i class="bi fs-4 text-white" :class="n.icon || 'bi-patch-check-fill'"></i>
                        </div>
                        <div class="mentor-toast-content">
                            <div class="mentor-toast-title" x-text="n.title"></div>
                            <div class="mentor-toast-message" x-html="n.message"></div>
                        </div>
                        <button type="button" class="btn-close small ms-2" @click="removeNotification(n.id)"></button>
                        
                        {{-- Progress Bar --}}
                        <div class="mentor-toast-progress">
                            <div class="mentor-toast-progress-bar" :style="'transform: scaleX(' + (n.remaining / n.duration) + ')'"></div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="app-main flex-grow-1 d-flex flex-column" :class="{'is-sidebar-collapsed': sidebarCollapsed}">
                @livewire('navigation-menu')

                @if (isset($header))
                    <header class="app-page-header app-surface border-bottom shadow-sm">
                        <div class="container-fluid py-3 px-3 px-lg-4">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main class="app-content flex-grow-1 py-4">
                    <div class="container-fluid px-3 px-lg-4">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @stack('modals')

        <!-- Chart.js for data visualization -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

        @livewireScripts

        <script>
            // Theme Color System
            function applyTheme(theme) {
                const root = document.documentElement;

                // Define theme colors with harmonious gradients
                // Define theme colors with harmonious gradients (Modern Palette)
                // Define theme colors with harmonious gradients (Modern Palette)
                const themeColors = {
                    primary: {
                        base: '#1B408E', // Deep Blue
                        dark: '#102A70',
                        light: '#4361EE', // More vibrant blue
                        lighter: '#A0B1F9',
                        rgb: '27, 64, 142'
                    },
                    secondary: {
                        base: '#475569', // Slate 700
                        dark: '#1E293B',
                        light: '#94A3B8', 
                        lighter: '#E2E8F0',
                        rgb: '71, 85, 105'
                    },
                    success: {
                        base: '#059669', // Emerald 600
                        dark: '#064E3B',
                        light: '#10B981', // Vibrant Emerald
                        lighter: '#A7F3D0',
                        rgb: '5, 150, 105'
                    },
                    warning: {
                        base: '#D97706', // Amber 600
                        dark: '#78350F',
                        light: '#F59E0B', // Bright Amber
                        lighter: '#FDE68A',
                        rgb: '217, 119, 6'
                    },
                    danger: {
                        base: '#DC2626', // Red 600
                        dark: '#7F1D1D',
                        light: '#EF4444', 
                        lighter: '#FECACA',
                        rgb: '220, 38, 38'
                    },
                    info: {
                        base: '#0891B2', // Cyan 600
                        dark: '#164E63',
                        light: '#06B6D4', 
                        lighter: '#A5F3FC',
                        rgb: '8, 145, 178'
                    }
                };

                const selectedTheme = themeColors[theme] || themeColors.primary;

                // Apply CSS custom properties for base colors
                root.style.setProperty('--bs-primary', selectedTheme.base);
                root.style.setProperty('--bs-primary-rgb', selectedTheme.rgb);

                // Apply gradient colors
                root.style.setProperty('--theme-primary', selectedTheme.base);
                root.style.setProperty('--theme-primary-dark', selectedTheme.dark);
                root.style.setProperty('--theme-primary-light', selectedTheme.light);
                root.style.setProperty('--theme-primary-lighter', selectedTheme.lighter);
                root.style.setProperty('--theme-primary-rgb', selectedTheme.rgb);

                // Set data attribute for theme-specific styling (text colors, etc.)
                root.setAttribute('data-theme-color', theme);

                // Store theme preference
                localStorage.setItem('user-theme-color', theme);
            }

            function initializeTheme() {
                const userTheme = '{{ Auth::user()->theme_color ?? "primary" }}';
                applyTheme(userTheme);
            }

            // Apply theme on initial page load
            document.addEventListener('DOMContentLoaded', initializeTheme);

            // Apply theme on Livewire navigation (SPA navigation with wire:navigate)
            document.addEventListener('livewire:navigated', initializeTheme);

            // Apply theme when Livewire is loaded/reloaded
            document.addEventListener('livewire:load', initializeTheme);

            // Listen for theme updates from the profile page
            window.addEventListener('theme-updated', function(event) {
                applyTheme(event.detail.themeColor);

                // Dispatch Livewire event to reload and apply theme globally
                setTimeout(function() {
                    window.location.reload();
                }, 300);
            });
        </script>

        <!-- CSRF Token Auto-Refresh -->
        <script>
            (function() {
                'use strict';

                // Refresh CSRF token every 10 minutes (600000ms)
                const REFRESH_INTERVAL = 600000; // 10 minutes

                function refreshCsrfToken() {
                    fetch('/refresh-csrf', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to refresh CSRF token');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.csrf_token) {
                            // Update all CSRF token meta tags
                            const metaTag = document.querySelector('meta[name="csrf-token"]');
                            if (metaTag) {
                                metaTag.setAttribute('content', data.csrf_token);
                            }

                            // Update all CSRF input fields
                            const csrfInputs = document.querySelectorAll('input[name="_token"]');
                            csrfInputs.forEach(input => {
                                input.value = data.csrf_token;
                            });

                            // Update Livewire CSRF token if available
                            if (window.Livewire && window.Livewire.components) {
                                window.Livewire.components.componentsById.forEach(component => {
                                    if (component.data && component.data._token) {
                                        component.data._token = data.csrf_token;
                                    }
                                });
                            }

                            console.log('CSRF token refreshed successfully');
                        }
                    })
                    .catch(error => {
                        console.error('CSRF token refresh error:', error);
                    });
                }

                // Start auto-refresh
                setInterval(refreshCsrfToken, REFRESH_INTERVAL);

                // Also refresh on page visibility change (user returns to tab)
                document.addEventListener('visibilitychange', function() {
                    if (!document.hidden) {
                        refreshCsrfToken();
                    }
                });

                // Refresh when Livewire navigates (SPA navigation)
                document.addEventListener('livewire:navigated', refreshCsrfToken);
            })();
        </script>
    </body>
</html>
