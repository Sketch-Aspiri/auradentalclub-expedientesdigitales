---
description: Bug fixer Laravel/MySQL para Expedientes Digitales Aura — reproduce con test rojo, corrige la causa raíz, verifica y deja listo para review
argument-hint: <descripción del bug> [ruta(s) o módulo afectado, opcional]
---

# /fix-issue — Bug Fixer · Expedientes Digitales Aura Dental Club

> Comando personalizado para este proyecto (Laravel 11 + Livewire + MySQL, expediente clínico bajo NOM-004/NOM-015/NOM-013). Sigue el patrón "reproducir con test en rojo → corregir a verde" (inspirado en `orch-fix-defect` y `tdd-guide`), con checklist de causa raíz de `silent-failure-hunter` y las prioridades de `php-reviewer`/`healthcare-reviewer`. Complementa a `/review` — este comando corrige, `/review` audita. Ante cualquier duda, `CLAUDE.md` (raíz) y `.claude/rules/testing.md` son la referencia autoritativa.

**Entrada**: $ARGUMENTS

Si `$ARGUMENTS` está vacío, pregunta al usuario qué está roto (síntoma observado, pasos para reproducir, y si toca datos de un paciente real o de prueba). No adivines el bug.

---

## Fase 1 — REPRODUCIR

1. Localiza el código relacionado con Grep/Glob (modelo, Policy, FormRequest, componente Livewire, migración) — no asumas la causa por el nombre del síntoma.
2. Si el bug es una excepción o error visible, reproduce el flujo exacto (misma ruta, mismo rol de usuario, mismos datos de entrada) antes de tocar nada.
3. **Escribe primero una prueba de regresión que falle (RED)** que capture el bug tal cual se reporta — no una prueba genérica. Ubícala en `tests/Unit` o `tests/Feature` según corresponda (ver `.claude/rules/testing.md`).
   - Si el bug involucra un rol específico (`doctor`/`administrador`/`superadmin`), la prueba debe fijar ese rol explícitamente.
   - Si el bug involucra datos clínicos sensibles, usa factories con datos mexicanos realistas, nunca datos reales de pacientes.
4. Corre la prueba y confirma que falla por la razón esperada (no por un typo o setup roto):

```bash
php artisan test --filter=NombreDeLaPrueba
```

Si no puedes reproducir el bug con una prueba automatizada (ej. bug de UI/Livewire visual), documenta los pasos de reproducción manual y sigue a la Fase 2 con precaución extra en la Fase 4 (verificación manual).

## Fase 2 — DIAGNOSTICAR causa raíz

No parches el síntoma. Antes de escribir el fix, identifica la causa raíz y descarta explícitamente estos patrones (checklist de `silent-failure-hunter`):

- [ ] ¿Hay un `catch` vacío o un `catch (\Exception $e) {}` que esté ocultando el error real?
- [ ] ¿Hay un fallback silencioso (`?? []`, `?? null`, `rescue()`) que esconde una falla en vez de propagarla?
- [ ] ¿Falta una validación en el `FormRequest` que debería haber rechazado el input antes de llegar aquí?
- [ ] ¿Falta una `Policy`/`Gate` que debería haber bloqueado la acción para ese rol?
- [ ] ¿El bug es de datos (migración, cast, `$fillable` incompleto) más que de lógica?
- [ ] Si toca `patient_files`, `consents`, `odontogram_records` u otra tabla clínica: ¿el bug pudo haber corrompido o expuesto datos de un paciente? Si sí, dilo explícitamente antes de continuar — puede requerir revisar `audit_logs` para entender el alcance real, no solo corregir el código.

Explica en una o dos frases cuál es la causa raíz antes de pasar a la Fase 3. Si tras investigar sigues sin estar seguro de la causa, dilo — no implementes un fix especulativo "a ver si pega".

## Fase 3 — CORREGIR (GREEN)

1. Implementa el cambio **mínimo** que corrige la causa raíz — no aproveches para refactorizar código no relacionado ni para "mejorar" cosas que no fallan (ver reglas globales de estilo del usuario).
2. Nunca corrijas ocultando el error: no agregues `catch` vacíos, no bajes el nivel de una validación, no debilites una `Policy` ni un default de seguridad de Laravel para "hacer pasar" el bug.
3. Si el fix toca una migración ya corrida, **no la edites** — crea una nueva migración correctiva.
4. Corre la prueba de regresión y confirma que ahora pasa:

```bash
php artisan test --filter=NombreDeLaPrueba
```

5. Corre la suite completa para descartar que el fix rompió algo más:

```bash
php artisan test
```

## Fase 4 — VERIFICAR

```bash
vendor/bin/pint --test
vendor/bin/phpstan analyse 2>/dev/null || vendor/bin/psalm 2>/dev/null
php artisan test --coverage --min=80 2>/dev/null || php artisan test
```

Si el bug estaba en una ruta/acción sobre datos de paciente (autorización, archivos, consentimientos, odontograma), aplica también el checklist CRÍTICO de `/review` antes de dar el fix por terminado — en particular Policy explícita, `audit_logs` y no exposición de PHI en logs/errores.

Si es un bug de UI/Livewire sin cobertura automatizada posible, verifica manualmente en el navegador el camino feliz y el caso que originó el bug (ver regla global "para cambios de UI, pruébalos en el navegador antes de reportar terminado").

## Fase 5 — REPORTAR

Resume así, sin crear archivos de reporte salvo que el usuario lo pida:

```
## Fix: <descripción corta del bug>

**Causa raíz**: <una o dos frases>
**Archivos modificados**: <lista>
**Prueba de regresión**: <archivo:test> — RED antes del fix, GREEN después
**Verificación**: Pint <pass/fail> · PHPStan/Psalm <pass/fail> · Tests <pass/fail, cobertura> · Verificación manual <si aplica>
**Impacto en datos clínicos**: Ninguno | <detalle y si se revisó audit_logs>
```

## Fase 6 — COMMIT (solo si el usuario lo pide)

No commitees automáticamente. Si el usuario pide commitear el fix:

- Mensaje tipo `fix: <descripción>` siguiendo el formato de `.claude/rules` (`ecc/common/git-workflow.md` del usuario).
- Nunca en el mismo commit mezclar el fix con refactors no relacionados.

---

## Casos especiales

- **No se puede reproducir**: dilo explícitamente, pide más contexto (versión, rol de usuario, datos exactos) en vez de adivinar un fix.
- **El "bug" es en realidad un requisito de negocio no definido** (ej. una regla de la NOM o de roles no cubierta en `CLAUDE.md`): no lo trates como bug de código — señálalo como pregunta al usuario/cliente (`CLAUDE.md` §12), no lo resuelvas por tu cuenta.
- **El bug está en una dependencia de terceros**: repórtalo como tal, no lo parches con un workaround permanente sin dejarlo documentado como deuda técnica temporal.
