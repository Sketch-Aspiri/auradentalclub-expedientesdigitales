# Sprint 4 — Odontograma digital interactivo

**Estado:** Completado
**Depende de:** Sprint 1 (puede correr en paralelo a Sprint 2/3)

## Objetivo

Estado estructurado por diente y por superficie, con catálogo ampliable de estados y nota
libre por hallazgo, visualizado como un odontograma interactivo con historial.

## Alcance (CLAUDE.md §4.4, §6)

- `odontogram_records`: **historial append-only** — cada fila es un hallazgo registrado en
  un momento dado sobre un diente o una superficie concreta. No hay `update` en sitio;
  corregir es registrar de nuevo, y un error de captura se archiva con soft delete.
- Numeración **FDI** dentición permanente (11–18, 21–28, 31–38, 41–48). Confirmada con el
  cliente el 2026-08-27 mediante el odontograma de referencia que aportó.
- Captura **por superficie** (mesial / distal / oclusal-incisal / vestibular / lingual) o
  sobre el diente completo. Confirmado con el cliente el 2026-08-27 (desviación del esquema
  de `CLAUDE.md` §6, que asumía un solo `status` por diente).
- Catálogo de `status` como enum ampliable (`App\Enums\ToothStatus`, 12 estados): sano,
  caries, obturado, sellador, fractura, corona, endodoncia, prótesis fija, implante,
  movilidad, extraído, ausente. Ampliar = nuevo caso en el enum + migración que extienda
  el `enum` de la columna.
- `note` de texto libre por hallazgo, **cifrada en reposo** (`encrypted` cast, CLAUDE.md §5).
- `recorded_by` (quién registró el hallazgo) y `recorded_at` (fecha clínica, puede no ser hoy).

## Tareas

- [x] Confirmar con el cliente: numeración FDI, catálogo de estados, captura por superficie
  e historial de cambios. Confirmado el 2026-08-27.
- [x] Migración `odontogram_records` (con `surface` nullable, `status` enum, soft deletes).
- [x] Modelo `OdontogramRecord` (Auditable, SoftDeletes, `note` cifrada), enums
  `ToothStatus` / `ToothSurface`, helper `App\Support\Dentition`.
- [x] `OdontogramRecordPolicy` (mismo acceso que `PatientPolicy`; sin `update`;
  `forceDelete` solo superadmin).
- [x] Componente Livewire `App\Livewire\Patients\Odontogram`: diagrama SVG de las 32 piezas,
  clic en número (diente completo) o zona (superficie) para seleccionar, formulario de
  hallazgo con validación de alcance (superficie vs diente completo), panel de estado
  vigente e historial por pieza, archivar hallazgo.
- [x] Al guardar, se crea un registro nuevo con `recorded_by` = usuario actual (nunca update).
- [x] Archivar (soft delete) y **restaurar** un hallazgo desde la sección plegable "Archivados"
  de cada pieza; ambas acciones auditadas.
- [x] Registro en `audit_logs` (evento `created` / `deleted` vía trait `Auditable`;
  `viewed` sobre el paciente al abrir la pantalla).
- [x] Ruta `GET patients/{patient}/odontogram` + `OdontogramController` invokable + enlace
  desde la ficha del paciente.
- [x] Livewire 4 instalado (`livewire/livewire ^4.4`) — primer módulo interactivo del repo.

## Criterios de aceptación

- [x] La numeración FDI usada está confirmada con la clínica (odontograma de referencia).
- [x] El odontograma es interactivo (seleccionar diente/superficie → cambiar estado) y
  refleja el estado guardado al recargar (estado vigente = hallazgo más reciente no archivado).
- [x] Cambiar el estado de un diente dispara un registro de auditoría con quién lo hizo.
- [x] Revisión `code-reviewer` sin hallazgos CRÍTICOS/ALTOS pendientes (veredicto ADVERTENCIA;
  ALTO y MEDIO corregidos, ver Registro de ejecución).

## Testing requerido

- [x] Historial: dos hallazgos en la misma superficie preservan ambas filas; el estado
  vigente es el más reciente (`OdontogramTest`).
- [x] Un rol sin permiso (visitante no autenticado) no puede abrir ni modificar el
  odontograma (`OdontogramPolicyTest`, `OdontogramTest`).
- [x] `audit_logs` al registrar y al archivar un hallazgo (`OdontogramTest`).
- [x] La nota clínica queda cifrada en la columna de MySQL (`OdontogramTest`).
- [x] Validación de alcance: estado de superficie no se registra en diente completo y
  viceversa; estado obligatorio; fecha no futura (`OdontogramTest`).
- [x] `forceDelete` solo superadmin (`OdontogramPolicyTest`).
- [x] Enum / `Dentition` cubiertos en `tests/Unit/ToothStatusTest.php`.
- [x] `Patient::forceDeleting` purga y audita los `odontogram_records` (`PatientForceDeleteTest`).

## Pendiente / dudas para el cliente

- **Orientación paciente en el diagrama:** la hoja física rotula "superior/inferior
  derecha/izquierda". El diagrama actual solo pone "Arcada superior/inferior" (el cuadrante
  ya está codificado en el número FDI). ¿Se quieren rótulos o marcadores D/I sobre cada mitad?
- **PHI en `<title>` de otras vistas:** el pase de UX quitó el nombre del paciente del
  `<title>` del odontograma (queda en historial del navegador / screen-share). El mismo
  patrón sigue en `patients/show`, `consultations/*` y `medical-history/edit` — conviene un
  pase aparte para corregirlo.
- **Modal de "Archivar":** usa una trampa de foco mínima (2 botones). Si estos diálogos
  crecen, evaluar el plugin `@alpinejs/focus`.
- Seguimiento BAJO de `code-reviewer`: test explícito de `403` a nivel de acción para
  `save`/`deleteRecord` (hoy sin impacto, ningún rol carece de acceso).

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| 2026-08-27 | Implementación completa del módulo: migración, enums, modelo, policy, componente Livewire interactivo (SVG por superficie + historial), controlador, ruta, factory y suite de pruebas. Instalado Livewire 4. | Implementación directa + `php artisan test` + Pint | 137 tests en verde (incluye 25 nuevos), Pint limpio, `npm run build` ok. |
| 2026-08-27 | Revisión y correcciones: FK `patient_id` de CASCADE → `restrictOnDelete()` (consistencia con el patrón de auditoría NOM-004 del Sprint anterior); `authorize('viewAny')` explícito en `select()` y en las computed `currentState`/`selectedToothHistory`; el panel "Estado vigente" oculta superficies de una pieza extraída/ausente. | Agente `code-reviewer` | Veredicto ADVERTENCIA → ALTO + MEDIO corregidos. Pendientes BAJOS de seguimiento: modal de confirmación (vs `wire:confirm`), test de denegación de acción. 137 tests en verde. |
| 2026-08-27 | Restauración de hallazgos archivados (opción B, a petición del cliente): sección plegable "Archivados" por pieza en el panel lateral, acción `restoreRecord` con `authorize('restore')` y scoping por `patient_id`, evento `restored` en `audit_logs` vía trait. | Implementación directa + `php artisan test` | 140 tests en verde (3 nuevos: restaura al estado vigente + auditoría, lista de archivados aislada por pieza, no se puede restaurar/archivar hallazgo de otro paciente). |
| 2026-08-27 | Pase de diseño UX/UI: piezas más grandes y números FDI con contraste real + área táctil 24 px, hover/foco en las zonas SVG, panel lateral `sticky` en escritorio y auto-scroll en móvil, diagrama con scroll horizontal enfocable por teclado, leyenda ampliada (marco = diente completo, cruz = extraído/ausente), Extraído (X sólida) vs Ausente (X punteada) distinguibles sin color, "Archivar" pasa de `window.confirm` a modal Alpine con trampa de foco, `aria-invalid`/`aria-describedby` en el formulario, `prefers-reduced-motion` global, `<title>` sin nombre de paciente (PHI), colores del catálogo clínico ajustados a ≥ 4:1 sobre blanco. | Agente `ux-ui-designer` | 140 tests en verde, build limpio. Dudas para el cliente abajo. |
