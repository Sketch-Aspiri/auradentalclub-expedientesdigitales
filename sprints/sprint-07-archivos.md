# Sprint 7 — Carga de archivos

**Estado:** No iniciado
**Depende de:** Sprint 1 (opcionalmente Sprint 3 si se asocian a una consulta)

## Objetivo

Subida y consulta segura de radiografías, fotos y documentos, asociados al expediente o a una consulta específica.

## Alcance (CLAUDE.md §4.7, §5, §6, `.claude/rules/api-conventios.md`)

- `patient_files`: `patient_id`, `consultation_id` (nullable), `uploaded_by`, `file_path`, `file_type` (radiografía/foto/documento), `description`.
- Almacenamiento **fuera de `public/`**, servido solo por ruta controlada con autorización.

## Tareas

- [ ] Migración `patient_files`.
- [ ] Modelo `PatientFile` con relaciones (`belongsTo Patient`, `belongsTo Consultation` nullable, `belongsTo User as uploader`).
- [ ] `PatientFilePolicy`.
- [ ] Form Request de subida: `mimes` permitidos por `file_type`, tamaño máximo, validación de extensión vs MIME real.
- [ ] Single-action controller `DownloadPatientFileController` (o similar) que valida `authorize('view', $patientFile)` antes de servir el archivo desde el disco `local`.
- [ ] UI de galería/lista de archivos por paciente, agrupable por tipo o por consulta.
- [ ] Registro en `audit_logs` — incluyendo la **descarga/visualización**, no solo la subida (`CLAUDE.md` §5, `.claude/rules/api-conventios.md`).

## Criterios de aceptación

- [ ] Ningún archivo de `patient_files` es accesible por URL directa sin pasar por la ruta autorizada.
- [ ] La subida valida tipo y tamaño real del archivo, no solo la extensión del nombre.
- [ ] Cada descarga/visualización de un archivo clínico queda auditada.

## Testing requerido

- Test de que un archivo subido no es accesible directamente vía `Storage::url()`/disco público.
- Test de que la ruta de descarga rechaza a un usuario sin autorización (403).
- Test de validación de subida (MIME/tamaño inválido rechazado).
- Test de `audit_logs` en subida y en descarga.

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| | | | |
