# Sprint 6 — Hoja de evolución y control (con costos)

**Estado:** No iniciado
**Depende de:** Sprint 1, Sprint 3

## Objetivo

Bitácora por cita de procedimiento realizado, materiales/insumos usados, costo, monto pagado ("a cuenta") y saldo. Es registro informativo dentro del expediente, no un módulo contable completo (`CLAUDE.md` §4.6).

## Alcance (CLAUDE.md §4.6, §6)

- `treatment_records`: `patient_id`, `consultation_id` (nullable), `doctor_id`, `treatment_date`, `procedure_performed`, `materials_used`, `cost`, `amount_paid`, `balance`.
- `balance` se calcula (probablemente `cost - amount_paid`, acumulado si hay pagos parciales en varias filas — **confirmar la regla exacta de acumulación con el cliente**, especialmente si el saldo es por registro o por paciente).

## Tareas

- [ ] Confirmar con el cliente: ¿el saldo (`balance`) es por fila (un tratamiento, un pago) o un acumulado corrido por paciente a través de múltiples `treatment_records`? Esto cambia el diseño del cálculo.
- [ ] Migración `treatment_records` con `cost`/`amount_paid`/`balance` como `DECIMAL` (nunca `FLOAT`/`DOUBLE`, ver `.claude/rules/api-conventios.md`).
- [ ] Modelo `TreatmentRecord`, accessor o servicio para calcular `balance` según lo confirmado.
- [ ] `TreatmentRecordPolicy`.
- [ ] Form Request de registro de tratamiento/pago.
- [ ] Vista de bitácora por paciente (cronológica) con totales visibles (total cobrado, total pagado, saldo).
- [ ] Registro en `audit_logs`.

## Criterios de aceptación

- [ ] El cálculo de saldo coincide exactamente con la regla confirmada por el cliente.
- [ ] Los montos usan `DECIMAL` en la base de datos, sin errores de redondeo de punto flotante.
- [ ] No se implementó nada de facturación fiscal/cortes de caja (fuera de alcance, `CLAUDE.md` §12).

## Testing requerido

- Test unitario del cálculo de `balance` con varios escenarios (pago completo, parcial, múltiples abonos).
- Test de que `cost`/`amount_paid` rechazan valores negativos o no numéricos vía Form Request.
- Policy test por rol.
- Test de `audit_logs`.

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| | | | |
