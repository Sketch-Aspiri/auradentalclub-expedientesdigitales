# Sprint 1 — Pacientes (ficha de identificación)

**Estado:** No iniciado
**Depende de:** Sprint 0

## Objetivo

Alta, edición, búsqueda y filtros de pacientes — la ficha de identificación base sobre la que cuelgan todos los demás módulos clínicos.

## Alcance (CLAUDE.md §4.1, §6)

- CRUD de `patients`: `full_name`, `birth_date`, `sex`, `occupation`, `marital_status`, `address`, `phone`, `email`, `emergency_contact_name`, `emergency_contact_phone`.
- `age` calculado desde `birth_date` (accessor de Eloquent), no almacenado.
- Búsqueda y filtros de expedientes (por nombre, teléfono, al menos).
- Autorización por rol: quién puede crear/editar/eliminar/ver pacientes (`CLAUDE.md` §3 — confirmar con el usuario/cliente si hay dudas sobre si `doctor` puede crear pacientes o solo `administrador`/`superadmin`).

## Tareas

- [ ] Migración `patients` según el esquema de `CLAUDE.md` §6.
- [ ] Modelo `Patient` con `$fillable`, casts apropiados, accessor `age`.
- [ ] `PatientPolicy` cubriendo los tres roles explícitamente.
- [ ] `StorePatientRequest` / `UpdatePatientRequest` (Form Requests) con validación completa.
- [ ] Controlador de recurso (`PatientController`) o componente Livewire equivalente, con `$this->authorize(...)` en cada acción.
- [ ] Vista de listado con búsqueda/filtro (paginado — sin cargar todos los pacientes de golpe, ver `.claude/rules/api-conventios.md`).
- [ ] Vista de alta/edición siguiendo la identidad visual (`CLAUDE.md` §7).
- [ ] Conectar el mecanismo de `audit_logs` del Sprint 0: cada `viewed`/`created`/`updated`/`deleted` sobre un paciente queda registrado.
- [ ] Factory de `Patient` con datos mexicanos realistas (`.claude/rules/testing.md`).

## Criterios de aceptación

- [ ] Un `administrador`/`superadmin` puede crear, editar, buscar y (si aplica) eliminar pacientes.
- [ ] Un `doctor` tiene el acceso que se haya confirmado con el cliente — no una suposición sin confirmar (`CLAUDE.md` §3, §13).
- [ ] Cada acción sobre un paciente genera su registro en `audit_logs`.
- [ ] No hay ninguna ruta de pacientes sin `Policy` explícita.

## Testing requerido

- Form Request: casos válidos e inválidos por campo relevante (fechas, teléfono, email).
- Policy: los tres roles, explícitamente, para cada acción (view/create/update/delete).
- Feature test: flujo completo de alta de paciente de punta a punta.
- Verifica que `audit_logs` recibe un registro al crear/editar/ver un paciente.

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| | | | |
