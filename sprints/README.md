# Sprints — Expedientes Digitales Aura Dental Club

Roadmap de ejecución del MVP, derivado de `CLAUDE.md` §4 (módulos) y §6 (esquema). Cada sprint es un archivo `.md` en esta carpeta que se actualiza a medida que se trabaja con Claude Code: marca tareas, actualiza el **Estado** y agrega una fila en **Registro de ejecución** cada vez que se cierra un bloque de trabajo (una sesión, una feature, un fix relevante).

No se avanza al siguiente sprint con dependencias sin resolver, salvo que se documente explícitamente por qué se decidió adelantar.

## Cómo usar estos archivos con Claude Code

1. Antes de empezar un sprint, ábrelo y confirma que las dependencias listadas ya están en estado `Completado`.
2. Trabaja las tareas con `/fix-issue` para bugs y flujo normal de implementación para features nuevas — el agente `code-reviewer` se invoca automáticamente al modificar código (ver `CLAUDE.md` §10).
3. Al cerrar una tarea o sesión, agrega una fila en la tabla **Registro de ejecución** del sprint correspondiente (fecha, qué se hizo, comando/agente usado, resultado).
4. Antes de marcar un sprint como `Completado`, revisa su sección **Criterios de aceptación** y **Testing requerido**.
5. Los sprints 8 (auditoría/seguridad) y 9 (deploy) no son "features" — son gates de calidad/seguridad que se corren real, no se saltan por prisa.

## Roadmap

| # | Sprint | Depende de | Estado |
|---|--------|-----------|--------|
| 0 | [Fundación del proyecto](sprint-00-fundacion.md) | — | No iniciado |
| 1 | [Pacientes (ficha de identificación)](sprint-01-pacientes.md) | 0 | No iniciado |
| 2 | [Historia clínica / anamnesis](sprint-02-historia-clinica.md) | 1 | No iniciado |
| 3 | [Consultas](sprint-03-consultas.md) | 1, 2 | No iniciado |
| 4 | [Odontograma digital interactivo](sprint-04-odontograma.md) | 1 | No iniciado |
| 5 | [Consentimientos informados](sprint-05-consentimientos.md) | 1, 2 | No iniciado |
| 6 | [Hoja de evolución y control (costos)](sprint-06-evolucion-costos.md) | 1, 3 | No iniciado |
| 7 | [Carga de archivos](sprint-07-archivos.md) | 1 (y opcionalmente 3) | No iniciado |
| 8 | [Auditoría, seguridad y cumplimiento NOM](sprint-08-auditoria-seguridad.md) | 1–7 | No iniciado |
| 9 | [QA final, UAT y deploy a Hostinger](sprint-09-deploy-uat.md) | 8 | No iniciado |

Estados válidos: `No iniciado` · `En progreso` · `Bloqueado` · `Completado`. Actualiza esta tabla junto con el archivo del sprint.

## Notas de orden

- **Sprint 0 es obligatorio primero**: hoy el repo no tiene ni `composer.json` — no existe proyecto Laravel todavía, solo la configuración de `.claude/` y `CLAUDE.md`.
- **4 (Odontograma)** solo depende de Pacientes, no de Historia clínica/Consultas — puede adelantarse en paralelo a 2/3 si conviene.
- **7 (Archivos)** puede empezar en paralelo a 3 una vez que 1 esté listo, ya que `patient_files.consultation_id` es nullable (`CLAUDE.md` §6).
- **8 y 9 nunca se saltan** — son los gates de "esto toca datos clínicos reales" antes de que la clínica use el sistema en producción.
