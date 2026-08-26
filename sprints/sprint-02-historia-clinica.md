# Sprint 2 — Historia clínica / anamnesis

**Estado:** Completado
**Depende de:** Sprint 1

## Objetivo

Capturar los antecedentes patológicos y no patológicos del paciente (uno por paciente, editable), incluyendo alergias y antecedentes médicos — el módulo con más densidad de datos sensibles del sistema.

## Alcance (CLAUDE.md §4.2, §4.8, §5, §6)

- `medical_histories`: relación `hasOne` con `Patient` (unique por `patient_id`).
- Antecedentes patológicos: diabetes, hipertensión, cardiopatías, VIH/hepatitis, problemas de coagulación, convulsiones (bools), alergias (texto), medicamentos actuales (texto), hospitalización/cirugía previa (bool + texto).
- Antecedentes no patológicos: higiene oral, tabaquismo, alcoholismo.
- Campos adicionales del consentimiento extendido: sangrado prolongado, productos para bajar de peso, embarazo (bool + tiempo), notas adicionales.
- **Cifrado en reposo** de campos sensibles (`allergies`, `current_medications`, notas) con cast `encrypted` de Eloquent — esto no es opcional, ver `CLAUDE.md` §5.

## Tareas

- [x] Migración `medical_histories` con todos los campos de `CLAUDE.md` §6, `patient_id` unique + FK `cascadeOnDelete`.
- [x] Modelo `MedicalHistory` con casts `encrypted` en `allergies`, `current_medications`, `hospitalization_details`, `pregnancy_time`, `additional_health_notes`; `$fillable` completo; trait `Auditable` (`auditPatientId()` devuelve `patient_id`).
- [x] `MedicalHistoryPolicy` — decisión confirmada con el usuario: mismo acceso que `PatientPolicy` (los tres roles CRUD completo, `forceDelete` solo superadmin).
- [x] `MedicalHistoryRequest` (un solo Form Request para create+update, ya que es upsert) — formulario agrupado en 3 secciones: patológicos / no patológicos / adicionales.
- [x] Un solo registro por paciente: `PatientMedicalHistoryController` usa `Route::singleton` (`edit`/`update` únicamente) + `updateOrCreate(['patient_id' => ...])`; constraint `unique` real en MySQL como respaldo.
- [x] `audit_logs` conectado: `created`/`updated` automáticos vía trait, `viewed` explícito solo cuando el registro ya existe (evita loggear "viewed" sobre un formulario vacío de un paciente sin historia todavía).

## Criterios de aceptación

- [x] Solo existe una `medical_history` por paciente en todo momento (constraint `unique` en migración + test de "segundo guardado actualiza, no duplica").
- [x] Los campos sensibles están cifrados en la base de datos — verificado leyendo la columna cruda vía `DB::table(...)->value(...)`, no solo el accessor de Eloquent.
- [x] Ningún dato de esta tabla aparece en logs, mensajes de error o URLs (verificado por el `code-reviewer`).
- [x] Autorización por rol confirmada explícitamente con el usuario (no asumida) — igual que `PatientPolicy`.

## Testing requerido

- [x] Test que verifica que el valor crudo en MySQL de un campo `encrypted` **no** es texto plano ni lo contiene.
- [x] Test de que solo se puede crear una `medical_history` por paciente (segundo intento actualiza, no duplica).
- [x] Policy test para los tres roles + `forceDelete` a nivel de Gate.
- [x] Test de `audit_logs` al ver/crear/editar historia clínica, incluyendo el caso "sin historia todavía → no debe loguear viewed".

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| 2026-08-26 | Migración, modelo (cifrado), factory, policy, form request, controlador singleton, rutas y vista agrupada en 3 secciones | Implementación directa | 20 tests iniciales en verde |
| 2026-08-26 | Revisión obligatoria de código (agente `code-reviewer`) — 0 críticos/altos, 1 MEDIO (ability de autorización inconsistente entre create/update) y 2 BAJO (mensajes de validación en inglés — gap preexistente del proyecto, no de este sprint; falta manejo de condición de carrera en `updateOrCreate`) | Agente `code-reviewer` | Veredicto APROBADO. Se corrigió el hallazgo MEDIO (autorización ahora contra la instancia real, no siempre contra `create`); los BAJO quedan en backlog |
| 2026-08-26 | Suite completa re-ejecutada tras el fix | `php artisan test` | 64 tests, 144 assertions, todo en verde |
