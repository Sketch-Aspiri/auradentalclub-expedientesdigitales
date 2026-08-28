# Sprint 5 — Consentimientos informados

**Estado:** En progreso
**Depende de:** Sprint 1, Sprint 2

## Objetivo

Versión digital de la hoja física de **consentimiento informado** de la clínica: datos del
paciente autollenados, cuestionario de salud tomado de la historia clínica (copia fija),
campos que llena el médico (plan, pronóstico, riesgos, alternativas), firmas digitales de
paciente / odontólogo tratante / hasta 2 testigos, y hoja membretada para imprimir con el
texto normativo real de la clínica.

## Decisiones confirmadas con el cliente (2026-08-28)

1. **Firmas:** canvas en pantalla (`signature_pad`), re-codificadas a PNG en el disco privado `local`.
2. **Post-firma:** el consentimiento firmado es **inmutable**; se corrige anulándolo (con motivo) y creando uno nuevo.
3. **Vista imprimible:** sí, hoja membretada con el texto legal real de la clínica (transcrito de la hoja física).
4. **Procedimientos y costos: FUERA de este módulo.** Vivirán en la Hoja de evolución y control
   (Sprint 6) y se vincularán al consentimiento. Desviación confirmada de `CLAUDE.md` §6
   (`consent_procedures` no se implementa).
5. **Datos de salud del paciente:** se copian a `consents.health_snapshot` (JSON cifrado) al
   **crear** el consentimiento; editar la historia clínica después no cambia la copia.
6. **"¿Cómo describiría su salud?" y "último examen médico":** se agregaron a la historia
   clínica (Sprint 2), y el consentimiento los toma de ahí.
7. Se conserva un campo `diagnosis` de texto libre (opcional) aunque la hoja física no lo tenga como renglón aparte.

## Esquema real implementado

**`consents`**: `patient_id` (FK restrict), `doctor_id`, `type` (enum `general`/`extraction`,
ampliable — `App\Enums\ConsentType`), `given_by` (enum `paciente`/`representante_legal`/`familiar`
— `App\Enums\ConsentGiver`), `relationship`, `diagnosis` (nullable, cifrado), `treatment_plan`,
`prognosis`, `risks_complications`, `management_alternatives` (cifrados), `health_snapshot`
(`encrypted:array`), `authorizes_photos_xrays`, `patient_accepts` (bool), `patient_signature_path`,
`doctor_signature_path`, `witness1_name`/`witness1_signature_path`, `witness2_name`/`witness2_signature_path`,
`signed_at`, `voided_at`, `voided_by`, `void_reason` (cifrado), soft deletes, timestamps.

**`medical_histories`** (+): `general_health_rating` (enum nullable), `last_medical_exam` (string nullable).

**`audit_logs.action`** (+): `signed`, `voided`.

Estado derivado (`App\Enums\ConsentStatus`): Borrador → Firmado → Anulado.

## Tareas

- [x] Migraciones `consents`, enum `audit_logs`, cuestionario de salud en `medical_histories`, reestructura a la hoja real.
- [x] Modelos `Consent` (Auditable, SoftDeletes, cifrado, `health_snapshot`, `snapshotHealthFrom`), `MedicalHistory` (2 campos), `Auditable::logAudit`.
- [x] `ConsentPolicy` (3 roles; `update`/`delete`/`sign` solo borrador; `void` solo firmado; `forceDelete` superadmin).
- [x] Form Requests `StoreConsentRequest` / `UpdateConsentRequest` / `VoidConsentRequest` + `ValidatesConsentData`; `MedicalHistoryRequest` ampliado.
- [x] Captura de firma con `signature_pad` (`resources/js/signature-canvas.js`), Livewire `SignConsent`, `App\Support\SignatureImage` (disco privado, re-codifica PNG).
- [x] Bloqueo de edición tras `signed_at` (policy) + flujo de anulación con motivo.
- [x] Registro en `audit_logs` (`created`/`viewed`/`updated`/`signed`/`voided`/`deleted`/`restored`).
- [x] Hoja de impresión membretada con el texto normativo real (Ley General de Salud, cláusulas A–D, declaraciones 1–2, bloques de firma).
- [x] Autollenado de datos personales (de `patients`) y del cuestionario de salud (de `medical_histories`, copia fija).
- [x] Módulo propio: tarjeta en el dashboard, enlace en la barra lateral, pantalla global con buscador de pacientes (`Consents\Browser`), acceso desde la ficha del paciente.
- [ ] Vincular con la Hoja de evolución y control (Sprint 6) — pendiente hasta que exista ese módulo.
- [ ] `/review` formal + `securrity-auditor` antes de cerrar el sprint.
- [ ] Texto legal específico del consentimiento de **extracción** (NOM-013): hoy usa el texto general + una nota; confirmar con la clínica si su hoja de extracción tiene texto propio.

## Criterios de aceptación

- [x] Un consentimiento firmado no puede editarse después de `signed_at` (403 por policy).
- [x] Las firmas se guardan fuera de `public/` y se sirven solo por `consents.signature` (auth + policy + throttle).
- [x] `health_snapshot` es una copia fija: editar la historia clínica no altera consentimientos ya creados.
- [~] El contenido legal de la hoja proviene del formato real de la clínica (transcrito de la hoja física que aportó el cliente el 2026-08-28). Falta confirmar el texto propio de extracción.
- [x] Procedimientos y costos quedan fuera; el `_form` y la impresión lo indican y remiten al módulo de evolución.

## Testing requerido

- [x] Un consentimiento firmado rechaza edición / eliminación (`ConsentPolicyTest`).
- [x] Firma no accesible sin autorización + cabeceras `no-store` (`ConsentSignatureAccessTest`).
- [x] Policy test por rol y por estado (`ConsentPolicyTest`).
- [x] `audit_logs` al crear / firmar / anular / restaurar (`ConsentTest`, `ConsentSigningTest`, `ConsentRestoreTest`).
- [x] Cifrado en reposo de texto clínico y de `health_snapshot` (`ConsentEncryptionTest`, `ConsentTest`).
- [x] `Patient::forceDeleting` purga consentimientos + firmas (`PatientForceDeleteTest`).
- [x] Cuestionario de salud en `medical_histories` (`MedicalHistoryRequestTest`).
- [x] Hoja de impresión (`ConsentPrintTest`).

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| 2026-08-28 | Implementación del módulo: migraciones, enums, modelo, policy, Form Requests, controlador, `ConsentSignatureController`, Livewire `SignConsent` + `signature_pad`, vistas (index/form/show/print/browser), factories y ~43 tests. | Implementación directa + `php artisan test` + Pint | Verde. |
| 2026-08-28 | Ajuste de alcance a petición del cliente: se quita `consent_procedures` (irá al módulo de evolución y se vinculará); Consentimientos pasa a módulo propio (dashboard + barra lateral + pantalla global). | Implementación directa | 251 tests verde. |
| 2026-08-28 | Fix: botón "Anular consentimiento" no enviaba — `minlength` oculto en el textarea bloqueaba el submit en silencio; formulario de anulación simplificado (HTML plano dentro del modal), regla `min:10` eliminada. | Verificación en navegador | Anulación funciona. |
| 2026-08-28 | Rediseño a la hoja física real de la clínica: `medical_histories` gana autopercepción de salud + último examen médico; `consents` gana `treatment_plan`/`prognosis`/`risks_complications`/`management_alternatives`/`health_snapshot`; autollenado de datos del paciente y copia fija del cuestionario de salud; hoja de impresión membretada con el texto normativo transcrito de la hoja física (Ley General de Salud, cláusulas A–D, declaraciones, bloques de firma). | Implementación directa + `php artisan test` + verificación en navegador | 254 tests verde. Revisión `code-reviewer` en curso. |
| 2026-08-28 | **Incidente:** un `migrate:fresh` durante el desarrollo vació la BD de desarrollo. Se recuperó al 100% desde el binary log de MySQL (3 pacientes, historia clínica, 2 consultas, 6 hallazgos de odontograma, auditoría) y se restauró. Respaldo en el scratchpad. | Reconstrucción con `mysqlbinlog` | BD restaurada, descifrado verificado. |
| 2026-08-28 | Revisión `code-reviewer`: veredicto ADVERTENCIA, 0 críticos. Corregido: auditoría de acceso al abrir el formulario de consentimiento (`create`/`edit` ahora llaman `recordView`); eventos `updated` espurios eliminados en alta/firma/anulación (`make()` antes del insert, `saveQuietly()` en las transiciones); tope de tamaño + límite de dimensiones en `SignatureImage` y `PrivateImage` (bomba de imagen); limpieza de firmas huérfanas si falla la persistencia; heurística Sí/No para medicación/alergias; nombre de ruta `consents.browse`. Diferido con nota: `last_medical_exam` en claro (referencia temporal, no PHI identificable); dos migraciones de `consents` sin plegar (ninguna desplegada, no viola la regla). | Agente `code-reviewer` | 255 tests verde. |
