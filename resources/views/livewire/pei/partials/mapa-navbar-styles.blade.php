<style>
    /* Public Navbar - Suporte Dark Mode */
    .public-navbar {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid var(--bs-border-color);
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    [data-bs-theme="dark"] .public-navbar {
        background: rgba(33, 37, 41, 0.95);
        border-bottom-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.3);
    }

    .brand-text-primary {
        color: var(--bs-primary);
        font-weight: 700;
    }

    .brand-text-secondary {
        color: var(--bs-secondary);
    }

    [data-bs-theme="dark"] .brand-text-secondary {
        color: rgba(255, 255, 255, 0.6);
    }

    .nav-link-public {
        color: var(--bs-body-color);
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .nav-link-public:hover {
        background: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
    }

    .nav-link-public.active {
        color: var(--bs-primary);
    }

    [data-bs-theme="dark"] .nav-link-public {
        color: rgba(255, 255, 255, 0.85);
    }

    [data-bs-theme="dark"] .nav-link-public:hover {
        background: rgba(255, 255, 255, 0.1);
        color: var(--bs-primary);
    }

    .btn-theme-toggle {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        border: 1px solid var(--bs-border-color);
        background: transparent;
        color: var(--bs-body-color);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .btn-theme-toggle:hover {
        background: rgba(var(--bs-primary-rgb), 0.1);
        border-color: var(--bs-primary);
        color: var(--bs-primary);
    }

    .btn-theme-toggle::after {
        display: none;
    }

    [data-bs-theme="dark"] .btn-theme-toggle {
        border-color: rgba(255, 255, 255, 0.2);
        color: rgba(255, 255, 255, 0.85);
    }

    [data-bs-theme="dark"] .btn-theme-toggle:hover {
        background: rgba(255, 255, 255, 0.1);
        color: var(--bs-primary);
    }

    .navbar-toggler {
        color: var(--bs-body-color);
    }

    [data-bs-theme="dark"] .navbar-toggler {
        color: rgba(255, 255, 255, 0.85);
    }

    .icon-shape {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
    }
</style>

<script>
    function setTheme(theme) {
        const THEME_KEY = 'app.theme';
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');

        localStorage.setItem(THEME_KEY, theme);

        const resolvedTheme = theme === 'system'
            ? (prefersDark.matches ? 'dark' : 'light')
            : theme;

        document.documentElement.setAttribute('data-bs-theme', resolvedTheme);
        updateThemeIcon(theme);
    }

    function updateThemeIcon(theme) {
        const icon = document.getElementById('themeIcon');
        if (!icon) return;

        const icons = {
            light: 'bi-sun',
            dark: 'bi-moon-stars',
            system: 'bi-circle-half'
        };

        icon.className = `bi ${icons[theme] || icons.system} fs-5`;
    }

    // Initialize theme on page load
    document.addEventListener('DOMContentLoaded', function() {
        const THEME_KEY = 'app.theme';
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
        let currentTheme = localStorage.getItem(THEME_KEY) || 'system';

        const resolvedTheme = currentTheme === 'system'
            ? (prefersDark.matches ? 'dark' : 'light')
            : currentTheme;

        document.documentElement.setAttribute('data-bs-theme', resolvedTheme);
        updateThemeIcon(currentTheme);

        // Listen for system theme changes
        prefersDark.addEventListener('change', function() {
            if (localStorage.getItem(THEME_KEY) === 'system') {
                document.documentElement.setAttribute('data-bs-theme', prefersDark.matches ? 'dark' : 'light');
            }
        });
    });
</script>
