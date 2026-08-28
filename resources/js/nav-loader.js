/**
 * Overlay de navegación (isotipo de Aura) — mecanismo de feedback de carga #1.
 *
 * Norma documentada en `.claude/agents/ux-ui-designer.md` ("Feedback de carga"). Cubre
 * navegaciones de página completa (clic en un `<a>` interno, o el `submit` de un
 * `<form>` clásico que no es `wire:submit`). Los `wire:click`/`wire:submit` de Livewire
 * NO deben mostrar este overlay: para eso está el mecanismo #2 (loaders de botón).
 *
 * El overlay vive en `resources/views/components/app-layout.blade.php`
 * (`#nav-loading-overlay`). Si el elemento no existe en la página actual (ej. las
 * vistas de auth usan `guest-layout.blade.php`, que no lo incluye), esta función no
 * hace nada — es seguro importarla en todas las páginas.
 */

const SHOW_DELAY_MS = 200;
const SAFETY_TIMEOUT_MS = 10000;

export function initNavigationOverlay() {
    const overlay = document.getElementById('nav-loading-overlay');
    if (!overlay) {
        return;
    }

    let showTimer = null;
    let safetyTimer = null;

    function show() {
        overlay.classList.remove('invisible');
        // Fuerza un reflow entre quitar "invisible" y activar la opacidad: si ambos cambios
        // de clase ocurren en el mismo tick, el navegador puede saltarse la transición
        // CSS y el overlay aparecería de golpe en vez de con un fundido suave.
        void overlay.offsetWidth;
        overlay.classList.remove('opacity-0');
        overlay.classList.add('opacity-100');
    }

    function hide() {
        if (showTimer !== null) {
            window.clearTimeout(showTimer);
            showTimer = null;
        }
        if (safetyTimer !== null) {
            window.clearTimeout(safetyTimer);
            safetyTimer = null;
        }
        overlay.classList.add('invisible');
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');
    }

    function scheduleShow() {
        // Si ya hay una navegación en curso (ej. el usuario hizo doble clic en dos
        // enlaces), no reinicies el temporizador: mantén el primero.
        if (showTimer !== null || safetyTimer !== null) {
            return;
        }
        // Retardo antes de mostrarse: la mayoría de las navegaciones de esta app local
        // terminan en bastante menos de 200ms: sin este retardo, cada clic produciría un
        // parpadeo molesto. Solo se muestra si la navegación realmente está tardando.
        showTimer = window.setTimeout(show, SHOW_DELAY_MS);
        // Nunca debe quedarse colgado: si por lo que sea nunca llega `pageshow` (ej. la
        // navegación fue cancelada por el navegador, o el destino nunca dispara load),
        // este timeout de seguridad lo retira de todos modos.
        safetyTimer = window.setTimeout(hide, SAFETY_TIMEOUT_MS);
    }

    function isExternalOrSamePageAnchor(url) {
        if (url.origin !== window.location.origin) {
            return true;
        }
        // Mismo path/query, solo cambia el hash: es un scroll interno, no una recarga.
        return (
            url.pathname === window.location.pathname &&
            url.search === window.location.search &&
            url.hash !== ''
        );
    }

    function shouldSkipAnchor(anchor, event) {
        if (event.defaultPrevented) {
            return true;
        }
        // Solo clic primario, sin modificadores (Ctrl/Cmd/Shift/Alt abren en otra
        // pestaña/ventana; botón central también). event.button === 1 es el botón central.
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return true;
        }
        if (anchor.hasAttribute('download')) {
            return true;
        }
        if (anchor.target && anchor.target !== '_self') {
            return true;
        }

        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#')) {
            return true;
        }
        if (/^(mailto:|tel:|javascript:)/i.test(href)) {
            return true;
        }

        try {
            const url = new URL(href, window.location.href);
            if (isExternalOrSamePageAnchor(url)) {
                return true;
            }
        } catch (error) {
            // Href no parseable como URL: no arriesgues mostrar el overlay por algo que
            // no reconocemos como navegación real.
            return true;
        }

        return false;
    }

    function isLivewireManagedForm(form) {
        for (const attribute of form.attributes) {
            if (attribute.name.indexOf('wire:submit') === 0) {
                return true;
            }
        }
        return false;
    }

    document.addEventListener(
        'click',
        function (event) {
            const anchor = event.target.closest('a[href]');
            if (!anchor || anchor.closest('[data-no-nav-loading]')) {
                return;
            }
            if (shouldSkipAnchor(anchor, event)) {
                return;
            }
            scheduleShow();
        },
        true
    );

    document.addEventListener(
        'submit',
        function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || event.defaultPrevented) {
                return;
            }
            if (form.hasAttribute('data-no-nav-loading') || isLivewireManagedForm(form)) {
                return;
            }
            if (form.target && form.target !== '_self') {
                return;
            }
            scheduleShow();
        },
        true
    );

    // Nunca debe quedarse colgado (caso crítico): `pageshow` se dispara tanto en una
    // carga normal (event.persisted === false) como al volver por el botón "atrás" desde
    // el bfcache (event.persisted === true, el overlay podría haber quedado visible tal
    // cual estaba justo antes de navegar). Lo ocultamos en ambos casos por seguridad.
    window.addEventListener('pageshow', hide);
}
