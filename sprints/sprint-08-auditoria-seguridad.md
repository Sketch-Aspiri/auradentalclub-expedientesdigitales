# Sprint 8 — Auditoría, seguridad y cumplimiento NOM

**Estado:** No iniciado
**Depende de:** Sprints 1–7

## Objetivo

Cerrar el MVP con una pasada dedicada de seguridad y cumplimiento antes de exponer el sistema a datos clínicos reales. Este sprint no agrega features — verifica que todo lo construido en 0–7 cumple lo que `CLAUDE.md` §5 exige desde el principio.

## Alcance

No es "nuevo código", es verificación + remediación de lo que ya existe:

- Cobertura completa de `audit_logs` en todos los módulos (lectura y escritura).
- Cifrado en reposo verificado en todos los campos sensibles listados en `CLAUDE.md` §5, no solo en `medical_histories`.
- Autorización por rol verificada en cada ruta/acción de los 7 módulos anteriores.
- Almacenamiento no público verificado para `patient_files` y firmas de `consents`.
- Endurecimiento general de configuración (ver `laravel-security` y el checklist del agente `securrity-auditor`).

## Tareas

- [ ] Invocar el agente `securrity-auditor` (`.claude/agents/securrity-auditor.md`) sobre el repo completo — no un módulo aislado.
- [ ] Resolver todos los hallazgos CRÍTICO antes de continuar; documentar explícitamente cualquier hallazgo ALTO que se decida posponer y por qué.
- [ ] Correr `/review` (`.claude/commands/review.md`) sobre el estado completo del proyecto.
- [ ] Verificar manualmente (no solo confiar en el agente): intentar acceder a un archivo de `patient_files` sin sesión, intentar ver un expediente con un rol sin permiso, revisar que `APP_DEBUG=false` no filtra nada en un entorno tipo staging.
- [ ] Revisar con el cliente/asesor legal cualquier duda pendiente de cumplimiento NOM-004/015/013 que se haya ido acumulando como "confirmar después" en los sprints 1–7 — no debe quedar ninguna sin resolver.
- [ ] Confirmar que `composer audit` está limpio (o que los CVEs restantes están documentados con plan de mitigación).
- [ ] Revisar cobertura de tests global (`.claude/rules/testing.md`, mínimo 80%).

### Hallazgos diferidos desde sprints anteriores (ya identificados, pendientes de resolver aquí)

- [ ] **Middleware global de cabeceras de seguridad + CSP** — hoy no existe. `X-Content-Type-Options`, `X-Frame-Options`/`frame-ancestors`, y una CSP para toda la app. Ojo: una CSP `script-src 'self'` rompería el `<script>` inline del formulario de foto (`patients/_form.blade.php`) y el `onerror` de `<x-patient-avatar>` — moverlos a un asset o usar nonce al introducir la CSP. (Detectado en la revisión de seguridad de la foto de paciente, Sprint 1.)
- [ ] **PHI en `<title>` del navegador** — `patients/show.blade.php`, `consultations/*` y `medical-history/edit` renderizan el nombre del paciente en `<title>`, que queda en historial/favoritos. El odontograma y el listado ya se corrigieron; falta el resto. Usar un título neutro tipo "Expediente #123".
- [ ] **Comando de barrido de archivos huérfanos** — `patient-photos/` (y en Sprint 7, `patient_files`) pueden quedar con archivos sin fila si un `save()` falla tras subir. Comando `artisan` periódico que compare disco contra BD.
- [ ] **Índice compuesto en `audit_logs`** para `recordViewOncePerDay` (`user_id, auditable_type, auditable_id, action, created_at`) si el volumen lo justifica.

## Criterios de aceptación

- [ ] Veredicto del agente `securrity-auditor`: `SEGURO PARA DESPLEGAR` (sin hallazgos CRÍTICO pendientes).
- [ ] Cero preguntas de cumplimiento NOM sin resolver con el cliente.
- [ ] Cobertura de tests ≥ 80% en el proyecto completo.

## Testing requerido

- Ver checklist del agente `securrity-auditor` — este sprint es en sí mismo una fase de testing/verificación, no de features.

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| | | | |
