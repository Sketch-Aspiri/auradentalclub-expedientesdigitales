# Sprint 4 — Odontograma digital interactivo

**Estado:** No iniciado
**Depende de:** Sprint 1 (puede correr en paralelo a Sprint 2/3)

## Objetivo

Estado estructurado por diente, con catálogo ampliable de estados y nota libre por pieza, visualizado como un odontograma interactivo.

## Alcance (CLAUDE.md §4.4, §6)

- `odontogram_records`: un registro por diente por paciente (estado actual, no histórico por defecto — confirmar si se necesita historial de cambios).
- Numeración **FDI** (11–18, 21–28, 31–38, 41–48) — asumida en `CLAUDE.md`, **confirmar con la clínica antes de fijarla en el código**.
- Catálogo de `status` ampliable: sano, caries, obturado, corona, extraído, endodoncia, implante, etc.
- `note` de texto libre por pieza, `updated_by` (quién hizo el último cambio).

## Tareas

- [ ] Confirmar con el cliente: numeración FDI, catálogo completo de estados esperado, y si se necesita **historial** de cambios por diente (auditoría de estados pasados) o solo el estado actual.
- [ ] Migración `odontogram_records`.
- [ ] Modelo `OdontogramRecord`, catálogo de `status` como enum o tabla de referencia (evalúa cuál según cuánto cambie el catálogo con el tiempo).
- [ ] `OdontogramPolicy`.
- [ ] Componente Livewire interactivo: representación visual de las 32 piezas, click para seleccionar diente y asignar estado + nota.
- [ ] Al guardar, crear/actualizar el registro y `updated_by` = usuario actual.
- [ ] Registro en `audit_logs`.

## Criterios de aceptación

- [ ] La numeración FDI usada está confirmada con la clínica, no asumida silenciosamente.
- [ ] El odontograma es interactivo (seleccionar diente → cambiar estado) y refleja el estado guardado al recargar.
- [ ] Cambiar el estado de un diente dispara un registro de auditoría con quién lo hizo.

## Testing requerido

- Test de que solo existe un `odontogram_record` "vigente" por diente por paciente (si no hay historial) o que el historial se preserva correctamente (si sí lo hay — según lo confirmado).
- Test de que un rol sin permiso no puede modificar el odontograma.
- Test de `audit_logs` al cambiar un estado.

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| | | | |
