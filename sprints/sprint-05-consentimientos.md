# Sprint 5 — Consentimientos informados

**Estado:** No iniciado
**Depende de:** Sprint 1, Sprint 2

## Objetivo

Formulario genérico de consentimiento con campo `type` (general, extracción, ampliable), tabla de procedimientos/costos, y firmas digitales de paciente/médico/testigos.

## Alcance (CLAUDE.md §4.5, §5, §6)

- `consents`: `patient_id`, `doctor_id`, `type`, `given_by`, `relationship`, `diagnosis`, `proposed_treatment`, `specific_risks`, `authorizes_photos_xrays`, `patient_accepts`, rutas de firma (paciente/médico/2 testigos), `signed_at`.
- `consent_procedures`: detalle de procedimiento/costo asociado a un consentimiento.
- El consentimiento de **extracción** debe respetar lo referenciado en **NOM-013-SSA2-2015** (`CLAUDE.md` §5) — si hay duda sobre el contenido exacto exigido, confirmar con el cliente/asesor, no redactarlo por cuenta propia.

## Tareas

- [ ] Migraciones `consents` y `consent_procedures`.
- [ ] Modelos con relación `Consent hasMany ConsentProcedures`.
- [ ] `ConsentPolicy`.
- [ ] Form Request de creación de consentimiento + procedimientos anidados (array de `consent_procedures`).
- [ ] Captura de firma digital (paciente, médico, hasta 2 testigos) — evalúa librería de firma en canvas (ej. signature pad) y **guarda la imagen fuera del disco público** (ver `.claude/rules/api-conventios.md`).
- [ ] Plantilla/vista por `type` (general vs extracción) — el contenido legal específico de cada tipo debe confirmarse con el cliente antes de darlo por definitivo.
- [ ] Bloqueo de edición una vez `signed_at` está establecido (un consentimiento firmado no debería editarse — solo se anula/reemplaza por uno nuevo; confirmar el flujo exacto con el cliente).
- [ ] Registro en `audit_logs`.

## Criterios de aceptación

- [ ] Un consentimiento firmado no puede editarse silenciosamente después de `signed_at`.
- [ ] Las firmas se guardan fuera de `public/` y se sirven solo por ruta autorizada.
- [ ] El contenido legal de cada `type` fue confirmado con el cliente (NOM-004/015/013 según corresponda) — no es una redacción propia de Claude sin validar.
- [ ] `consent_procedures` refleja correctamente costos por procedimiento dentro del consentimiento.

## Testing requerido

- Test de que un consentimiento firmado rechaza intentos de edición (o sigue el flujo confirmado de reemplazo).
- Test de subida/acceso de firma: no accesible sin autorización.
- Policy test por rol.
- Test de `audit_logs` al crear/firmar un consentimiento.

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| | | | |
