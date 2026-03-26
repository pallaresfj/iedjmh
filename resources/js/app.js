const initAutoFilters = () => {
    const forms = document.querySelectorAll('[data-auto-filter-form]');
    const isPrimaryClick = (event) => event.button === 0
        && !event.metaKey
        && !event.ctrlKey
        && !event.shiftKey
        && !event.altKey;

    forms.forEach((form) => {
        if (form.dataset.autoFilterInitialized === '1') {
            return;
        }

        form.dataset.autoFilterInitialized = '1';

        let debounceTimer;
        let activeController;

        const buildRequestUrl = () => {
            const url = new URL(form.action, window.location.origin);
            const params = new URLSearchParams();

            new FormData(form).forEach((value, key) => {
                if (typeof value !== 'string') {
                    return;
                }

                const trimmedValue = value.trim();

                if (trimmedValue === '') {
                    return;
                }

                params.append(key, trimmedValue);
            });

            url.search = params.toString();

            return url;
        };

        const requestAndSwap = async (requestUrl) => {
            const targetSelector = form.dataset.autoFilterTarget;

            if (!targetSelector) {
                return;
            }

            const currentTarget = document.querySelector(targetSelector);

            if (!currentTarget) {
                window.location.assign(requestUrl.toString());

                return;
            }

            if (activeController) {
                activeController.abort();
            }

            const controller = new AbortController();
            activeController = controller;

            try {
                const response = await fetch(requestUrl.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error('Request failed.');
                }

                const html = await response.text();
                const documentParser = new DOMParser();
                const responseDocument = documentParser.parseFromString(html, 'text/html');
                const nextTarget = responseDocument.querySelector(targetSelector);

                if (!nextTarget) {
                    throw new Error('Target not found in response.');
                }

                currentTarget.replaceWith(nextTarget);
                window.history.replaceState({}, '', requestUrl);
            } catch (error) {
                if (error instanceof DOMException && error.name === 'AbortError') {
                    return;
                }

                window.location.assign(requestUrl.toString());
            } finally {
                if (activeController === controller) {
                    activeController = undefined;
                }
            }
        };

        const submitFilters = async () => {
            const requestUrl = buildRequestUrl();

            await requestAndSwap(requestUrl);
        };

        const handlePaginationClick = (event) => {
            if (event.defaultPrevented || !isPrimaryClick(event)) {
                return;
            }

            if (!(event.target instanceof Element)) {
                return;
            }

            const link = event.target.closest('a[data-public-pagination-link]');

            if (!link) {
                return;
            }

            if (link.target && link.target !== '_self') {
                return;
            }

            if (link.hasAttribute('download')) {
                return;
            }

            const targetSelector = form.dataset.autoFilterTarget;

            if (!targetSelector) {
                return;
            }

            const currentTarget = document.querySelector(targetSelector);

            if (!currentTarget || !currentTarget.contains(link)) {
                return;
            }

            const href = link.getAttribute('href');

            if (!href) {
                return;
            }

            event.preventDefault();
            window.clearTimeout(debounceTimer);
            requestAndSwap(new URL(href, window.location.origin));
        };

        document.addEventListener('click', handlePaginationClick);

        form.querySelectorAll('input[type="text"], input[type="search"]').forEach((input) => {
            input.addEventListener('input', () => {
                window.clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(submitFilters, 350);
            });
        });

        form.querySelectorAll('select').forEach((select) => {
            select.addEventListener('change', submitFilters);
        });

        form.querySelectorAll('[data-auto-filter-clear]').forEach((clearAction) => {
            clearAction.addEventListener('click', (event) => {
                event.preventDefault();

                window.clearTimeout(debounceTimer);

                form.querySelectorAll('input[type="text"], input[type="search"]').forEach((input) => {
                    input.value = '';
                });

                form.querySelectorAll('select').forEach((select) => {
                    const hasEmptyOption = Array.from(select.options).some((option) => option.value === '');

                    if (hasEmptyOption) {
                        select.value = '';

                        return;
                    }

                    select.selectedIndex = 0;
                });

                submitFilters();
            });
        });
    });
};

const PUBLIC_THEME_STORAGE_KEY = 'ied_public_theme';
const LEGACY_HOME_THEME_STORAGE_KEY = 'ied_public_home_theme';

const isValidPublicTheme = (value) => value === 'light' || value === 'dark';

const getSystemPublicTheme = () => (
    window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
);

const getStoredPublicTheme = () => {
    try {
        const storedTheme = localStorage.getItem(PUBLIC_THEME_STORAGE_KEY);
        const legacyTheme = localStorage.getItem(LEGACY_HOME_THEME_STORAGE_KEY);

        if (isValidPublicTheme(storedTheme)) {
            return storedTheme;
        }

        return isValidPublicTheme(legacyTheme) ? legacyTheme : null;
    } catch (error) {
        return null;
    }
};

const setStoredPublicTheme = (theme) => {
    try {
        localStorage.setItem(PUBLIC_THEME_STORAGE_KEY, theme);
        localStorage.setItem(LEGACY_HOME_THEME_STORAGE_KEY, theme);
    } catch (error) {
        // Ignore storage failures and keep runtime theme.
    }
};

const updatePublicThemeToggleUi = (theme) => {
    const isDarkTheme = theme === 'dark';
    const iconName = isDarkTheme ? 'light_mode' : 'dark_mode';
    const buttonLabel = isDarkTheme ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro';

    document.querySelectorAll('[data-public-theme-toggle]').forEach((button) => {
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        button.setAttribute('aria-pressed', isDarkTheme ? 'true' : 'false');
        button.setAttribute('aria-label', buttonLabel);

        const icon = button.querySelector('[data-public-theme-toggle-icon]');

        if (icon) {
            icon.textContent = iconName;
        }
    });
};

const setPublicTheme = (theme, persist = true) => {
    if (!isValidPublicTheme(theme)) {
        return;
    }

    document.documentElement.setAttribute('data-public-theme', theme);
    // Backward compatibility for existing home-scoped selectors.
    document.documentElement.setAttribute('data-home-theme', theme);
    updatePublicThemeToggleUi(theme);

    if (persist) {
        setStoredPublicTheme(theme);
    }
};

const initPublicThemeToggle = () => {
    const storedTheme = getStoredPublicTheme();
    const runtimeTheme = document.documentElement.getAttribute('data-public-theme');
    const initialTheme = isValidPublicTheme(runtimeTheme)
        ? runtimeTheme
        : (storedTheme ?? getSystemPublicTheme());

    setPublicTheme(initialTheme, false);

    document.querySelectorAll('[data-public-theme-toggle]').forEach((button) => {
        if (!(button instanceof HTMLButtonElement) || button.dataset.publicThemeInitialized === '1') {
            return;
        }

        button.dataset.publicThemeInitialized = '1';

        button.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-public-theme') === 'dark' ? 'dark' : 'light';
            const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';

            setPublicTheme(nextTheme);
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initAutoFilters();
        initPublicThemeToggle();
    }, { once: true });
} else {
    initAutoFilters();
    initPublicThemeToggle();
}
