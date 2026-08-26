---
description: Code review Laravel/MySQL para Expedientes Digitales Aura — seguridad clínica (NOM), PSR-12/Eloquent, autorización por rol y calidad
argument-hint: [ruta(s) o módulo | vacío = diff local sin commitear]
---

# /review — Code Review · Expedientes Digitales Aura Dental Club

> Comando personalizado para este proyecto (Laravel 11 + Livewire + MySQL, expediente clínico bajo NOM-004/NOM-015/NOM-013). Combina las prioridades de los agentes `php-reviewer`, `security-reviewer`/`healthcare-reviewer` y `database-reviewer`, y las skills `laravel-security` y `laravel-verification`, adaptadas a MySQL y al esquema de este repositorio. Ante cualquier duda, `CLAUDE.md` (raíz) y `.claude/rules/code-style.md` / `.claude/rules/testing.md` son la referencia autoritativa.

**Entrada**: $ARGUMENTS

---

## Fase 0 — Alcance

- Si `$ARGUMENTS` está vacío → revisa el diff local sin commitear.
- Si `$ARGUMENTS` trae rutas de archivo → revisa esas rutas completas (no solo el diff).
- Si `$ARGUMENTS` nombra un módulo del dominio (`odontograma`, `consentimientos`, `historia clínica`, `archivos`, `evolución`) → localiza los archivos relevantes (modelo, migración, policy, Form Request, componente Livewire, vistas) con Glob/Grep antes de revisar.

```bash
git status --porcelain
git diff --name-only HEAD
```

Si no hay repo git inicializado o no hay cambios, dilo explícitamente y pregunta si revisar un directorio/módulo específico en su lugar. No inventes hallazgos para justificar la ejecución.

## Fase 1 — Contexto

Antes de juzgar cualquier línea, lee:

1. `CLAUDE.md` (raíz) — especialmente secciones 3 (roles), 5 (datos sensibles/NOM), 6 (esquema) y 8 (convenciones).
2. `.claude/rules/code-style.md` y `.claude/rules/testing.md`.
3. El archivo completo de cada cambio (no solo el hunk del diff) — modelos, policies y migraciones relacionadas.

## Fase 2 — Revisión delegada (paralelo)

Lanza en paralelo, vía Agent tool, los revisores especializados ya instalados — cada uno con el contexto de qué archivos cambiaron y el resumen de `CLAUDE.md` relevante:

| Agente/skill | Foco en este proyecto |
|---|---|
| `php-reviewer` | PSR-12, `declare(strict_types=1)`, Eloquent (`$fillable`, N+1, FormRequest vs `$request->all()`), Livewire (`#[Rule]`, `authorize()`) |
| `healthcare-reviewer` | Exposición de PHI/datos clínicos en logs, errores, URLs o storage del cliente; integridad de `audit_logs`; consentimientos y firmas |
| `security-reviewer` | OWASP Top 10 general, secretos hardcodeados, CSRF, autenticación/sesión |
| `database-reviewer` | Úsalo solo como referencia de principios (es PostgreSQL) — traduce sus checks a MySQL con la sección 3 de este comando |

Si el cambio es pequeño (1-2 archivos, sin tocar auth/datos clínicos), puedes omitir la delegación y aplicar el checklist de la Fase 3 directamente para no sobrecargar la revisión.

## Fase 3 — Checklist propio del proyecto

### CRÍTICO — Datos clínicos y cumplimiento (bloquea)

- [ ] Ninguna ruta/acción sobre `patients`, `medical_histories`, `consultations`, `consents`, `odontogram_records`, `treatment_records` o `patient_files` carece de **Policy** explícita por rol (`doctor`/`administrador`/`superadmin`) — nunca asumir "ya está protegido".
- [ ] Toda acción `viewed`/`created`/`updated`/`deleted` sobre datos de paciente genera su registro en `audit_logs` (`user_id`, `patient_id`, `action`, `auditable_type/id`, `ip_address`).
- [ ] Campos especialmente sensibles (`allergies`, `current_medications`, notas clínicas, antecedentes) usan cast `encrypted` de Eloquent — verifica en el modelo, no asumas.
- [ ] No hay datos clínicos (nombre de paciente, diagnóstico, alergias, etc.) en `Log::`, mensajes de excepción, parámetros de URL, o `dd()`/`dump()` olvidados.
- [ ] Archivos subidos (`patient_files`, radiografías/fotos/documentos, firmas de consentimiento) se guardan en disco **no público** (`local`, no `public`) y se sirven vía ruta controlada con `authorize()`, nunca URL directa.
- [ ] Cambios al esquema del odontograma, consentimientos o historia clínica no contradicen la estructura NOM-004/NOM-015/NOM-013 descrita en `CLAUDE.md` §5 sin haberlo confirmado antes.
- [ ] Ninguna nueva capacidad de rol se introdujo sin estar definida en `CLAUDE.md` §3 (si hace falta, señálalo como pregunta, no como silencio).

### CRÍTICO — Seguridad general

- [ ] Sin credenciales/API keys/tokens hardcodeados.
- [ ] Sin SQL crudo con interpolación de strings (`DB::raw`, `whereRaw`, `DB::statement` con variables sin bind) — usa Eloquent o bindings parametrizados.
- [ ] Sin `$guarded = []` ni `Model::create($request->all())` — siempre `$fillable` + `FormRequest::validated()`.
- [ ] Blade: sin `{!! $variable !!}` sobre contenido de usuario/clínico sin sanitizar.
- [ ] Formularios de estado (crear/editar/eliminar expediente, consentimiento, evolución) llevan `@csrf` y pasan por `FormRequest::authorize()`.
- [ ] Subida de archivos valida `mimes`, tamaño máximo y, si aplica, dimensiones — nunca confía en la extensión del nombre de archivo.

### ALTO — Laravel / Eloquent / Livewire

- [ ] N+1: relaciones cargadas con `with()`/`load()` en listados de pacientes/consultas/odontograma, no en loops.
- [ ] Lógica de negocio pesada fuera de controladores/componentes Livewire → movida a servicios/actions (ver `CLAUDE.md` §8).
- [ ] Migraciones nuevas (no editar una ya corrida) con `up()`/`down()` reversibles y nombre descriptivo `Y_m_d_His_*`.
- [ ] Componentes Livewire con responsabilidad única; validación con `#[Rule]` o `rules()`, autorización explícita en `mount()`/acciones.
- [ ] `declare(strict_types=1)` y type hints en métodos públicos nuevos.

### ALTO — MySQL / esquema (adaptado de principios Postgres a MySQL)

- [ ] Claves foráneas (`patient_id`, `doctor_id`, `consultation_id`, etc.) indexadas y con `ON DELETE` explícito (evita `CASCADE` sobre historia clínica salvo que sea intencional y confirmado).
- [ ] Tipos correctos: `DECIMAL` (no `FLOAT`/`DOUBLE`) para `cost`/`amount_paid`/`balance`; `ENUM` o columna con `CHECK`/validación de aplicación para catálogos cerrados (`status` del odontograma, `role`, `file_type`); `DATE`/`DATETIME` según corresponda (no `VARCHAR` para fechas).
- [ ] Sin `SELECT *` en queries de producción; listados paginados (`paginate()`), nunca cargar todo el historial de un paciente sin límite.
- [ ] Collation/charset consistente (`utf8mb4`) para soportar acentos y ñ en nombres/direcciones mexicanas.
- [ ] Índices en columnas usadas para búsqueda/filtro de expedientes (ej. `full_name`, `phone`) si el módulo de búsqueda los requiere.

### MEDIO/BAJO — Calidad general

- [ ] PSR-12, funciones <50 líneas, archivos <800 líneas, anidación <4 niveles (regla global del usuario).
- [ ] Sin `dd()`/`dump()`/`var_dump()` en código commiteado.
- [ ] Nombres en inglés (código) / español (UI), consistente con `CLAUDE.md` §8.
- [ ] Pruebas nuevas o actualizadas para Form Requests, Policies y el flujo tocado (ver `.claude/rules/testing.md`).

## Fase 4 — Validación automática

Corre lo que exista en el proyecto (omite silenciosamente lo que no esté instalado, pero dilo):

```bash
composer validate
vendor/bin/pint --test
vendor/bin/phpstan analyse 2>/dev/null || vendor/bin/psalm 2>/dev/null
php artisan test --coverage --min=80 2>/dev/null || php artisan test
composer audit
php artisan migrate --pretend
```

Registra pass/fail de cada uno — no lo omitas del reporte final.

## Fase 5 — Reporte

Formato por hallazgo:

```
[SEVERIDAD] Título del hallazgo
Archivo: ruta/al/archivo.php:LÍNEA
Problema: qué está mal y qué escenario concreto lo dispara
Fix: qué cambiar
```

Cierra siempre con:

```
## Resumen de revisión

| Severidad | Cantidad | Estado |
|-----------|----------|--------|
| CRÍTICO   | N        | ...    |
| ALTO      | N        | ...    |
| MEDIO     | N        | ...    |
| BAJO      | N        | ...    |

| Validación                  | Resultado        |
|------------------------------|------------------|
| Pint (PSR-12)                | Pass/Fail/Omitido |
| PHPStan/Psalm                | Pass/Fail/Omitido |
| Pest/PHPUnit + cobertura     | Pass/Fail/Omitido |
| composer audit               | Pass/Fail/Omitido |
| migrate --pretend            | Pass/Fail/Omitido |

Veredicto: APROBADO | ADVERTENCIA | BLOQUEADO
```

## Criterios de veredicto

- **APROBADO**: sin hallazgos CRÍTICO ni ALTO, validaciones en verde. Un reporte limpio con cero hallazgos es un resultado válido — no fuerces hallazgos para justificar la revisión.
- **ADVERTENCIA**: solo hallazgos ALTO, o alguna validación no disponible en el entorno (documentarlo).
- **BLOQUEADO**: cualquier hallazgo CRÍTICO (especialmente PHI/NOM, autorización faltante o auditoría faltante), o una validación en rojo.

No apruebes lógica clínica o de autorización de la que no estés seguro — en ese caso, marca "NECESITA CONFIRMACIÓN DEL CLIENTE" en vez de aprobar u objetar a ciegas, siguiendo `CLAUDE.md` §12.
