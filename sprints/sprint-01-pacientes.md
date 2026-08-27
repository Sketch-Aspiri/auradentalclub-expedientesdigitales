# Sprint 1 — Pacientes (ficha de identificación)

**Estado:** Completado
**Depende de:** Sprint 0

## Objetivo

Alta, edición, búsqueda y filtros de pacientes — la ficha de identificación base sobre la que cuelgan todos los demás módulos clínicos.

## Alcance (CLAUDE.md §4.1, §6)

- CRUD de `patients`: `full_name`, `birth_date`, `sex`, `occupation`, `marital_status`, `address`, `phone`, `email`, `emergency_contact_name`, `emergency_contact_phone`.
- `age` calculado desde `birth_date` (accessor de Eloquent), no almacenado.
- Búsqueda y filtros de expedientes (por nombre, teléfono, al menos).
- Autorización por rol: quién puede crear/editar/eliminar/ver pacientes (`CLAUDE.md` §3 — `doctor` puede crear pacientes tambien `administrador`/`superadmin`).

## Tareas

- [x] Migración `patients` según el esquema de `CLAUDE.md` §6 — incluye `soft_deletes` (decisión propia: retención de expediente en vez de borrado físico, ver nota abajo) e índices en `full_name`/`phone`.
- [x] Modelo `Patient` con `$fillable`, casts apropiados, accessor `age` (vía `Attribute::make`), trait `Auditable` conectado (`auditPatientId()` devuelve su propio id).
- [x] `PatientPolicy` cubriendo los tres roles explícitamente — decisión confirmada con el usuario: `doctor`/`administrador`/`superadmin` tienen el mismo acceso CRUD completo sobre la ficha; `forceDelete` (borrado permanente) restringido a `superadmin`.
- [x] `StorePatientRequest` / `UpdatePatientRequest` con validación completa; reglas compartidas extraídas a `App\Http\Requests\Concerns\ValidatesPatientData` (evita duplicación).
- [x] `PatientController` (resource controller) con `$this->authorize(...)` explícito en cada acción.
- [x] Vista de listado con búsqueda (nombre/teléfono) y paginación (`paginate(15)`).
- [x] Vistas de alta/edición/ficha siguiendo la identidad visual (`CLAUDE.md` §7, tokens `aura-*`).
- [x] `audit_logs` conectado: `created`/`updated`/`deleted` automáticos vía trait, `viewed` explícito en `PatientController@show`.
- [x] Factory de `Patient` con datos mexicanos (Faker `es_MX`, formato de teléfono `55########`).

## Criterios de aceptación

- [x] Un `administrador`/`superadmin` puede crear, editar, buscar y eliminar (soft delete) pacientes.
- [x] Un `doctor` tiene el mismo acceso CRUD que `administrador`/`superadmin` sobre la ficha de identificación — confirmado explícitamente con el usuario en sesión de desarrollo (no fue una suposición), ver `CLAUDE.md` §3/§13.
- [x] Cada acción sobre un paciente genera su registro en `audit_logs`.
- [x] No hay ninguna ruta de pacientes sin `Policy` explícita.

## Testing requerido

- [x] Form Request: casos válidos e inválidos por campo relevante (fechas, teléfono, email, sexo) — `StorePatientRequestTest`, `UpdatePatientRequestTest`.
- [x] Policy: los tres roles, explícitamente, para cada acción (view/create/update/delete), más `forceDelete` a nivel de Gate — `PatientPolicyTest`.
- [x] Feature test: flujo completo alta → ver → editar → eliminar de punta a punta — `PatientCrudTest`.
- [x] Verifica que `audit_logs` recibe un registro al crear/ver/editar/eliminar un paciente.

## Notas de decisiones tomadas en esta sesión

- **Soft delete:** se implementó `SoftDeletes` en `patients` (no borrado físico) como buena práctica estándar para retención de expediente clínico. No fue confirmado explícitamente con el cliente — revisar si aplica normativamente (NOM) antes de producción.
- **Acceso del rol `doctor`:** confirmado con el usuario en sesión — CRUD completo, igual que `administrador`/`superadmin`.

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| 2026-08-26 | Migración, modelo, factory, policy, form requests, controlador resource, rutas y vistas (index/create/edit/show) del módulo de pacientes | Implementación directa | 26 tests iniciales en verde |
| 2026-08-26 | Revisión obligatoria de código (agente `code-reviewer`) — 0 críticos, 1 ALTO (falta test de `forceDelete` por rol) y 1 MEDIO (falta `UpdatePatientRequestTest`) | Agente `code-reviewer` | Ambos hallazgos corregidos; también se eliminó duplicación de reglas de validación (DRY) |
| 2026-08-26 | Suite completa re-ejecutada tras las correcciones | `php artisan test` | 48 tests, 112 assertions, todo en verde |
| 2026-08-27 | **Foto de identificación del paciente** (mejora del módulo, a petición del cliente): columna `photo_path`, disco privado `local`, ruta autorizada `patients.photo`, subida con re-codificación a JPEG vía GD (descarta EXIF/GPS, neutraliza políglotas), quitar/reemplazar con borrado del archivo huérfano, avatar con iniciales de reserva (`<x-patient-avatar>`). Auditoría **opción B** (elegida por el cliente): 1 evento `viewed` por usuario/paciente/día en el endpoint de la foto (`recordViewOncePerDay`). Cabeceras `no-store`/`nosniff`, `throttle:300,1`. | Implementación + `ux-ui-designer` (rediseño del listado + integración visual) + `code-reviewer` + `ecc:security-reviewer` | code-reviewer APROBADO (0 críticos), security-reviewer WARNING (0 críticos/altos); todos los MEDIO/BAJO abordados salvo los diferidos a Sprint 8 (ver ahí). +13 tests de foto. |
| 2026-08-27 | **Listado de pacientes → componente Livewire** (`App\Livewire\Patients\PatientList`): búsqueda en vivo por nombre/teléfono con debounce, alternancia activos/archivados y restauración sin recargar; `#[Url]` conserva `?q=`/`?archived=` y el render inicial SSR los respeta (tests antiguos siguen verdes). La ruta `patients.index` se mantiene vía un `PatientController::index` reducido a `authorize` + vista contenedora. | Implementación directa + `code-reviewer` | +9 tests (`PatientListTest`). Suite en 167 verde. |
