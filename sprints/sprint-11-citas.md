# Sprint 11 — Agenda de citas

**Estado:** No iniciado
**Depende de:** Sprint 1 (Pacientes), Sprint 3 (Consultas)

## Objetivo

Agregar un módulo de **Agenda**: un calendario para agendar citas a pacientes ya existentes, ubicado entre Pacientes y Odontograma tanto en la barra lateral como en el dashboard. La próxima cita agendada de cada paciente debe alimentar la columna «Próxima consulta» del listado de pacientes, hoy un placeholder sin dato.

## Contexto y conflicto de alcance resuelto

El sistema registra el expediente clínico, pero no tenía forma de **planear** la atención: solo se documentaba lo que ya había ocurrido (`consultations`). La columna «Próxima consulta» existe desde el rediseño del 2026-08-28 como placeholder explícito, a la espera de este módulo.

`CLAUDE.md` §4 decía hasta ahora: *«No implementes funcionalidad de citas o inventario en este proyecto — eso vive en sistemas separados»*, y §1 menciona un sistema hermano «Citas en Línea» bajo el mismo dominio `auradentalclub.com`. **El cliente decidió el 2026-08-28 construir la agenda aquí**, con el esquema preparado para sincronizarse con ese sistema cuando exista. Este sprint incluye actualizar `CLAUDE.md` §4 y §12 para que quede registrada la decisión — si la regla se queda contradiciendo al código, una revisión futura marcará el módulo como fuera de alcance y podría intentar revertirlo.

## Alcance (CLAUDE.md §4/§6, actualizado en este sprint)

- `appointments`: `patient_id`, `doctor_id`, `starts_at`, `duration_minutes`, `reason`, `status`, más `uuid`/`source`/`external_id` para sincronización futura.
- Vista de calendario mensual con panel del día seleccionado.
- Pantallas de agendar/editar/ver una cita, reutilizando el patrón de `consultations`.
- Columna «Próxima consulta» del listado de pacientes dejando de ser placeholder.
- Entrada de navegación «Agenda» en `nav-links.blade.php` y tarjeta activa en `dashboard.blade.php`, entre Pacientes y Odontograma.

**Fuera de alcance de este sprint** (ver Pendientes): API JSON o webhooks reales hacia el futuro sistema de Citas en Línea, vista semanal del calendario, duración configurable por tipo de cita, y locking pesimista contra traslapes concurrentes.

## Decisiones confirmadas con el cliente (CLAUDE.md §13)

1. **Alcance:** el módulo se construye en este sistema, no en uno aparte. Se actualiza `CLAUDE.md` §4 y §12 como parte del sprint.
2. **Nombre:** «Agenda» en la interfaz. Tabla `appointments`, rutas `appointments.*`, modelo `Appointment`. **No se llama «Consultas»** porque `consultations.*` ya está ocupado por cinco rutas del expediente clínico (`consultations.show/edit/update/destroy/restore`), y `nav-links.blade.php` usa ese prefijo para resaltar el ítem **Pacientes** — reutilizarlo rompería el espacio de nombres y el resaltado de navegación.
3. **Datos de una cita:** paciente, doctor asignado, fecha/hora de inicio, duración en minutos (**default 60, confirmado que puede cambiar a futuro**), motivo (texto libre, dato clínico), estado.
4. **Permisos:** los tres roles (`doctor`, `administrador`, `superadmin`) gestionan toda la agenda — calcado de `ConsultationPolicy` (todos pueden todo salvo `forceDelete`, solo `superadmin`).
5. **Sincronización futura:** el esquema queda preparado (`uuid`, `source`, `external_id` único) para cuando exista el sistema de Citas en Línea. **En este sprint no se construye API, ni webhooks, ni autenticación tipo Sanctum** — eso requiere confirmación explícita aparte cuando el otro sistema exista (`.claude/rules/api-conventios.md`, sección "Si en el futuro se agrega una API JSON real").
6. **Fecha/hora:** se guarda en la zona horaria de la app (`America/Cancun`, ya configurada en `config/app.php`), no en UTC. Cancún es UTC−05:00 todo el año sin horario de verano desde 2015, así que no hay horas ambiguas; introducir UTC solo para esta tabla crearía una convención mixta con el resto del sistema (todas las demás fechas de dominio usan el default de la app). La interoperabilidad futura se resuelve serializando con offset explícito (`toIso8601String()`) en el borde de integración, no cambiando el almacenamiento.
7. **Cifrado:** el motivo (`reason`) se cifra en reposo igual que las notas clínicas de `Consultation`. Consecuencia aceptada: no se puede buscar ni ordenar por motivo en SQL. `starts_at`, `duration_minutes`, `status`, `doctor_id`, `patient_id` **no** se cifran porque el calendario y la validación de traslapes dependen de filtrarlos y ordenarlos en la base de datos.
8. **Auditoría:** cancelar una cita se audita como `updated` (el cambio de `status` ya queda registrado), no como una acción nueva — evita una migración de `ALTER TABLE` sobre el enum de `audit_logs.action` sin necesidad real.
9. **Traslapes:** se valida en la aplicación (Form Request), no con una constraint de base de datos — MySQL no soporta exclusión por rango y la duración es variable. Se acepta la ventana de carrera teórica en concurrencia (ver Pendientes) dado el volumen de un solo consultorio.

## Tareas

### Fase 1 — Datos
- [ ] Enum `App\Enums\AppointmentStatus` (`scheduled`, `confirmed`, `attended`, `cancelled`, `no_show`) con `label()` y `isActive(): bool` (true solo para `scheduled`/`confirmed` — único criterio de "próxima cita" y de "bloquea el horario").
- [ ] Migración `create_appointments_table`: `id`, `uuid` único, `patient_id` (FK `restrictOnDelete`), `doctor_id` (FK a `users`), `starts_at` (`dateTime`), `duration_minutes` (`unsignedSmallInteger`, default 60), `reason` (`text` nullable), `status` (enum, default `scheduled`), `source` (enum `local`/`online`, default `local`), `external_id` (`string` nullable único), `softDeletes()`, `timestamps()`. Índices `['doctor_id','starts_at']` y `['patient_id','starts_at']`.
- [ ] Modelo `App\Models\Appointment`: traits `Auditable, HasFactory, SoftDeletes`; `casts()` como método (`starts_at` → `datetime`, `duration_minutes` → `integer`, `reason` → `encrypted`, `status` → `AppointmentStatus::class`); `auditPatientId()` sobrescrito; relaciones `patient()`, `doctor()`; accessor `endsAt` derivado (`starts_at + duration_minutes`, nunca columna propia); `booted()` asigna `uuid` en `creating`; scopes `active()`, `upcoming()`, `inRange()`.
- [ ] `Patient`: relaciones `appointments(): HasMany` y `nextAppointment(): HasOne` (mismo patrón que `latestConsultation()`, con `ofMany` filtrando `active()->upcoming()`).
- [ ] Añadir `appointments()` a la lista de `Patient::booted()` → `static::forceDeleting(...)` para que el borrado permanente purgue también las citas y quede auditado.
- [ ] `database/factories/AppointmentFactory.php` con motivos en español real («Limpieza dental de rutina», «Revisión de ortodoncia») y estados `->cancelled()`, `->past()`, `->forDoctor()`.
- [ ] `App\Policies\AppointmentPolicy`: copia de `ConsultationPolicy` (los tres roles en todo, `forceDelete` solo `superadmin`).

### Fase 2 — Validación y controlador
- [ ] `app/Rules/NoAppointmentOverlap.php`: rechaza una cita si se traslapa con otra **activa** del mismo doctor (excluyendo la propia al editar).
- [ ] Trait `app/Http/Requests/Concerns/ValidatesAppointmentData.php` con reglas compartidas y `doctorIdRule()` (igual patrón que `ValidatesConsultationData`).
- [ ] `StoreAppointmentRequest` / `UpdateAppointmentRequest`: `starts_at` `required|date|after_or_equal:today`, `duration_minutes` `required|integer|min:15|max:480`, `reason` `nullable|string|max:1000`, `status` `Rule::enum(...)`, `doctor_id` con la regla compartida. `UpdateAppointmentRequest::prepareForValidation()` usa `merge()` para reponer el `doctor_id` original si el usuario es doctor — **replicar la defensa de `UpdateConsultationRequest`**, que corrigió un bypass real por query string.
- [ ] `App\Http\Controllers\AppointmentController`: resource clásico; `authorize()` explícito en `index/create/show/edit/destroy/restore`; `store`/`update` autorizan vía Form Request; helper `doctorsForSelector()` extraído a un trait/action compartido con `ConsultationController` en vez de duplicarlo.
- [ ] Rutas en `routes/web.php`, dentro del grupo `auth`, entre `patients.*` y `odontogram`: `GET appointments` → `appointments` (calendario, nombre plano como `odontogram`), `Route::resource('appointments', ...)->except(['index'])`, `PUT appointments/{appointment}/restore` → `appointments.restore` (`withTrashed()`).

### Fase 3 — Vistas de cita
- [ ] `resources/views/appointments/{create,edit,show,_form}.blade.php`, siguiendo el patrón de `consultations/_form.blade.php` (selector de paciente ya existente, selector de doctor condicional al rol, campos de fecha/hora/duración/motivo/estado).
- [ ] Botones con `<x-button>` (obligatorio para acciones nuevas, `.claude/agents/ux-ui-designer.md`), acciones destructivas con `<x-confirm-modal>`.
- [ ] `<title>` de estas pantallas sin nombre de paciente ni motivo (PHI, CLAUDE.md §5), igual que el resto del expediente.

### Fase 4 — Calendario
- [ ] `App\Livewire\Appointments\Calendar` + vista `resources/views/livewire/appointments/calendar.blade.php`: vista mensual con panel del día seleccionado (sin vista semanal en este sprint).
- [ ] `#[Url] $month` (`Y-m`) y `#[Url] $day`; navegación mes anterior/siguiente y botón «Hoy»; `mount()` autoriza `viewAny, Appointment::class`, el método que arma la query re-autoriza.
- [ ] Una sola consulta por mes (`inRange()->with('patient:id,full_name', 'doctor:id,name')`), agrupada en PHP por fecha — sin N+1 por celda.
- [ ] Rejilla de mes en `<table>` con `<caption class="sr-only">` y `<th scope="col">`; cada celda es un botón con nombre accesible completo (ej. «12 de septiembre, 2 citas»); **el motivo nunca va en `aria-label`/`title`**. Sin librerías JS externas — Blade + Tailwind + Alpine, como el odontograma.
- [ ] Agendar desde el calendario navega a `appointments.create` con la fecha preseleccionada por query string (no un formulario dentro de un modal).

### Fase 5 — Integración con pacientes y navegación
- [ ] `PatientList::patients()`: añadir `nextAppointment` al eager load (`with(['latestConsultation.doctor:id,name', 'nextAppointment'])`).
- [ ] `patient-list.blade.php`: sustituir el placeholder de «Próxima consulta» (tabla `xl:table-cell`) por `{{ $patient->nextAppointment?->starts_at?->translatedFormat('d M Y, H:i') ?? '—' }}` — nunca el motivo; **eliminar el comentario Blade que marcaba la integración pendiente**; **agregar la columna también a la vista de tarjetas (`lg:hidden`)**, donde hoy no existe.
- [ ] `nav-links.blade.php`: nuevo ítem «Agenda» entre Pacientes y Odontograma, `$isAppointments = request()->routeIs('appointments', 'appointments.*')`.
- [ ] `dashboard.blade.php`: tarjeta activa de «Agenda» (no «Próximamente») como tercer módulo del grid.

### Fase 6 — Documentación
- [ ] `CLAUDE.md` §4: registrar que el módulo de agenda vive en este sistema, con la fecha de la decisión.
- [ ] `CLAUDE.md` §12 ("Fuera de alcance"): ajustar la mención a Citas en Línea para reflejar que la agenda local sí vive aquí y que la integración con el sistema externo queda pendiente y sin API todavía.
- [ ] `sprints/README.md`: agregar la fila del Sprint 11 a la tabla de sprints.

## Criterios de aceptación

- Los tres roles pueden ver el calendario, agendar, reprogramar y cancelar citas de cualquier paciente.
- Dos citas activas del mismo doctor con horarios que se traslapan no pueden coexistir; una cita cancelada no bloquea el horario; otro doctor sí puede ocupar el mismo horario.
- La columna «Próxima consulta» del listado de pacientes muestra la próxima cita activa (o `—`), tanto en la vista de tabla (`xl`) como en la de tarjetas (móvil), sin exponer el motivo.
- El ítem «Agenda» aparece en la barra lateral y en el dashboard, entre Pacientes y Odontograma, y queda resaltado como activo en sus propias rutas.
- `CLAUDE.md` no contradice al código: §4 y §12 reflejan que la agenda vive en este sistema.
- Ningún dato clínico (motivo) aparece en `title`, `aria-label`, URLs o el `<title>` del navegador.

## Testing requerido

- `tests/Feature/Appointments/AppointmentTest.php`: flujo end-to-end (agendar → ver → reprogramar → cancelar) con verificación de `audit_logs` en cada paso, y test de cifrado en reposo de `reason` comparando la columna cruda (`DB::table('appointments')`) contra el accessor.
- `tests/Feature/Appointments/AppointmentPolicyTest.php`: los tres roles con acceso completo (`dataset`), visitante no autenticado redirige a login, `forceDelete` restringido a `superadmin`.
- `tests/Feature/Appointments/AppointmentRequestTest.php`: fecha pasada rechazada, duración fuera de rango, `doctor_id` que no corresponde a un doctor, y test de regresión del bypass de `doctor_id` por query string.
- `tests/Feature/Appointments/AppointmentOverlapTest.php`: traslape exacto, parcial al inicio, parcial al final, cita contenida; cita cancelada no bloquea; otro doctor sí puede; editar una cita no choca consigo misma.
- `tests/Feature/Appointments/AppointmentCalendarTest.php`: `Livewire::test(...)` de navegación entre meses, filtrado correcto por mes, y conteo de queries para confirmar que no hay N+1.
- Ampliar `tests/Feature/Patients/PatientListTest.php`: la columna muestra la próxima cita activa, ignora canceladas y pasadas, y muestra `—` si no hay ninguna.
- `PatientForceDeleteTest` (ya existente) debe seguir pasando sin ajustes tras añadir `appointments` a `forceDeleting` — si falla, es la señal de que se olvidó ese paso.
- Suite completa (`php artisan test`) en verde antes de cerrar el sprint; hoy son 212 tests.

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente | Resultado |
|---|---|---|---|
| 2026-08-28 | Planeación del sprint (exploración de `Consultation`/`PatientList`/navegación + diseño de esquema, permisos y calendario) | `/plan` | Plan aprobado por el cliente; este archivo documenta las decisiones |

## Pendientes (backlog, sin bloqueo)

- Ventana de carrera en la validación de traslapes: dos agendamientos simultáneos sobre el mismo hueco podrían colarse ambos. Resolver con transacción + `lockForUpdate()` si el volumen del consultorio lo llega a justificar.
- Vista semanal del calendario (solo mensual + panel de día en este sprint).
- Duración configurable por tipo de procedimiento (hoy default fijo de 60 minutos para todas las citas).
- Integración real con el sistema de Citas en Línea: el esquema queda listo (`uuid`, `source`, `external_id`), pero falta el transporte (API JSON, webhooks o lo que se decida). Requiere confirmación explícita del cliente cuando ese sistema exista — `.claude/rules/api-conventios.md` lo marca como fuera de alcance sin confirmarlo antes.
- Al añadir futuros módulos clínicos con relación a `Patient`, seguir conectándolos a `Patient::forceDeleting()` — el test `PatientForceDeleteTest` fallará si se olvida.
