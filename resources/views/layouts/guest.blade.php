<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta data-update-uri="{{ url('/livewire/update') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

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

            /* Gradient for buttons */
            .gradient-theme-btn {
                background: linear-gradient(135deg, var(--theme-primary), var(--theme-primary-light));
                border: none;
                color: white;
                box-shadow: 0 2px 8px rgba(var(--theme-primary-rgb), 0.25);
                transition: all 0.2s ease;
            }

            .gradient-theme-btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(var(--theme-primary-rgb), 0.35);
                color: white;
            }

            /* Gradient for icons and badges */
            .gradient-theme-icon {
                background: linear-gradient(135deg, var(--theme-primary), var(--theme-primary-light));
                color: white;
            }

            /* Dark mode - same gradients work for both modes */
            [data-bs-theme="dark"] .gradient-theme,
            [data-bs-theme="dark"] .gradient-theme-btn,
            [data-bs-theme="dark"] .gradient-theme-icon {
                /* Variables already adapt to dark mode, no changes needed */
            }
        </style>

        <style>
            body {
                background-color: #f8f9fa;
                transition: background-color 0.3s ease;
            }

            [data-bs-theme="dark"] body {
                background: linear-gradient(135deg, #1a1d29 0%, #2d3748 100%);
            }

            .guest-container {
                position: relative;
                z-index: 1;
            }

            /* Subtle pattern overlay for dark mode */
            [data-bs-theme="dark"] body::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-image:
                    radial-gradient(circle at 20% 30%, rgba(13, 110, 253, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 80% 70%, rgba(102, 16, 242, 0.1) 0%, transparent 50%);
                pointer-events: none;
                z-index: 0;
            }
        </style>
    </head>
    <body>
        <!-- Theme Switcher -->
        <div class="theme-switcher-guest">
            <button type="button"
                    id="guestThemeSwitcher"
                    class="btn btn-theme-toggle-guest"
                    aria-label="Toggle theme"
                    data-bs-toggle="tooltip"
                    data-bs-placement="left"
                    title="System">
                <i class="bi bi-circle-half"></i>
            </button>
        </div>

        <div class="min-vh-100 d-flex align-items-center justify-content-center guest-container">
            {{ $slot }}
        </div>

        @livewireScripts

        <style>
            .theme-switcher-guest {
                position: fixed;
                top: 1.5rem;
                right: 1.5rem;
                z-index: 1050;
            }

            .btn-theme-toggle-guest {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                border: 1px solid var(--bs-border-color);
                background: rgba(255, 255, 255, 0.95);
                color: var(--bs-body-color);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .btn-theme-toggle-guest:hover {
                background: rgba(var(--bs-primary-rgb), 0.1);
                border-color: var(--bs-primary);
                color: var(--bs-primary);
                transform: scale(1.05);
            }

            [data-bs-theme="dark"] .btn-theme-toggle-guest {
                background: rgba(255, 255, 255, 0.1);
                border-color: rgba(255, 255, 255, 0.2);
                color: rgba(255, 255, 255, 0.9);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            }

            [data-bs-theme="dark"] .btn-theme-toggle-guest:hover {
                background: rgba(255, 255, 255, 0.15);
                border-color: var(--bs-primary);
                color: var(--bs-primary);
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const THEME_KEY = 'app.theme';
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');

                let currentTheme = localStorage.getItem(THEME_KEY) || 'system';

                const themeConfig = {
                    light: { label: 'Light', icon: 'bi-brightness-high' },
                    dark: { label: 'Dark', icon: 'bi-moon-stars' },
                    system: { label: 'System', icon: 'bi-circle-half' }
                };

                const applyTheme = (theme) => {
                    const resolvedTheme = theme === 'system'
                        ? (prefersDark.matches ? 'dark' : 'light')
                        : theme;
                    document.documentElement.setAttribute('data-bs-theme', resolvedTheme);
                };

                const updateThemeButton = () => {
                    const themeSwitcher = document.getElementById('guestThemeSwitcher');
                    if (!themeSwitcher) return;

                    const icon = themeSwitcher.querySelector('i');
                    const config = themeConfig[currentTheme] || themeConfig.system;

                    icon.className = `bi ${config.icon}`;

                    const tooltipInstance = bootstrap.Tooltip.getInstance(themeSwitcher);
                    if (tooltipInstance) {
                        tooltipInstance.setContent({ '.tooltip-inner': config.label });
                    }
                };

                const cycleTheme = () => {
                    currentTheme = currentTheme === 'light' ? 'dark' : currentTheme === 'dark' ? 'system' : 'light';
                    localStorage.setItem(THEME_KEY, currentTheme);
                    applyTheme(currentTheme);
                    updateThemeButton();
                };

                // Apply initial theme
                applyTheme(currentTheme);

                // Watch system preference changes
                prefersDark.addEventListener('change', () => {
                    if (currentTheme === 'system') {
                        applyTheme(currentTheme);
                    }
                });

                // Setup theme switcher button
                const themeSwitcher = document.getElementById('guestThemeSwitcher');
                if (themeSwitcher) {
                    // Initialize tooltip
                    new bootstrap.Tooltip(themeSwitcher);

                    themeSwitcher.addEventListener('click', cycleTheme);
                    updateThemeButton();
                }
            });

            // Theme Color System (Guest uses default primary)
            (function() {
                const root = document.documentElement;

                const themeColors = {
                    primary: {
                        base: '#0d6efd',
                        dark: '#0a58ca',
                        light: '#7bb5ff',
                        lighter: '#6ea8fe',
                        rgb: '13, 110, 253'
                    }
                };

                const selectedTheme = themeColors.primary;

                // Apply CSS custom properties
                root.style.setProperty('--bs-primary', selectedTheme.base);
                root.style.setProperty('--bs-primary-rgb', selectedTheme.rgb);
                root.style.setProperty('--theme-primary', selectedTheme.base);
                root.style.setProperty('--theme-primary-dark', selectedTheme.dark);
                root.style.setProperty('--theme-primary-light', selectedTheme.light);
                root.style.setProperty('--theme-primary-lighter', selectedTheme.lighter);
                root.style.setProperty('--theme-primary-rgb', selectedTheme.rgb);

                // Set data attribute for theme-specific styling (text colors, etc.)
                root.setAttribute('data-theme-color', 'primary');
            })();
        </script>
    </body>
</html>
