/**
 * Loader de botones para formularios Blade clásicos — mecanismo de feedback de carga #2
 * (la mitad "no-Livewire"; la mitad Livewire vive como directivas `wire:loading` en
 * `resources/views/components/button.blade.php`).
 *
 * Norma documentada en `.claude/agents/ux-ui-designer.md` ("Feedback de carga"). Aplica
 * automáticamente a cualquier `<button type="submit">` (o sin `type`, que por defecto
 * también es submit) dentro de un `<form>` que NO sea `wire:submit` — no hace falta
 * marcar nada a mano para obtener el mínimo funcional (bloqueo de doble envío +
 * `aria-busy`); si el botón fue construido con `<x-button>`, además obtiene el
 * intercambio visual icono → spinner porque esos marcan `[data-button-icon]` /
 * `[data-button-spinner]`.
 *
 * Dos trampas reales que este archivo evita a propósito (no las reintroduzcas):
 *
 * 1. Deshabilitar el botón de forma SÍNCRONA dentro del evento `submit` hace que el
 *    navegador ya no incluya su par `name`/`value` al serializar el formulario (algunos
 *    formularios pueden depender de eso, ej. varios submits nombrados en un mismo
 *    formulario). Por eso `disabled` se aplica en un `setTimeout(fn, 0)`: para cuando
 *    corre, el navegador ya capturó los valores del formulario para la petición en
 *    curso. El bloqueo de doble envío durante esa ventana brevísima lo cubre el
 *    `event.preventDefault()` de más abajo (comprobando el propio atributo de carga).
 * 2. Si el usuario vuelve con el botón "atrás" y la página se restaura desde el
 *    bfcache, el DOM restaurado conserva el estado que tenía justo antes de navegar
 *    (botón deshabilitado, spinner visible, para siempre). `pageshow` se dispara tanto
 *    en una carga normal como en una restauración de bfcache — reseteamos siempre.
 */

const LOADING_ATTR = 'data-loading';

function isLivewireManagedForm(form) {
    for (const attribute of form.attributes) {
        if (attribute.name.indexOf('wire:submit') === 0) {
            return true;
        }
    }
    return false;
}

function setLoading(button) {
    if (button.getAttribute(LOADING_ATTR) === 'true') {
        return;
    }
    button.setAttribute(LOADING_ATTR, 'true');
    button.setAttribute('aria-busy', 'true');
    button.classList.add('opacity-70', 'cursor-wait');

    const icon = button.querySelector('[data-button-icon]');
    const spinner = button.querySelector('[data-button-spinner]');
    if (icon) {
        icon.classList.add('hidden');
    }
    if (spinner) {
        spinner.classList.remove('hidden');
    }

    // Trampa 1 (ver cabecera del archivo): disabled se aplica diferido, no aquí mismo.
    window.setTimeout(function () {
        button.disabled = true;
    }, 0);
}

function clearLoading(button) {
    button.removeAttribute(LOADING_ATTR);
    button.removeAttribute('aria-busy');
    button.disabled = false;
    button.classList.remove('opacity-70', 'cursor-wait');

    const icon = button.querySelector('[data-button-icon]');
    const spinner = button.querySelector('[data-button-spinner]');
    if (icon) {
        icon.classList.remove('hidden');
    }
    if (spinner) {
        spinner.classList.add('hidden');
    }
}

export function initButtonLoading() {
    document.addEventListener(
        'submit',
        function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || event.defaultPrevented) {
                return;
            }
            // Formulario gestionado por Livewire (wire:submit): su propio ciclo de vida
            // ya deshabilita/reactiva el botón vía wire:loading — no lo dupliques aquí.
            if (isLivewireManagedForm(form)) {
                return;
            }

            // SubmitEvent.submitter (soportado en navegadores evergreen) identifica el
            // botón exacto que disparó el envío. Si no está disponible, o el envío fue
            // programático sin un submitter visible (ej. `form.requestSubmit()` sin
            // argumento, como en el modal de confirmación de eliminar), no hay nada que
            // marcar como "cargando" aquí — ese caso lo cubre `<x-button alpine-loading>`.
            const submitter = event.submitter;
            if (!submitter || submitter.tagName !== 'BUTTON' || submitter.type !== 'submit') {
                return;
            }

            if (submitter.getAttribute(LOADING_ATTR) === 'true') {
                // Trampa 1 (ventana antes de que el disabled diferido surta efecto):
                // bloquea aquí un segundo envío accidental por doble clic muy rápido.
                event.preventDefault();
                return;
            }

            setLoading(submitter);
        },
        true
    );

    // Trampa 2 (ver cabecera del archivo).
    window.addEventListener('pageshow', function () {
        document.querySelectorAll('[' + LOADING_ATTR + '="true"]').forEach(clearLoading);
    });
}
