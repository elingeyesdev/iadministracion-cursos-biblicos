/**
 * Menú lateral: expandido / contraído (estilo ChatGPT) en escritorio.
 *
 * El estado se controla con la clase "sidebar-collapsed" en <html> y se
 * persiste en localStorage (misma mecánica que usa el tema; ver theme.js).
 * En pantallas pequeñas el menú sigue siendo el offcanvas de Bootstrap, por
 * lo que el estado contraído solo se aplica a partir de 992px (el CSS lo
 * limita con una media query).
 *
 * Tooltips: al estar contraído, cada icono muestra el nombre de su opción.
 * Se generan a partir del texto del propio enlace para no duplicar datos.
 */
import { Tooltip, Offcanvas } from 'bootstrap';

const STORAGE_KEY = 'church-sidebar';
const COLLAPSED_CLASS = 'sidebar-collapsed';
const DESKTOP_QUERY = window.matchMedia('(min-width: 992px)');

let tooltips = [];

function isCollapsed() {
    return document.documentElement.classList.contains(COLLAPSED_CLASS);
}

function isDesktop() {
    return DESKTOP_QUERY.matches;
}

/** Texto de la opción sin iconos ni elementos decorativos. */
function labelFor(element) {
    if (element.dataset.sidebarTooltip) {
        return element.dataset.sidebarTooltip.trim();
    }

    const clone = element.cloneNode(true);
    clone
        .querySelectorAll('i, .sidebar-caret, .badge, .dropdown-toggle')
        .forEach((node) => node.remove());

    return clone.textContent.replace(/\s+/g, ' ').trim();
}

function destroyTooltips() {
    tooltips.forEach((instance) => instance.dispose());
    tooltips = [];
}

/** Crea (o elimina) los tooltips según el estado y el tamaño de pantalla. */
function refreshTooltips() {
    destroyTooltips();

    if (!isCollapsed() || !isDesktop()) {
        return;
    }

    document
        .querySelectorAll('.sidebar .nav-link, .sidebar .user-menu-toggle')
        .forEach((link) => {
            const label = labelFor(link);

            if (!label) {
                return;
            }

            tooltips.push(
                new Tooltip(link, {
                    title: label,
                    placement: 'right',
                    container: 'body',
                    trigger: 'hover',
                    fallbackPlacements: ['left'],
                })
            );
        });
}

/** Sincroniza el botón (icono, tooltip y accesibilidad) con el estado. */
function updateToggles() {
    const collapsed = isCollapsed();

    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        const label = collapsed ? 'Expandir menú' : 'Contraer menú';

        button.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
        button.setAttribute('title', label);
        button.setAttribute('aria-label', label);

        const icon = button.querySelector('i');

        if (icon) {
            icon.classList.toggle('bi-chevron-double-left', !collapsed);
            icon.classList.toggle('bi-chevron-double-right', collapsed);
        }
    });
}

function setCollapsed(collapsed) {
    // Contraer carece de sentido en móvil (ahí manda el offcanvas).
    if (collapsed && !isDesktop()) {
        return;
    }

    document.documentElement.classList.toggle(COLLAPSED_CLASS, collapsed);

    try {
        localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
    } catch (e) {
        // localStorage no disponible (modo privado, etc.).
    }

    updateToggles();
    refreshTooltips();
}

export function initSidebar() {
    // El estado inicial de la clase lo aplica el script del <head>
    // (layouts/partials/sidebar-script.blade.php) para evitar parpadeos.
    updateToggles();
    refreshTooltips();

    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            setCollapsed(!isCollapsed());
        });
    });

    // Estando contraído, pulsar una sección plegable expande el menú.
    document.addEventListener(
        'click',
        (event) => {
            const section = event.target.closest('.sidebar-section-toggle');

            if (section && isCollapsed() && isDesktop()) {
                setCollapsed(false);
            }
        },
        true
    );

    // En móvil, al elegir una opción de navegación se cierra el panel.
    document
        .querySelectorAll('#sidebarOffcanvas .nav-link[href]')
        .forEach((link) => {
            if (link.getAttribute('href') === '#') {
                return;
            }

            link.addEventListener('click', () => {
                const offcanvas = document.getElementById('sidebarOffcanvas');

                if (!offcanvas) {
                    return;
                }

                // Se prefiere la instancia del bundle del CDN (la que abre el
                // panel mediante data-bs-toggle); si no está, se usa la de npm.
                const OffcanvasCtor =
                    (window.bootstrap && window.bootstrap.Offcanvas) || Offcanvas;

                OffcanvasCtor.getOrCreateInstance(offcanvas).hide();
            });
        });

    // Al cambiar entre escritorio y móvil, reajustar tooltips y estado.
    DESKTOP_QUERY.addEventListener('change', () => {
        updateToggles();
        refreshTooltips();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSidebar);
} else {
    initSidebar();
}
