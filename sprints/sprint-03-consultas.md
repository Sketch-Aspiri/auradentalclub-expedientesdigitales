# Sprint 3 — Consultas

**Estado:** No iniciado
**Depende de:** Sprint 1, Sprint 2

## Objetivo

Registrar por cita: signos vitales, exploración bucal, motivo de consulta, diagnóstico, plan de tratamiento, pronóstico, riesgos y alternativas.

## Alcance (CLAUDE.md §4.3, §6)

- `consultations`: `patient_id`, `doctor_id`, `consultation_date`.
- Signos vitales: `blood_pressure`, `heart_rate`, `temperature`.
- Exploración bucal: `soft_tissues_notes`, `gums_periodontium_notes`, `oral_hygiene_level` (enum buena/regular/mala).
- `chief_complaint`, `clinical_diagnosis`, `treatment_plan`, `prognosis`, `risks_and_complications`, `treatment_alternatives`.

## Tareas

- [ ] Migración `consultations` según `CLAUDE.md` §6.
- [ ] Modelo `Consultation` con relaciones (`belongsTo Patient`, `belongsTo User as doctor`), `$fillable`.
- [ ] `ConsultationPolicy`: confirmar si un `doctor` solo ve/edita sus propias consultas o todas (`CLAUDE.md` §3 no lo especifica — **preguntar antes de asumir**, `CLAUDE.md` §13).
- [ ] Form Request de registro de consulta.
- [ ] Vista de historial de consultas por paciente (listado cronológico) y de captura de una nueva.
- [ ] `doctor_id` se asigna automáticamente al usuario autenticado si es `doctor` (no seleccionable libremente) — confirmar si `administrador`/`superadmin` pueden registrar en nombre de un doctor.
- [ ] Registro en `audit_logs`.

## Criterios de aceptación

- [ ] Una consulta queda asociada correctamente a paciente y doctor.
- [ ] El historial de consultas de un paciente se ve ordenado cronológicamente.
- [ ] La regla de "quién puede ver/editar qué consulta" está confirmada con el cliente, no asumida.
- [ ] Sin notas clínicas (`clinical_diagnosis`, etc.) expuestas fuera de la vista autorizada.

## Testing requerido

- Policy test cubriendo el caso confirmado de acceso por doctor (propio vs todos).
- Form Request: validación de signos vitales con formatos/rangos razonables.
- Feature test de alta de consulta completa.
- Test de `audit_logs`.

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| | | | |
