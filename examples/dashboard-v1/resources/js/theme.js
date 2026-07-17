const STORAGE_KEY = 'theme';
const MODES = ['light', 'dark', 'system'];

function resolveIsDark(mode) {
    if (mode === 'dark') {
        return true;
    }

    if (mode === 'light') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

export function applyTheme(mode) {
    document.documentElement.classList.toggle('dark', resolveIsDark(mode));
}

export function getStoredMode() {
    const stored = localStorage.getItem(STORAGE_KEY);

    return MODES.includes(stored) ? stored : 'system';
}

export function setTheme(mode) {
    if (! MODES.includes(mode)) {
        return;
    }

    localStorage.setItem(STORAGE_KEY, mode);
    applyTheme(mode);

    if (window.Alpine?.store('theme')) {
        window.Alpine.store('theme').mode = mode;
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        mode: getStoredMode(),

        set(mode) {
            this.mode = mode;
            setTheme(mode);
        },

        isDark() {
            return resolveIsDark(this.mode);
        },
    });

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (Alpine.store('theme').mode === 'system') {
            applyTheme('system');
        }
    });

    window.addEventListener('storage', (event) => {
        if (event.key !== STORAGE_KEY || ! MODES.includes(event.newValue)) {
            return;
        }

        Alpine.store('theme').mode = event.newValue;
        applyTheme(event.newValue);
    });
});
