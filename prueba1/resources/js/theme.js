/**
 * Alternancia entre modo claro y oscuro.
 *
 * El modo claro es el predeterminado. El tema se controla con una única
 * clase en el elemento <html>: "church-light" o "church-dark". Ambas se
 * definen en resources/css/church-theme.css.
 *
 * La preferencia se guarda en localStorage. Para evitar el parpadeo inicial
 * (flash) cada layout incluye un pequeño script en línea en el <head> que
 * aplica la clase correcta antes del primer render.
 */
const STORAGE_KEY = 'church-theme';
const LIGHT_CLASS = 'church-light';
const DARK_CLASS = 'church-dark';

function applyTheme(theme) {
    const isLight = theme === 'light';

    document.documentElement.classList.toggle(LIGHT_CLASS, isLight);
    document.documentElement.classList.toggle(DARK_CLASS, !isLight);

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', isLight ? 'true' : 'false');
        button.setAttribute(
            'title',
            isLight ? 'Cambiar a modo oscuro' : 'Cambiar a modo claro'
        );
    });
}

export function initTheme() {
    let theme = 'light';

    try {
        theme = localStorage.getItem(STORAGE_KEY) || 'light';
    } catch (e) {
        // localStorage no disponible (modo privado, etc.)
    }

    applyTheme(theme);

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const next = document.documentElement.classList.contains(LIGHT_CLASS)
                ? 'dark'
                : 'light';

            try {
                localStorage.setItem(STORAGE_KEY, next);
            } catch (e) {
                // Ignorar si no se puede persistir la preferencia.
            }

            applyTheme(next);
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
} else {
    initTheme();
}
