const toggle = document.querySelector('[data-menu-toggle]');
const drawer = document.querySelector('[data-nav-drawer]');
const loadMore = document.querySelector('[data-load-more]');
const feedStatus = document.querySelector('[data-feed-status]');

function setDrawerOpen(open) {
    if (!toggle || !drawer) {
        return;
    }

    drawer.classList.toggle('is-open', open);
    drawer.hidden = !open;
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
}

if (toggle && drawer) {
    toggle.addEventListener('click', () => {
        setDrawerOpen(!drawer.classList.contains('is-open'));
    });

    drawer.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setDrawerOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && drawer.classList.contains('is-open')) {
            setDrawerOpen(false);
            toggle.focus();
        }
    });
}

if (loadMore && feedStatus) {
    loadMore.addEventListener('click', () => {
        loadMore.disabled = true;
        loadMore.setAttribute('aria-busy', 'true');
        feedStatus.textContent = 'Loading more stories…';

        window.setTimeout(() => {
            loadMore.disabled = false;
            loadMore.removeAttribute('aria-busy');
            feedStatus.textContent =
                'Prototype only: no additional articles to load.';
        }, 700);
    });
}
