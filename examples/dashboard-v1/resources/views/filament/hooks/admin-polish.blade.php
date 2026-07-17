<style>
    /* Admin polish: restrained amber product register */
    .fi-simple-layout {
        background-color: oklch(0.97 0.008 75);
        background-image: radial-gradient(
            ellipse 120% 80% at 50% -20%,
            oklch(0.92 0.04 75 / 0.35),
            transparent 60%
        );
    }

    .dark .fi-simple-layout {
        background-color: oklch(0.16 0.012 75);
        background-image: radial-gradient(
            ellipse 100% 60% at 50% -10%,
            oklch(0.28 0.05 75 / 0.2),
            transparent 55%
        );
    }

    .fi-simple-main {
        box-shadow:
            0 1px 2px oklch(0.2 0.02 75 / 0.06),
            0 12px 40px oklch(0.2 0.02 75 / 0.08);
    }

    .fi-sidebar-nav-group-label {
        font-size: 0.625rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .fi-ta-header-cell-label {
        font-weight: 600;
        letter-spacing: 0.01em;
    }

    .fi-wi-stats-overview-stat {
        transition: box-shadow 180ms cubic-bezier(0.22, 1, 0.36, 1);
    }

    .fi-wi-stats-overview-stat:hover {
        box-shadow: 0 4px 16px oklch(0.2 0.02 75 / 0.08);
    }
</style>
