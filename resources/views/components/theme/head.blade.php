<meta name="color-scheme" content="light dark">
<script>
    (() => {
        const storageKey = 'usnsoft-theme';
        const themeOptions = ['light', 'dark', 'system'];
        const root = document.documentElement;
        const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');

        const readStoredTheme = () => {
            try {
                const theme = window.localStorage.getItem(storageKey);

                return themeOptions.includes(theme) ? theme : 'system';
            } catch (error) {
                return 'system';
            }
        };

        const theme = readStoredTheme();
        const effectiveTheme = theme === 'system'
            ? (prefersDarkScheme.matches ? 'dark' : 'light')
            : theme;

        root.classList.toggle('dark', effectiveTheme === 'dark');
        root.dataset.theme = theme;
        root.dataset.themeEffective = effectiveTheme;
        root.style.colorScheme = effectiveTheme;
    })();
</script>
