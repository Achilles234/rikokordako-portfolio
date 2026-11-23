
/*
|--------------------------------------------------------------------------
| FAMILY PORTAL - THEME & SIDEBAR CONTROLLER
|--------------------------------------------------------------------------
*/

(() => {
    'use strict';

    const root = document.documentElement;
    const body = document.body;

    /*
    |--------------------------------------------------------------------------
    | THEME CONTROLLER
    |--------------------------------------------------------------------------
    */

    const THEME_STORAGE_KEY = 'rk-family-theme';
    const themeColor = document.querySelector('[data-family-theme-color]');

    const applyTheme = (theme, persist = false) => {
        const selected = theme === 'light' ? 'light' : 'dark';

        root.classList.toggle('family-theme-light', selected === 'light');
        root.classList.toggle('family-theme-dark', selected === 'dark');

        root.style.colorScheme = selected;

        if (themeColor) {
            themeColor.setAttribute(
                'content',
                selected === 'light' ? '#E8DDC8' : '#17120F'
            );
        }

        document.querySelectorAll('[data-family-theme-toggle]').forEach((button) => {
            button.setAttribute('aria-pressed', String(selected === 'light'));
            button.setAttribute(
                'title',
                selected === 'light' ? 'Gunakan tema gelap' : 'Gunakan tema terang'
            );
        });

        if (persist) {
            try {
                localStorage.setItem(THEME_STORAGE_KEY, selected);
            } catch (error) {}
        }
    };

    let initialTheme = 'dark';

    try {
        initialTheme = localStorage.getItem(THEME_STORAGE_KEY) === 'light'
            ? 'light'
            : 'dark';
    } catch (error) {}

    applyTheme(initialTheme);

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) return;

        const button = event.target.closest('[data-family-theme-toggle]');
        if (!button) return;

        const currentlyLight = root.classList.contains('family-theme-light');

        applyTheme(currentlyLight ? 'dark' : 'light', true);
    });

    /*
    |--------------------------------------------------------------------------
    | SIDEBAR PIN CONTROLLER
    |--------------------------------------------------------------------------
    */

    const PIN_STORAGE_KEY = 'rk-family-sidebar-pinned';
    const sidebar = document.querySelector('[data-family-sidebar]');

    if (!sidebar) return;

    const desktop = window.matchMedia('(min-width: 1024px)');
    const PEEK_EDGE = 24;

    const closePeek = () => {
        body.classList.remove('family-sidebar-peek');
    };

    const openPeek = () => {
        if (!desktop.matches) return;
        if (sidebar.dataset.sidebarPinned === 'true') return;

        body.classList.add('family-sidebar-peek');
    };

    const applyPinState = (pinned, persist = false) => {
        sidebar.dataset.sidebarPinned = String(pinned);

        root.classList.toggle('family-sidebar-pinned', pinned);
        root.classList.toggle('family-sidebar-unpinned', !pinned);

        if (pinned) closePeek();

        document.querySelectorAll('[data-family-sidebar-pin]').forEach((button) => {
            button.setAttribute('aria-pressed', String(pinned));
            button.setAttribute(
                'title',
                pinned ? 'Matikan pin sidebar' : 'Pin sidebar'
            );
            button.setAttribute(
                'aria-label',
                pinned ? 'Matikan pin sidebar' : 'Pin sidebar'
            );
        });

        if (persist) {
            try {
                localStorage.setItem(PIN_STORAGE_KEY, String(pinned));
            } catch (error) {}
        }
    };

    let initialPinned = true;

    try {
        initialPinned = localStorage.getItem(PIN_STORAGE_KEY) !== 'false';
    } catch (error) {}

    applyPinState(initialPinned);

    // PIN / UNPIN
    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) return;

        const button = event.target.closest('[data-family-sidebar-pin]');
        if (!button) return;

        const currentlyPinned =
            sidebar.dataset.sidebarPinned !== 'false';

        applyPinState(!currentlyPinned, true);

        if (currentlyPinned) openPeek();
    });

    // Peek saat pointer menyentuh tepi kiri
    document.addEventListener('pointermove', (event) => {
        if (!desktop.matches || event.pointerType === 'touch') return;
        if (sidebar.dataset.sidebarPinned === 'true') return;

        if (event.clientX <= PEEK_EDGE) {
            openPeek();
        }
    });

    // Tutup saat pointer keluar sidebar
    sidebar.addEventListener('mouseleave', () => {
        if (sidebar.dataset.sidebarPinned === 'false') {
            closePeek();
        }
    });

    // Tutup dengan Escape
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closePeek();
    });

    desktop.addEventListener('change', closePeek);
})();
