# Sprint 3 — Consultas

**Estado:** Completado
**Depende de:** Sprint 1, Sprint 2

## Objetivo

Registrar por cita: signos vitales, exploración bucal, motivo de consulta, diagnóstico, plan de tratamiento, pronóstico, riesgos y alternativas.

## Alcance (CLAUDE.md §4.3, §6)

- `consultations`: `patient_id`, `doctor_id`, `consultation_date`.
- Signos vitales: `blood_pressure`, `heart_rate`, `temperature`.
- Exploración bucal: `soft_tissues_notes`, `gums_periodontium_notes`, `oral_hygiene_level` (enum buena/regular/mala).
- `chief_complaint`, `clinical_diagnosis`, `treatment_plan`, `prognosis`, `risks_and_complications`, `treatment_alternatives`.

## Decisiones confirmadas con el cliente (CLAUDE.md §13)

1. **Alcance de acceso del rol `doctor`:** un doctor puede **ver y editar cualquier** consulta, no solo las suyas (es un solo consultorio; la continuidad de atención entre doctores lo requiere). `ConsultationPolicy` queda idéntica a `PatientPolicy` / `MedicalHistoryPolicy`.
2. **Asignación de `doctor_id`:** si quien registra es `doctor`, la consulta se le asigna automáticamente y el campo no se muestra. `administrador` / `superadmin` **sí** eligen el doctor tratante de una lista (obligatorio). Al **editar**, un doctor no reasigna la consulta a sí mismo — solo `administrador` / `superadmin` pueden cambiar el `doctor_id`.
3. **Eliminación:** soft delete con el mismo patrón que Pacientes (los tres roles hacen soft delete; `forceDelete` solo `superadmin`).

## Tareas

- [x] Migración `consultations` según `CLAUDE.md` §6 (+ `softDeletes`, índice `[patient_id, consultation_date]`).
- [x] Modelo `Consultation` con relaciones (`belongsTo Patient`, `belongsTo User as doctor`), `$fillable`, trait `Auditable`, `SoftDeletes`.
- [x] **Cifrado en reposo** (`encrypted` cast) de las 8 notas clínicas de texto libre (`chief_complaint`, `clinical_diagnosis`, `treatment_plan`, `prognosis`, `risks_and_complications`, `treatment_alternatives`, `soft_tissues_notes`, `gums_periodontium_notes`). Signos vitales y `oral_hygiene_level` quedan en claro (necesarios para listar/mostrar, menor sensibilidad).
- [x] `ConsultationPolicy` — decisión 1 arriba.
- [x] Form Requests `StoreConsultationRequest` / `UpdateConsultationRequest` + trait `ValidatesConsultationData` (reglas y mensajes en español compartidos). Rango razonable de signos vitales, fecha no futura, motivo y diagnóstico obligatorios.
- [x] `OralHygieneLevel` enum (`buena` / `regular` / `mala`) con `label()`.
- [x] Rutas `Route::resource('patients.consultations')->shallow()` — `index`/`create`/`store` anidadas bajo el paciente; `show`/`edit`/`update`/`destroy` sobre `consultations/{consultation}`.
- [x] Vistas: historial cronológico por paciente, formulario de captura/edición, detalle de consulta. Enlace "Consultas" en la ficha del paciente.
- [x] `doctor_id` automático para el rol `doctor` (decisión 2); selector de doctor para `administrador` / `superadmin`.
- [x] Registro en `audit_logs` (`created`/`updated`/`deleted` automáticos vía trait, `viewed` explícito solo al abrir el detalle de una consulta, no al listar).

## Criterios de aceptación

- [x] Una consulta queda asociada correctamente a paciente y doctor.
- [x] El historial de consultas de un paciente se ve ordenado cronológicamente (fecha desc, luego id desc).
- [x] La regla de "quién puede ver/editar qué consulta" está confirmada con el cliente, no asumida.
- [x] Sin notas clínicas (`clinical_diagnosis`, etc.) expuestas fuera de la vista autorizada — cifradas en BD, nunca en logs/errores/URLs.

## Testing requerido

- [x] Policy test cubriendo los tres roles (ver/crear/editar/eliminar cualquier consulta) + `forceDelete` a nivel de Gate.
- [x] Form Request: validación de signos vitales fuera de rango, fecha futura, motivo/diagnóstico vacío, `oral_hygiene_level` fuera de catálogo, selección de doctor obligatoria y válida para `administrador`.
- [x] Feature test de alta de consulta completa + flujo ver/editar/eliminar.
- [x] Test de `audit_logs` (created/viewed/updated/deleted con `patient_id` correcto; "listar historial no genera viewed").
- [x] Test de cifrado en reposo (valor crudo en MySQL ≠ texto plano, accessor de Eloquent descifra).
- [x] Test de asignación de `doctor_id` (automática para doctor; a nombre del doctor elegido para administrador; un doctor no reasigna al editar).

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| 2026-08-27 | Preguntas de negocio resueltas (alcance doctor, asignación de doctor_id, eliminación) antes de codificar | AskUserQuestion | 3 decisiones confirmadas, documentadas arriba |
| 2026-08-27 | Migración, enum `OralHygieneLevel`, modelo con cifrado, factory, policy, 2 Form Requests + trait, controlador resource shallow, rutas, 5 vistas, enlace en ficha de paciente | Implementación directa | 30 tests nuevos en verde; suite completa 94 tests / 215 assertions en verde |
| 2026-08-27 | `./vendor/bin/pint --dirty` | Pint | Formato aplicado (imports ordenados, fully-qualified types) |
| 2026-08-27 | Revisión obligatoria de código | Agente `code-reviewer` | Veredicto ADVERTENCIA: 0 CRÍTICO, 1 ALTO, 1 MEDIO, 4 BAJO. Seguridad de fondo sólida (cifrado, auditoría, policy, sin PHI en logs/URLs) |
| 2026-08-27 | Corrección del hallazgo ALTO: un `doctor` podía reasignar `doctor_id` al editar colando `?doctor_id=` por la query string (`remove()` solo limpia el body bag; `validated()` valida sobre `all()` = body + query). Fix: `merge()` fuerza el `doctor_id` original. + helper `User::isDoctor()` (hallazgo BAJO de duplicación). 2 tests de regresión nuevos (bypass por query string; rutas shallow sin autenticar) | `/fix-issue` | RED→GREEN; suite completa 96 tests / 225 assertions en verde; Pint OK en los archivos del sprint |
| 2026-08-27 | Fix operativo: `php artisan migrate` en la base de desarrollo (la migración de consultas quedó Pending; error `Table 'consultations' doesn't exist` al abrir la sección) | `/fix-issue` | Tabla creada, sección de consultas visible. Sin impacto en datos |
| 2026-08-27 | Resolución de los pendientes del `code-reviewer` (confirmados con el usuario): (1) `index()` de consultas ahora audita el acceso como `viewed` sobre el `Patient`; (2) FK `consultations.patient_id` y `medical_histories.patient_id` cambiadas a `RESTRICT` + `Patient::forceDeleting` purga cada registro hijo explícitamente para que quede su `deleted` en `audit_logs`; (3) `phpmyadmin` movido al perfil de Compose `dev` (`docker compose --profile dev up -d`) | Implementación directa | Migración `2026_08_27_120000` corrida en dev y testing. Suite completa 98 tests / 236 assertions en verde. Pint OK |
| 2026-08-27 | Cierre de los 2 pendientes de backlog: (A) evento `restored` auditado — enum `audit_logs.action` += `restored` (migración `2026_08_27_140000`), `Auditable` engancha `static::restored` en modelos con SoftDeletes, y flujo de restauración real: `patients.restore` + `consultations.restore` (rutas `PUT` con `withTrashed()`), vista de "archivados" en el listado de pacientes y sección "Consultas archivadas" en el historial, botón Restaurar con check de policy. (B) `Patient::forceDeleting` — test dinámico que descubre toda tabla con `patient_id` y falla si queda huérfana tras el purgado (contrato verificado para sprints futuros) | Implementación directa | 13 tests nuevos; suite completa 111 tests / 272 assertions en verde. Pint OK |
| 2026-08-27 | Revisión de código de A+B | Agente `code-reviewer` | Veredicto APROBADO (0 CRÍTICO/ALTO). Corregido el hallazgo MEDIO: `SoftDeletes::restore()` disparaba un `updated` fantasma en `audit_logs` además del `restored` — `Auditable` ahora ignora el `updated` cuyo único cambio es `deleted_at → null`. BAJO: `down()` del enum aborta si hay filas `restored`; `archivedConsultations` con `limit(100)` + scope `orderedForHistory`. Suite 112 tests / 274 assertions en verde |

### Pendientes (backlog, sin bloqueo)

- _Ninguno._ Al añadir los módulos de consentimientos, archivos, hoja de evolución y odontograma, conectar cada relación clínica a `Patient::forceDeleting()` — el test `PatientForceDeleteTest::"el borrado permanente no deja registros huérfanos..."` lo hará fallar si se olvida.
