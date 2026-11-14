
/*
|--------------------------------------------------------------------------
| Riko Kordako - Theme Controller
|--------------------------------------------------------------------------
*/

(() => {
    'use strict';

    const THEME_STORAGE_KEY = 'rk-theme';
    const root = document.documentElement;

    const themeColors = {
        dark: '#0B1120',
        light: '#F8FAFC',
    };

    /*
    |--------------------------------------------------------------------------
    | Apply Theme
    |--------------------------------------------------------------------------
    */

    const applyTheme = (theme, persist = false) => {
        const selectedTheme = theme === 'light' ? 'light' : 'dark';

        root.classList.toggle('light', selectedTheme === 'light');
        root.style.colorScheme = selectedTheme;

        const themeColor = document.querySelector(
            'meta[name="theme-color"]'
        );

        if (themeColor) {
            themeColor.setAttribute(
                'content',
                themeColors[selectedTheme]
            );
        }

        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            const nextTheme = selectedTheme === 'dark' ? 'light' : 'dark';

            const label = nextTheme === 'light'
                ? button.dataset.themeLabelLight
                : button.dataset.themeLabelDark;

            if (label) {
                button.setAttribute('aria-label', label);
                button.setAttribute('title', label);
            }

            button.setAttribute(
                'aria-pressed',
                String(selectedTheme === 'light')
            );
        });

        if (persist) {
            try {
                localStorage.setItem(
                    THEME_STORAGE_KEY,
                    selectedTheme
                );
            } catch (error) {
                // Theme remains functional if storage is unavailable.
            }
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Initialize Theme
    |--------------------------------------------------------------------------
    */

    const initialTheme = root.classList.contains('light')
        ? 'light'
        : 'dark';

    applyTheme(initialTheme);

    /*
    |--------------------------------------------------------------------------
    | Theme Toggle Interaction
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', (event) => {

        if (!(event.target instanceof Element)) {
            return;
        }

        const toggle = event.target.closest('[data-theme-toggle]');

        if (!toggle) {
            return;
        }

        const currentTheme = root.classList.contains('light')
            ? 'light'
            : 'dark';

        const nextTheme = currentTheme === 'dark'
            ? 'light'
            : 'dark';

        applyTheme(nextTheme, true);
    });

})();
