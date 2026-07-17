<script>
    (function () {
        const MODES = ['light', 'dark', 'system'];
        const stored = localStorage.getItem('theme');
        const theme = MODES.includes(stored) ? stored : 'system';
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isDark = theme === 'dark' || (theme === 'system' && prefersDark);

        document.documentElement.classList.toggle('dark', isDark);
    })();
</script>
