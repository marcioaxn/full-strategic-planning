import './bootstrap';
import './session-timer';

import mask from '@alpinejs/mask';
import focus from '@alpinejs/focus';
import Tooltip from 'bootstrap/js/dist/tooltip';
import Toast from 'bootstrap/js/dist/toast';

// Make Bootstrap components globally accessible for inline scripts like Alpine.js
window.bootstrap = {
    Tooltip: Tooltip,
    Toast: Toast
};

window.deferLoadingAlpine = true;

Alpine.plugin(mask);
Alpine.plugin(focus);

window.Alpine = Alpine;

let alpineStarted = false;

const startAlpine = () => {
    if (alpineStarted) {
        return;
    }

    Alpine.start();
    alpineStarted = true;
    console.debug('[app.js] Alpine started');
};

const THEME_KEY = 'app.theme';
const SIDEBAR_KEY = 'appSidebarCollapsed';
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');

const getStoredTheme = () => localStorage.getItem(THEME_KEY) ?? 'system';

const applyTheme = (theme) => {
    const resolvedTheme = theme === 'system'
        ? (prefersDark.matches ? 'dark' : 'light')
        : theme;

    document.documentElement.setAttribute('data-bs-theme', resolvedTheme);
    document.dispatchEvent(new CustomEvent('app-theme-changed', { detail: { theme, resolvedTheme } }));
};

const initTooltips = (scope = document) => {
    console.debug('[app.js] initTooltips()', scope);

    scope.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
        const existing = Tooltip.getInstance(element);
        if (existing) {
            existing.dispose();
        }

        const trigger = element.dataset.bsTrigger ?? 'hover focus';
        const tooltip = new Tooltip(element, {
            trigger,
            delay: { show: 150, hide: 100 },
        });

        console.debug('[app.js] tooltip initialised', element);
    });
};

const initToasts = (scope = document) => {
    console.debug('[app.js] initToasts()', scope);

    scope.querySelectorAll('.toast').forEach((element) => {
        const existing = Toast.getInstance(element);
        if (existing) {
            existing.dispose();
        }

        const toast = new Toast(element);
        const shouldAutoShow = element.dataset.bsAutoshow === 'true';

        if (shouldAutoShow && element.dataset.toastShown !== 'true') {
            toast.show();
            element.dataset.toastShown = 'true';
        }
    });
};

window.appLayout = function () {
    return {
        sidebarCollapsed: JSON.parse(localStorage.getItem(SIDEBAR_KEY) ?? 'false'),
        theme: getStoredTheme(),
        get themeLabel() {
            return {
                light: 'Light',
                dark: 'Dark',
                system: 'System',
            }[this.theme] ?? 'System';
        },
        get themeIcon() {
            return {
                light: 'bi-brightness-high',
                dark: 'bi-moon-stars',
                system: 'bi-circle-half',
            }[this.theme] ?? 'bi-circle-half';
        },
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem(SIDEBAR_KEY, JSON.stringify(this.sidebarCollapsed));
            document.dispatchEvent(new CustomEvent('app-sidebar-toggled', { detail: { collapsed: this.sidebarCollapsed } }));
        },
        openSidebar() {
            this.sidebarCollapsed = false;
            localStorage.setItem(SIDEBAR_KEY, JSON.stringify(this.sidebarCollapsed));
        },
        closeSidebar() {
            this.sidebarCollapsed = true;
            localStorage.setItem(SIDEBAR_KEY, JSON.stringify(this.sidebarCollapsed));
        },
        cycleTheme() {
            this.theme = this.theme === 'light' ? 'dark' : this.theme === 'dark' ? 'system' : 'light';
            localStorage.setItem(THEME_KEY, this.theme);
            applyTheme(this.theme);

            // Update and hide tooltip
            const themeSwitcher = document.getElementById('appThemeSwitcher');
            if (themeSwitcher) {
                const tooltipInstance = Tooltip.getInstance(themeSwitcher);
                if (tooltipInstance) {
                    // Update content. Bootstrap 5 uses setContent
                    tooltipInstance.setContent({ '.tooltip-inner': this.themeLabel });
                    // Hide after a short delay to allow the user to see the updated text
                    setTimeout(() => {
                        tooltipInstance.hide();
                    }, 500); // 500ms delay to see the updated tooltip content
                }
            }
        },
        init() {
            applyTheme(this.theme);
            prefersDark.addEventListener('change', () => {
                if (this.theme === 'system') {
                    applyTheme(this.theme);
                }
            });
            initTooltips();
        },
    };
};

applyTheme(getStoredTheme());

document.addEventListener('DOMContentLoaded', () => {
    console.debug('[app.js] DOMContentLoaded');

    if (!window.Livewire) {
        startAlpine();
    }

    initTooltips();
    initToasts();
});

document.addEventListener('app-sidebar-toggled', () => {
    console.debug('[app.js] app-sidebar-toggled');
    initTooltips();
    initToasts();
});

document.addEventListener('livewire:load', () => {
    console.debug('[app.js] livewire:load');

    startAlpine();
    initTooltips();
    initToasts();

    Livewire.hook('message.processed', (message, component) => {
        console.debug('[app.js] Livewire message.processed', component.id);
        initTooltips(component.el);
        initToasts(component.el);
    });

    Livewire.on('refresh-navigation-menu', () => initTooltips());
    Livewire.on('saved', () => initTooltips());
});

document.addEventListener('livewire:navigated', () => {
    console.debug('[app.js] livewire:navigated');
    initTooltips();
    initToasts();
});
