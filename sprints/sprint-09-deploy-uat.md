# Sprint 9 — QA final, UAT y deploy a Hostinger

**Estado:** No iniciado
**Depende de:** Sprint 8

## Objetivo

Llevar el sistema a un entorno real (Hostinger) para que la clínica lo valide (UAT) antes de usarlo con pacientes reales, y luego promoverlo a producción.

## Alcance

- Deploy a staging en Hostinger siguiendo `.claude/skills/deploy/SKILL.md`.
- Validación de usuario (UAT) con al menos un `doctor` y un `administrador`/`superadmin` reales de la clínica.
- Deploy a producción solo después de UAT aprobado.

## Tareas

- [ ] Confirmar con el cliente el tipo de plan de Hostinger contratado (ver §1 de la skill de deploy).
- [ ] Seguir la skill de deploy paso a paso para **staging** primero, nunca directo a producción.
- [ ] Crear usuarios reales de prueba (no de producción todavía) para que la clínica pruebe los flujos completos: alta de paciente → historia clínica → consulta → odontograma → consentimiento → archivo → evolución/costos.
- [ ] Recoger feedback de UAT y convertirlo en tareas puntuales (bugs → `/fix-issue`, ajustes → sprints de vuelta a los módulos correspondientes si son grandes).
- [ ] Repetir el checklist de verificación post-deploy de la skill de deploy en staging.
- [ ] Solo tras aprobación explícita del cliente: deploy a producción, con los datos reales de la clínica.
- [ ] Confirmar backups de base de datos activos en producción antes de que se capture el primer paciente real.

## Criterios de aceptación

- [ ] UAT completado y aprobado explícitamente por el cliente (no asumido).
- [ ] Checklist de verificación post-deploy de la skill de Hostinger, en verde, en staging y en producción.
- [ ] Backups configurados y confirmados antes de datos reales.
- [ ] Ningún hallazgo CRÍTICO del Sprint 8 quedó sin resolver antes de ir a producción.

## Testing requerido

- E2E manual de los flujos críticos completos en staging (no solo tests automatizados — ver regla global de "probar en el navegador antes de dar por terminado" para cambios de UI).

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| | | | |
