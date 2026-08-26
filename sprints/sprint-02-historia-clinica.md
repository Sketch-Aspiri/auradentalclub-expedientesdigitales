# Sprint 2 — Historia clínica / anamnesis

**Estado:** No iniciado
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

- [ ] Migración `medical_histories` con todos los campos de `CLAUDE.md` §6, `patient_id` unique.
- [ ] Modelo `MedicalHistory` con casts `encrypted` en los campos sensibles, `$fillable`.
- [ ] `MedicalHistoryPolicy` (o reutilizar la lógica de `PatientPolicy` si el acceso es el mismo — confirmar).
- [ ] Form Request de captura/edición — es un formulario largo, agrúpalo en secciones claras en la UI (patológicos / no patológicos / adicionales).
- [ ] Un solo registro por paciente: la UI debe crear si no existe, editar si ya existe (no permitir duplicados).
- [ ] Registro en `audit_logs` al ver/crear/editar la historia clínica.

## Criterios de aceptación

- [ ] Solo existe una `medical_history` por paciente en todo momento.
- [ ] Los campos sensibles están cifrados en la base de datos (verificable leyendo la columna cruda, no solo el accessor de Eloquent).
- [ ] Ningún dato de esta tabla aparece en logs, mensajes de error o URLs.
- [ ] Autorización por rol confirmada explícitamente (no asumida).

## Testing requerido

- Test que verifica que el valor crudo en MySQL de un campo `encrypted` **no** es texto plano.
- Test de que solo se puede crear una `medical_history` por paciente (segundo intento actualiza, no duplica).
- Policy test para los tres roles.
- Test de `audit_logs` al ver/editar historia clínica.

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| | | | |
