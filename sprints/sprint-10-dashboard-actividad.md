# Sprint 10 — Dashboard: actividad reciente

**Estado:** No iniciado
**Depende de:** Sprint 1 (Pacientes), Sprint 3 (Consultas), infraestructura `audit_logs` (Sprint 0)
**Prioridad:** Baja — mejora de la pantalla de inicio, no bloquea ningún módulo clínico. Puede tomarse en cualquier momento después de sus dependencias; no antes de tener al menos dos módulos clínicos generando actividad.

## Contexto

El rediseño del dashboard (sesión 2026-08-27, con el agente `ux-ui-designer`) dejó la pantalla de inicio con:

- Banner de bienvenida (saludo + rol + fecha).
- Rejilla de **Módulos** (Pacientes activo; Odontograma, Consentimientos, Hoja de evolución y Carga de archivos como «Próximamente»).
- Sección **Actividad reciente** en **estado vacío** — solo estructura visual, sin datos.

`DashboardController` sigue siendo un `__invoke` que solo hace `return view('dashboard')`. Este sprint conecta la sección de actividad a datos reales.

## Objetivo

Mostrar en el dashboard un resumen honesto de la actividad reciente del expediente: últimos pacientes registrados/editados y últimas consultas, con enlaces directos al recurso, respetando el control de acceso por rol y sin exponer PHI de más.

## Decisiones pendientes de confirmar con el cliente

- [ ] **Qué cuenta como «actividad reciente»**: ¿altas y ediciones de pacientes? ¿consultas creadas? ¿también cambios de historia clínica / futuros módulos (odontograma, consentimientos)? ¿eliminaciones/restauraciones?
- [ ] **Fuente de datos**: leer de `audit_logs` (ya registra `viewed/created/updated/deleted/restored` con `user_id`, `patient_id`, `auditable_type/id`) vs. consultar directamente `patients`/`consultations` por `updated_at`. `audit_logs` es más fiel a "quién hizo qué y cuándo", pero requiere resolver el `auditable` para enlazar.
- [ ] **Alcance por rol**: ¿todos los roles ven toda la actividad, o el rol `doctor` solo ve la suya / la de sus pacientes? (Ver `CLAUDE.md` §3. Hoy las Policies dan CRUD completo a los tres roles, así que el default sería "toda la actividad", pero confirmarlo.)
- [ ] **Cuánta información mostrar**: ¿nombre completo del paciente en el feed del dashboard, o solo "Paciente #123 · Consulta registrada"? El dashboard es la primera pantalla tras login; mostrar nombres de pacientes ahí es exponer PHI en una vista de resumen. Confirmar con el cliente el nivel de detalle aceptable.
- [ ] **Ventana temporal / cantidad**: ¿últimos 10 eventos? ¿últimos 7 días? ¿paginado o "ver todo"?

## Alcance (una vez confirmado lo anterior)

- `DashboardController`: pasar a la vista una colección acotada de actividad reciente (con `with()` para evitar N+1; `limit` explícito; sin queries pesadas en cada carga de la home).
- Vista `dashboard.blade.php`: reemplazar el estado vacío de la sección **Actividad reciente** por la lista real, conservando el estado vacío para cuando no haya nada (sistema recién instalado).
- Cada item enlaza al recurso con `route(...)` y muestra: tipo de evento, quién lo hizo, cuándo (`diffForHumans` en español, timezone `America/Cancun`), y el enlace al paciente/consulta.
- Si se lee de `audit_logs`: un método/scope que traiga los últimos eventos relevantes resolviendo el `auditable` de forma segura (ignorar registros huérfanos de recursos ya `forceDelete`d).
- **No** registrar una nueva entrada de auditoría por *ver* el dashboard (el dashboard no es "ver un expediente"); si se decide auditar el acceso al resumen, es una decisión aparte.

## Criterios de aceptación

- [ ] La sección muestra actividad real cuando la hay y el estado vacío cuando no.
- [ ] Ningún dato clínico sensible (diagnóstico, alergias, notas) aparece en el feed — como máximo, el nivel de detalle confirmado con el cliente.
- [ ] El feed respeta el alcance por rol confirmado.
- [ ] Sin N+1: una o dos queries acotadas por carga del dashboard, verificado con `DB::listen` o Laravel Debugbar/telescope en desarrollo.
- [ ] Los enlaces del feed llevan al recurso correcto y no rompen si el recurso fue archivado (soft-deleted) o eliminado.

## Testing requerido

- Feature test: con actividad sembrada (factories de `Patient` + `Consultation`), el dashboard renderiza los N eventos esperados, más recientes primero.
- Feature test: dashboard sin actividad → se muestra el estado vacío, no un error.
- Test de autorización: si el alcance por rol es restringido, un `doctor` no ve actividad fuera de su alcance.
- Test de no-exposición: el HTML del dashboard no contiene campos clínicos sensibles del paciente.
- Test de que un evento de `audit_logs` cuyo `auditable` ya no existe no rompe el render.
- Regla del proyecto: si se toca `DashboardController` con queries sobre datos de paciente, el agente `code-reviewer` es **obligatorio** (`CLAUDE.md` §10).

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| 2026-08-27 | Sprint creado. Rediseño visual del dashboard + `app-layout` (banner, rejilla de módulos, estado vacío de actividad, navegación móvil) entregado; sección "Actividad reciente" queda como estructura sin datos, pendiente de este sprint. | agente `ux-ui-designer` | Estructura lista; feature con datos pendiente de confirmación del cliente |
