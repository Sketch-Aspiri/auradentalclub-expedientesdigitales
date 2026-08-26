# Convenciones de rutas/endpoints y manejo de errores

> Este proyecto es Blade + Livewire (sin API JSON desacoplada — ver `CLAUDE.md` §2). "Endpoints" aquí significa rutas HTTP servidas por controladores/Livewire, no una REST API pública. Si en el futuro se expone una API JSON real (ej. para una app móvil), trata la sección 7 como punto de partida y confírmalo con el usuario antes de construirla.

## Organización de rutas

- Agrupa `routes/web.php` por dominio, no por tipo técnico: pacientes, historia clínica, consultas, odontograma, consentimientos, evolución/costos, archivos. Si crece, extrae a `routes/patients.php`, `routes/consultations.php`, etc. e inclúyelos desde `web.php`.
- Usa **Resource Controllers** (`Route::resource`) para CRUD estándar de un recurso (`patients`, `consultations`). Usa **single-action controllers** (`__invoke`) para acciones que no encajan en CRUD (`DownloadPatientFileController`, `SignConsentController`, `GenerateOdontogramReportController`).
- Nombra las rutas (`->name(...)`) siguiendo `recurso.accion`: `patients.index`, `patients.show`, `consultations.store`, `patient-files.download`. Nunca enlaces por URL cruda en Blade/Livewire — siempre `route('...')`.
- Todo grupo de rutas que toque datos de paciente va dentro de middleware `auth` como mínimo, y con el middleware de rol/policy correspondiente cuando aplique (ver `CLAUDE.md` §3 y §5) — nunca una ruta "temporalmente sin protección".

## Autorización en cada endpoint

- Cada acción de controlador que lea o modifique datos de un paciente llama explícitamente a `$this->authorize(...)` / `Gate::authorize(...)` o usa `->middleware('can:...')` en la ruta — no asumas que el middleware `auth` es suficiente.
- Las Policies son la fuente de verdad de "quién puede qué"; no dupliques esa lógica con `if ($user->role === '...')` sueltos dentro de controladores o componentes Livewire.
- Route model binding (`{patient}` → `Patient $patient`) es el default para recursos por id; si necesitas ocultar el id secuencial en URLs sensibles, evalúa binding por UUID/slug y coméntalo al proponerlo.

## Validación de entrada

- Toda acción que reciba datos del usuario usa **Form Request** (`StorePatientRequest`, `UpdateConsentRequest`, etc.) — nunca valides "a mano" dentro del controlador o del método de un componente Livewire salvo reglas triviales de un solo campo.
- Mensajes de validación visibles al usuario van en español (ver `CLAUDE.md` §8); usa el método `messages()` del Form Request o `lang/es/validation.php` en vez de hardcodear strings en la vista.
- En componentes Livewire, usa `#[Rule(...)]` o `rules()` + `$this->validate()`; muestra los errores con `@error('campo')` en la vista, consistente con el patrón de Form Requests del resto del proyecto.

## Códigos de estado HTTP

| Situación | Código | Notas |
|---|---|---|
| Éxito con vista/redirect | `302` (redirect) o `200` | Flujo normal Blade/Livewire |
| Validación fallida | `422` | Laravel lo maneja automático vía Form Request; Livewire lo traduce a errores inline |
| No autenticado | `401` (API) / redirect a login (web) | Middleware `auth` |
| Autenticado sin permiso (rol incorrecto) | `403` | Policy/Gate deniega — nunca devuelvas `404` para ocultar un recurso que sí existe pero no autorizado, salvo que sea una decisión explícita de no revelar existencia |
| Recurso no encontrado | `404` | Route model binding lo da automático (`ModelNotFoundException`) |
| Token CSRF expirado | `419` | Vista personalizada explicando que la sesión expiró, sin tecnicismos |
| Error de servidor no controlado | `500` | Nunca mostrar stack trace ni mensaje de excepción crudo en producción (`APP_DEBUG=false`) |

## Manejo de errores y excepciones

- Excepciones de dominio (ej. "no se puede eliminar un consentimiento ya firmado", "no se puede editar una consulta de otro doctor") se modelan como excepciones custom (`App\Exceptions\...`) con mensaje claro, no como `abort(400, 'texto suelto')` repetido en varios controladores.
- `App\Exceptions\Handler` (o `bootstrap/app.php` en Laravel 12 con `->withExceptions()`) centraliza el render de esas excepciones a una respuesta amigable — no captures la excepción en cada controlador por separado.
- **Nunca** silencies una excepción con un `catch` vacío o un `catch (\Throwable $e) {}` — como mínimo se registra con `report($e)` o `Log::error()` antes de responder al usuario. Ver regla global del usuario: "nunca silencies errores".
- Mensajes de error mostrados al usuario son genéricos y en español ("No se pudo guardar el consentimiento, intenta de nuevo"); el detalle técnico va solo al log, nunca a la respuesta HTTP ni a la vista.
- **Nunca** incluyas datos clínicos del paciente (nombre, diagnóstico, alergias, notas) en el mensaje de una excepción, en `Log::` a nivel `info`/`error` sin necesidad, ni en la URL (query string) — ver `CLAUDE.md` §5.
- Para acciones destructivas sobre datos clínicos (eliminar expediente, consentimiento, archivo), usa confirmación explícita en la UI (Livewire modal, no un simple link `DELETE`) y considera soft deletes en vez de borrado físico cuando la retención del expediente lo requiera — confírmalo con el cliente si no está definido.

## Descarga y acceso a archivos (`patient_files`)

- Nunca sirvas un archivo clínico con una URL directa al disco `public`. Usa una ruta controlada (`GET /patients/{patient}/files/{file}/download`) que:
  1. Verifique autorización (`$this->authorize('view', $patientFile)`).
  2. Genere el stream/response desde el disco `local` (`Storage::disk('local')->download(...)` o `response()->file(...)` con `Storage::path(...)`).
- Si necesitas compartir un archivo por tiempo limitado, usa `temporaryUrl()` firmada, nunca un link permanente.
- Registra en `audit_logs` cada descarga/visualización de un archivo clínico, igual que otras acciones sobre datos de paciente (`CLAUDE.md` §5).

## Logging

- Usa canales de log separados si es necesario (`storage/logs/laravel.log` para aplicación, un canal dedicado para eventos de seguridad/autenticación) — nunca mezcles logs de auditoría clínica (`audit_logs`, que es una tabla, no un archivo de log) con logs de depuración técnica.
- No dejes `Log::debug()`/`dd()`/`dump()` en código commiteado a ramas compartidas.

## Si en el futuro se agrega una API JSON real

Fuera de alcance por ahora (`CLAUDE.md` §11 no lo prohíbe explícitamente, pero no existe hoy). Si se solicita:

- Confirma primero si es para consumo interno (otro sistema del dominio `auradentalclub.com`) o externo, ya que cambia los requisitos de autenticación (Sanctum vs nada).
- Usa un envelope de respuesta consistente: indicador de éxito/estado, payload de datos (nullable en error), mensaje de error (nullable en éxito), y metadata de paginación cuando aplique.
- Aplica el mismo checklist de autorización, validación y no-exposición de PHI de este documento — una API no relaja ninguna de las reglas anteriores.
