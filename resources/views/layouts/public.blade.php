<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta data-update-uri="{{ url('/livewire/update') }}">

        <title>{{ config('app.name', 'Laravel') }} | Portal da Transparência</title>

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
            /* Hardcoded colors to prevent variable loss during Livewire updates */
            :root {
                --theme-primary: #1B408E;
                --theme-primary-light: #4361EE;
                --theme-primary-rgb: 27, 64, 142;
            }

            .gradient-theme { background: linear-gradient(135deg, #1B408E, #4361EE) !important; }
            .gradient-theme-btn {
                background: linear-gradient(135deg, #1B408E, #4361EE);
                border: none; color: white;
                box-shadow: 0 2px 8px rgba(27, 64, 142, 0.25);
                transition: all 0.2s ease;
            }
            .gradient-theme-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(27, 64, 142, 0.35); color: white; }
            .gradient-theme-icon { background: linear-gradient(135deg, #1B408E, #4361EE); color: white; }
            
            .btn-premium {
                background: linear-gradient(135deg, #1B408E 0%, #4361EE 100%) !important;
                border: none !important; color: white !important; font-weight: 600 !important;
                padding: 0.5rem 1.5rem; border-radius: 50px !important;
                box-shadow: 0 4px 15px rgba(27, 64, 142, 0.3);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                display: inline-flex; align-items: center; gap: 8px;
            }
            .btn-premium:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(27, 64, 142, 0.4); filter: brightness(1.1); color: white !important; }
            
            /* Navbar Styles - Moved from component to layout to prevent re-render loss */
            .public-navbar {
                background: rgba(var(--bs-body-bg-rgb), 0.85);
                backdrop-filter: blur(12px) saturate(180%);
                -webkit-backdrop-filter: blur(12px) saturate(180%);
                border-bottom: 1px solid rgba(var(--bs-primary-rgb), 0.1);
                transition: all 0.3s ease;
            }
            [data-bs-theme="dark"] .public-navbar {
                background: rgba(30, 34, 39, 0.85);
                border-bottom-color: rgba(255, 255, 255, 0.05);
            }
            .btn-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; }
            .btn-ghost-secondary:hover { background: rgba(var(--bs-secondary-rgb), 0.1); color: var(--bs-primary); }
        </style>
    </head>
    <body class="bg-body"
          x-data="appLayout()"
          x-init="init()">
        
        @livewire('public-navbar')

        <div class="min-vh-100">
            {{ $slot }}
        </div>

        @livewireScripts

        <script>
            // Simple Theme Logic for Public Page
            const THEME_KEY = 'app.theme';
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
            const applyTheme = (theme) => {
                const resolvedTheme = theme === 'system' ? (prefersDark.matches ? 'dark' : 'light') : theme;
                document.documentElement.setAttribute('data-bs-theme', resolvedTheme);
            };
            applyTheme(localStorage.getItem(THEME_KEY) || 'system');

            // Set Primary Theme Colors
            (function() {
                const root = document.documentElement;
                const selectedTheme = {
                    base: '#1B408E', dark: '#102A70', light: '#4361EE', lighter: '#A0B1F9', rgb: '27, 64, 142'
                };
                root.style.setProperty('--bs-primary', selectedTheme.base);
                root.style.setProperty('--bs-primary-rgb', selectedTheme.rgb);
                root.style.setProperty('--theme-primary', selectedTheme.base);
                root.style.setProperty('--theme-primary-dark', selectedTheme.dark);
                root.style.setProperty('--theme-primary-light', selectedTheme.light);
                root.style.setProperty('--theme-primary-lighter', selectedTheme.lighter);
                root.style.setProperty('--theme-primary-rgb', selectedTheme.rgb);
            })();
        </script>

        <!-- Sistema Global de Tratamento de Erro 419 - CSRF/Session Expired (Public) -->
        <script>
            (function() {
                'use strict';

                const LOGIN_URL = '{{ route("login") }}';
                let isRedirecting = false;

                function redirectToLogin() {
                    if (isRedirecting) return;
                    isRedirecting = true;
                    localStorage.setItem('session_expired', 'true');
                    window.location.href = LOGIN_URL;
                }

                // Intercepta fetch
                const originalFetch = window.fetch;
                window.fetch = async function(...args) {
                    try {
                        const response = await originalFetch.apply(this, args);
                        if (response.status === 419) {
                            redirectToLogin();
                        }
                        return response;
                    } catch (error) {
                        throw error;
                    }
                };

                // Intercepta XHR
                const originalXHRSend = XMLHttpRequest.prototype.send;
                XMLHttpRequest.prototype.send = function(...args) {
                    this.addEventListener('load', function() {
                        if (this.status === 419) {
                            redirectToLogin();
                        }
                    });
                    return originalXHRSend.apply(this, args);
                };

                // Listener Livewire
                document.addEventListener('livewire:init', function() {
                    if (window.Livewire) {
                        Livewire.hook('request', ({ fail }) => {
                            fail(({ status, preventDefault }) => {
                                if (status === 419) {
                                    preventDefault();
                                    redirectToLogin();
                                }
                            });
                        });
                    }
                });
            })();
        </script>
    </body>
</html>
