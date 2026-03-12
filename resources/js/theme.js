const STORAGE_KEY = 'usnsoft-theme';
const THEME_OPTIONS = ['light', 'dark', 'system'];
const THEME_CHANGE_EVENT = 'usnsoft-theme:change';
const THEME_LABELS = {
    light: 'Light',
    dark: 'Dark',
    system: 'System',
};
const THEME_DESCRIPTIONS = {
    light: 'Bright surfaces with standard contrast.',
    dark: 'Low-glare surfaces for darker environments.',
    system: 'Match this browser or device setting.',
};

const prefersDarkScheme =
    typeof window !== 'undefined' && typeof window.matchMedia === 'function'
        ? window.matchMedia('(prefers-color-scheme: dark)')
        : null;

const normalizeTheme = (value) => (THEME_OPTIONS.includes(value) ? value : 'system');

const readStoredTheme = () => {
    try {
        return normalizeTheme(window.localStorage.getItem(STORAGE_KEY));
    } catch (error) {
        return 'system';
    }
};

const persistTheme = (theme) => {
    try {
        window.localStorage.setItem(STORAGE_KEY, theme);
    } catch (error) {
        // Local-only preference storage can fail in locked-down browsers.
    }
};

const resolveTheme = (theme) => {
    const normalizedTheme = normalizeTheme(theme);

    if (normalizedTheme === 'system') {
        return prefersDarkScheme?.matches ? 'dark' : 'light';
    }

    return normalizedTheme;
};

const applyTheme = (theme) => {
    const normalizedTheme = normalizeTheme(theme);
    const effectiveTheme = resolveTheme(normalizedTheme);
    const root = document.documentElement;

    root.classList.toggle('dark', effectiveTheme === 'dark');
    root.dataset.theme = normalizedTheme;
    root.dataset.themeEffective = effectiveTheme;
    root.style.colorScheme = effectiveTheme;

    return effectiveTheme;
};

const createThemeController = () => {
    let currentTheme = readStoredTheme();
    let effectiveTheme = applyTheme(currentTheme);
    const listeners = new Set();

    const notify = () => {
        const detail = {
            theme: currentTheme,
            effectiveTheme,
            isSystem: currentTheme === 'system',
        };

        listeners.forEach((listener) => listener(detail));
        window.dispatchEvent(new CustomEvent(THEME_CHANGE_EVENT, { detail }));
    };

    const setTheme = (theme) => {
        currentTheme = normalizeTheme(theme);
        persistTheme(currentTheme);
        effectiveTheme = applyTheme(currentTheme);
        notify();
    };

    const handleSystemThemeChange = () => {
        if (currentTheme !== 'system') {
            return;
        }

        effectiveTheme = applyTheme(currentTheme);
        notify();
    };

    const handleStorage = (event) => {
        if (event.key !== null && event.key !== STORAGE_KEY) {
            return;
        }

        currentTheme = readStoredTheme();
        effectiveTheme = applyTheme(currentTheme);
        notify();
    };

    if (prefersDarkScheme) {
        if (typeof prefersDarkScheme.addEventListener === 'function') {
            prefersDarkScheme.addEventListener('change', handleSystemThemeChange);
        } else {
            prefersDarkScheme.addListener(handleSystemThemeChange);
        }
    }

    window.addEventListener('storage', handleStorage);

    return {
        storageKey: STORAGE_KEY,
        labels: THEME_LABELS,
        descriptions: THEME_DESCRIPTIONS,
        themeOptions: [...THEME_OPTIONS],
        getTheme: () => currentTheme,
        getEffectiveTheme: () => effectiveTheme,
        resolveTheme,
        setTheme,
        onChange(callback) {
            listeners.add(callback);
            callback({
                theme: currentTheme,
                effectiveTheme,
                isSystem: currentTheme === 'system',
            });

            return () => {
                listeners.delete(callback);
            };
        },
    };
};

export const installThemeController = (Alpine) => {
    const controller = createThemeController();

    window.USNsoftTheme = controller;

    Alpine.store('theme', {
        currentTheme: controller.getTheme(),
        effectiveTheme: controller.getEffectiveTheme(),
        labels: THEME_LABELS,
    });

    const themeStore = Alpine.store('theme');

    controller.onChange(({ theme, effectiveTheme }) => {
        themeStore.currentTheme = theme;
        themeStore.effectiveTheme = effectiveTheme;
    });

    Alpine.data('themeToggle', (config = {}) => ({
        open: false,
        align: config.align ?? 'right',
        get currentTheme() {
            return this.$store.theme.currentTheme;
        },
        get effectiveTheme() {
            return this.$store.theme.effectiveTheme;
        },
        get currentThemeLabel() {
            return THEME_LABELS[this.currentTheme] ?? THEME_LABELS.system;
        },
        get effectiveThemeLabel() {
            return THEME_LABELS[this.effectiveTheme] ?? THEME_LABELS.light;
        },
        get triggerLabel() {
            return this.currentTheme === 'system'
                ? `Theme: ${this.currentThemeLabel} (${this.effectiveThemeLabel})`
                : `Theme: ${this.currentThemeLabel}`;
        },
        get triggerAriaLabel() {
            return `Appearance settings. Current theme: ${this.triggerLabel}.`;
        },
        get menuAlignmentClasses() {
            return this.align === 'left'
                ? 'start-0 origin-top-left'
                : 'end-0 origin-top-right';
        },
        isSelected(theme) {
            return this.currentTheme === theme;
        },
        labelFor(theme) {
            return THEME_LABELS[theme] ?? THEME_LABELS.system;
        },
        descriptionFor(theme) {
            return THEME_DESCRIPTIONS[theme] ?? THEME_DESCRIPTIONS.system;
        },
        setTheme(theme) {
            controller.setTheme(theme);
            this.open = false;
        },
        toggleMenu() {
            this.open = !this.open;
        },
    }));
};
