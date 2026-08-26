# CLAUDE.md — Sistema de Expedientes Digitales de Pacientes (Clínica Dental Aura)

Este archivo guía a Claude Code en el desarrollo de este proyecto. Léelo completo antes de escribir código.

## 1. Resumen del proyecto

Plataforma web para el registro y consulta del historial clínico de los pacientes de Clínica Dental Aura (un solo consultorio). Incluye datos personales y clínicos, odontograma digital, historial de tratamientos, carga de archivos (radiografías/fotos/documentos), alergias/antecedentes médicos y consentimientos con firma digital. Es un sistema **independiente** (su propia base de datos y autenticación) que eventualmente convivirá bajo el dominio `auradentalclub.com` junto a otros dos sistemas (Citas en Línea, Inventarios), pero no comparte datos ni sesión con ellos.

## 2. Stack técnico

- **Backend:** Laravel 11, PHP 8.3
- **Frontend:** Blade + Livewire (sin Vue/React/Inertia)
- **Estilos:** Tailwind CSS *(asumido por defecto — confirmar o cambiar si prefieres otra cosa)*
- **Base de datos:** MySQL
- **Punto de partida:** Laravel limpio, sin starter kit (Breeze/Jetstream) — la autenticación se construye a medida
- **Almacenamiento de archivos:** disco local del servidor (Hostinger), usando el filesystem `local`/`public` de Laravel
- **Testing:** Pest (o PHPUnit si se prefiere sintaxis clásica) desde el inicio del proyecto
- **Control de versiones:** Git, repositorio privado en GitHub

## 3. Roles y permisos

Tres roles, sin soporte multi-sucursal (es una sola ubicación):

| Rol | Alcance típico |
|---|---|
| `superadmin` | Acceso total, incluida configuración del sistema y gestión de usuarios/roles |
| `administrador` | Gestión operativa: pacientes, expedientes, reportes, usuarios (sin configuración crítica del sistema) |
| `doctor` | Acceso a expedientes de pacientes, odontograma, historial de tratamientos, notas de consulta |

Usa Laravel Policies/Gates (o un paquete como `spatie/laravel-permission` si se necesita más granularidad) para el control de acceso. No asumas capacidades no definidas aquí sin confirmarlo — al añadir una nueva acción protegida, pregunta a qué rol(es) corresponde en vez de adivinar.

## 4. Módulos del MVP (todos son prioridad)

1. **Datos personales y clínicos del paciente** — alta, edición, búsqueda y filtros de expedientes (ficha de identificación).
2. **Historia clínica / anamnesis** — antecedentes patológicos y no patológicos, capturados una vez por paciente y editables.
3. **Consultas** — registro por cita: signos vitales, exploración bucal, motivo de consulta, diagnóstico, plan de tratamiento, pronóstico, riesgos y alternativas.
4. **Odontograma digital interactivo** — estado estructurado por diente (catálogo ampliable: sano, caries, obturado, corona, extraído, endodoncia, implante, etc.) más nota libre por pieza. Numeración FDI asumida — confirmar con la clínica.
5. **Consentimientos informados** — formulario genérico con campo `type` (general, extracción, ampliable a futuro), incluye tabla de procedimientos/costos, firmas de paciente/médico/testigos.
6. **Hoja de evolución y control (con costos)** — bitácora por cita de procedimiento realizado, materiales/insumos usados, costo, monto pagado ("a cuenta") y saldo. Vive dentro de este sistema, no es un módulo de facturación aparte.
7. **Carga de archivos** — radiografías, fotos, documentos, asociados al expediente o a una consulta específica.
8. **Alergias y antecedentes médicos** — parte de la historia clínica (punto 2).

No implementes funcionalidad de citas o inventario en este proyecto — eso vive en sistemas separados. El manejo de saldos aquí es informativo/registro por cita, no un módulo contable completo (sin cortes de caja, facturación fiscal, etc.) salvo que se indique lo contrario.

## 5. Datos sensibles y seguridad (requisito importante)

Este sistema digitaliza el expediente clínico físico actual de la clínica, el cual está construido conforme a normas oficiales mexicanas. Al diseñar campos y flujos, respeta la estructura de:

- **NOM-004-SSA3-2012** — Del expediente clínico (estructura general: ficha de identificación, historia clínica, exploración física, diagnóstico, plan de tratamiento).
- **NOM-015-SSA2-2015** — Referenciada junto con la anterior en el expediente clínico legal de la clínica.
- **NOM-013-SSA2-2015** — Para la prevención y control de enfermedades bucales, referenciada específicamente en el consentimiento de extracciones dentales.

Esto no es una interpretación legal de Claude — si hay dudas sobre el cumplimiento exacto de estas normas, se debe confirmar con el cliente o un asesor legal/de salud. Trata este sistema con el mismo cuidado que un sistema de salud real:

- **Nunca** expongas datos clínicos en logs, mensajes de error o URLs.
- Encripta en reposo los campos especialmente sensibles (alergias, antecedentes médicos, notas clínicas) usando el casting `encrypted` de Eloquent, o al menos evalúalo y coméntalo al proponer el esquema.
- Implementa **logs de auditoría** para acciones sobre expedientes: quién vio, creó o modificó un registro clínico y cuándo (tabla tipo `audit_logs` o paquete como `spatie/laravel-activitylog`).
- Control de acceso estricto por rol en cada ruta/acción que toque datos de pacientes — nunca asumas que "ya está protegido" sin una policy explícita.
- Archivos subidos (radiografías, documentos) deben guardarse fuera del `public/` accesible directamente; sírvelos a través de rutas controladas con autorización.
- Contraseñas y sesiones siguen los defaults de seguridad de Laravel (hashing, CSRF, throttling de login) — no los debilites por conveniencia.

## 6. Esquema de base de datos (propuesto)

Este esquema surge de digitalizar las hojas físicas actuales del expediente (ficha de identificación, historia clínica/anamnesis, exploración y diagnóstico, odontograma, consentimientos y hoja de evolución con costos). Es la base de referencia para las migraciones — si necesitas desviarte de esto al implementar, confírmalo primero.

**`users`** (personal de la clínica)
`id`, `name`, `email`, `password`, `role` (enum: `doctor`, `administrador`, `superadmin`), timestamps

**`patients`**
`id`, `full_name`, `birth_date`, `sex` (M/F), `occupation`, `marital_status`, `address`, `phone`, `email`, `emergency_contact_name`, `emergency_contact_phone`, timestamps
*(`age` se calcula a partir de `birth_date`, no se almacena)*

**`medical_histories`** (uno por paciente)
`id`, `patient_id` (unique, FK → patients)
- Antecedentes patológicos: `has_diabetes`, `has_hypertension`, `has_heart_disease`, `has_hiv_hepatitis`, `has_coagulation_problems`, `has_seizures` (bools), `allergies` (texto: antibióticos/anestesia/látex), `current_medications` (texto), `has_been_hospitalized_or_operated` (bool + texto)
- Antecedentes no patológicos: `oral_hygiene_times_per_day` (int), `smokes` (bool), `drinks_alcohol` (bool)
- Adicionales (del consentimiento extendido): `prolonged_bleeding_history` (bool), `weight_loss_products_history` (bool), `is_pregnant` (bool, nullable), `pregnancy_time` (texto, nullable), `additional_health_notes` (texto)
- timestamps

**`consultations`** (una por cita)
`id`, `patient_id` (FK), `doctor_id` (FK → users), `consultation_date`
- Signos vitales: `blood_pressure`, `heart_rate`, `temperature`
- Exploración bucal: `soft_tissues_notes`, `gums_periodontium_notes`, `oral_hygiene_level` (enum: buena/regular/mala)
- `chief_complaint`, `clinical_diagnosis`, `treatment_plan`, `prognosis`, `risks_and_complications`, `treatment_alternatives`
- timestamps

**`consents`** (formulario genérico con tipo)
`id`, `patient_id` (FK), `doctor_id` (FK), `type` (enum: `general`, `extraction`, ampliable), `given_by` (paciente/representante legal/familiar), `relationship` (nullable), `diagnosis`, `proposed_treatment`, `specific_risks` (texto largo), `authorizes_photos_xrays` (bool), `patient_accepts` (bool), `patient_signature_path`, `doctor_signature_path`, `witness1_name`, `witness1_signature_path`, `witness2_name`, `witness2_signature_path`, `signed_at`, timestamps

**`consent_procedures`** (detalle Procedimiento/Costo de un consentimiento)
`id`, `consent_id` (FK), `procedure_name`, `cost`, timestamps

**`treatment_records`** (Hoja de Evolución y Control, con costos)
`id`, `patient_id` (FK), `consultation_id` (FK, nullable), `doctor_id` (FK), `treatment_date`, `procedure_performed`, `materials_used` (texto), `cost`, `amount_paid` (a cuenta), `balance` (saldo), timestamps

**`odontogram_records`** (un registro por diente por paciente, estado actual)
`id`, `patient_id` (FK), `tooth_number` (notación FDI: 11–18, 21–28, 31–38, 41–48 — *asumido, pendiente de confirmar*), `status` (enum ampliable: sano, caries, obturado, corona, extraído, endodoncia, implante, etc.), `note` (texto libre), `updated_by` (FK → users), timestamps

**`patient_files`** (radiografías, fotos, documentos)
`id`, `patient_id` (FK), `consultation_id` (FK, nullable), `uploaded_by` (FK → users), `file_path`, `file_type` (enum: radiografía/foto/documento), `description`, timestamps

**`audit_logs`**
`id`, `user_id` (FK), `patient_id` (FK, nullable), `action` (enum: viewed/created/updated/deleted), `auditable_type`, `auditable_id`, `ip_address`, `created_at`

**Relaciones clave:**
- `Patient hasOne MedicalHistory`
- `Patient hasMany Consultations, Consents, TreatmentRecords, OdontogramRecords, PatientFiles`
- `Consent hasMany ConsentProcedures`
- `User (doctor) hasMany Consultations, Consents, TreatmentRecords`
- `Consultation hasMany PatientFiles` (opcional, vía `consultation_id` nullable)

## 7. Identidad de marca y UI

Basado en el manual de marca de Aura (Brand Strategy Summary, elaborado por Good Makers).

**Logotipo:**
- Logotipo principal: "aura" en minúsculas, tipografía geométrica sans-serif delgada, con "dental club" debajo en una fuente más pequeña de la misma línea gráfica.
- Isotipo (ícono reducido): la letra "a" minúscula sola — úsalo para favicon, avatares o espacios pequeños donde no cabe el logotipo completo.
- Existe una variante secundaria con "Dental Club" en script/manuscrita, usada en el manual para aplicaciones de merchandising (tote bags, etiquetas) — no es la variante a usar en la interfaz del sistema salvo que se indique lo contrario.

**Tipografías oficiales:**
- Títulos: Downey Regular
- Subtítulos: Helvética Thai Regular
- Texto/párrafos: Helvética Thai Light

⚠️ Verificar disponibilidad y licencia de estas fuentes para uso web antes de implementarlas (no son fuentes estándar de Google Fonts, y Helvetica en particular es una fuente comercial). Si no se cuenta con los archivos de fuente originales o una licencia web, proponer alternativas tipográficamente similares (geométrica delgada para títulos, sans neutra para texto) y confirmarlo con el cliente antes de aplicarlas de forma definitiva.

**Paleta de colores:**

| Color | Hex | Uso sugerido |
|---|---|---|
| Blanco hueso | `#F3F3F0` | Fondo principal |
| Gris claro | `#D2D2D1` | Fondos secundarios, bordes, separadores |
| Verde oliva | `#7E8C54` | Color de acento de marca (botones primarios, enlaces activos, elementos destacados) |
| Gris sage | `#AEB0AC` | Acento secundario / neutro intermedio |
| Gris medio | `#6E6E6D` | Texto secundario |
| Gris oscuro | `#515150` | Texto principal, encabezados |

Nota: el verde oliva (`#7E8C54`) **no aparece en la paleta neutra del manual de marca** — fue proporcionado directamente por el cliente como el color de acento real de la marca. Los demás tonos sí vienen del manual oficial. Toda la paleta es de tonos neutros/apagados — evita introducir colores saturados o brillantes que no encajen con esta identidad minimalista.

**Personalidad y tono de marca:**
- Arquetipo: Sabio — experto, confiable, informativo.
- Estilo: Inteligente — claro, visionario, cercano a la audiencia, profesional.
- Tono de voz: claridad, sofisticación y autoridad.
- Estética general: minimalista, moderna, sofisticada ("menos es más"), tipografías claras, colores neutros con un acento verde oliva.

**Implicaciones para el diseño del sistema:**
- UI minimalista, con espacio en blanco generoso, sin elementos decorativos innecesarios.
- Usa la paleta de la tabla anterior como base del `tailwind.config.js` (ej. `aura-cream`, `aura-gray-light`, `aura-olive`, `aura-sage`, `aura-gray`, `aura-gray-dark`) en vez de hex codes sueltos en las vistas.
- El verde oliva (`#7E8C54`) es el acento principal — úsalo para botones primarios, estados activos y elementos de llamada a la acción, no como color de fondo extenso.
- Estados funcionales de la interfaz (éxito, error, advertencia) pueden requerir colores fuera de la paleta de marca (verde/rojo/ámbar estándar de UI) — está bien usarlos ahí, ya que son convenciones de usabilidad y no de identidad visual.

## 8. Convenciones de código

- **Idioma:** inglés en código — nombres de tablas, columnas, variables, clases, métodos. Español en la interfaz de usuario (textos visibles al usuario).
- **Estilo:** estándar Laravel/PSR-12 — `snake_case` en base de datos, `PascalCase` en clases, `camelCase` en métodos y variables.
- Sigue las convenciones idiomáticas de Laravel: Eloquent para el ORM, Form Requests para validación, Policies para autorización, Resource Controllers cuando aplique.
- Migraciones descriptivas y reversibles; nunca modifiques una migración ya "corrida" en producción — crea una nueva.
- Componentes Livewire con responsabilidad única; evita lógica de negocio pesada dentro del componente — muévela a servicios/actions cuando crezca.

## 9. Testing

- Cobertura mínima esperada: Form Requests (validación), Policies (autorización) y flujos críticos (crear/editar expediente, subir archivo, registrar consentimiento).
- Usa factories y seeders realistas mexicanos (nombres, CURP/RFC si aplica, formatos de teléfono locales) para datos de prueba — no dejes datos genéricos tipo "John Doe" sin avisar.
- Corre la suite de tests antes de considerar cualquier feature "terminada".

## 10. Agentes y comandos personalizados de Claude Code

Este repo trae herramientas propias en `.claude/` — úsalas en vez de improvisar el equivalente genérico:

- **Agente `code-reviewer`** (`.claude/agents/code-reviewer.md`) — **úsalo inmediatamente después de escribir o modificar código en este repo**, sin que el usuario lo pida. Es **obligatorio** (no opcional) cuando el cambio toca:
  - cualquier tabla o flujo con datos de paciente (`patients`, `medical_histories`, `consultations`, `consents`, `consent_procedures`, `treatment_records`, `odontogram_records`, `patient_files`, `audit_logs`);
  - autorización o roles (Policies, Gates, middleware);
  - migraciones nuevas o modificadas;
  - subida/descarga de archivos.
  Para cambios triviales fuera de esas áreas (ajustes de estilo, textos de UI, docs) sigue siendo buena práctica invocarlo, pero no es estrictamente obligatorio.
- **Comando `/review`** (`.claude/commands/review.md`) — auditoría más profunda que delega en paralelo a `php-reviewer`, `healthcare-reviewer` y `security-reviewer`, corre linters/tests/`composer audit`, y da un veredicto formal (APROBADO/ADVERTENCIA/BLOQUEADO). Úsalo antes de dar por cerrada una feature completa o un módulo entero, no solo un cambio puntual — para eso ya está el agente `code-reviewer`.
- **Comando `/fix-issue`** (`.claude/commands/fix-issue.md`) — para corregir bugs reportados: reproduce con una prueba en rojo, corrige la causa raíz, verifica, y **nunca** commitea sin que el usuario lo pida.
- **Agente `securrity-auditor`** (`.claude/agents/securrity-auditor.md`) — auditoría de seguridad dedicada (no de calidad general de código, para eso es `code-reviewer`). **Úsalo, sin que el usuario lo pida**:
  - antes de cualquier despliegue a producción o staging;
  - antes de un commit/PR que toque autenticación, sesiones, Policies/Gates, subida o descarga de archivos, o firmas de consentimiento;
  - cuando se introduzca o modifique una migración que afecte una tabla con datos de paciente;
  - periódicamente sobre el repo completo si ha pasado tiempo desde la última auditoría o tras varios cambios acumulados.
  Un solo hallazgo CRÍTICO de este agente (PHI expuesto, autorización o auditoría faltante) bloquea el despliegue — no lo omitas por prisa.

Reglas complementarias en `.claude/rules/`: `code-style.md` (convenciones de código, ver también §8), `testing.md` (estándares de pruebas, ver también §9) y `api-conventios.md` (rutas, códigos HTTP y manejo de errores).

## 11. Flujo de trabajo con Git

- Repo privado en GitHub.
- Commits descriptivos y atómicos.
- Antes de un cambio grande (nueva migración, refactor de estructura), confirma el enfoque si no está cubierto por este documento.

## 12. Fuera de alcance (por ahora)

- Soporte multi-sucursal.
- Autenticación o base de datos compartida con los sistemas de Citas o Inventarios.
- Integraciones de facturación o pagos.

## 13. Cuando algo no esté claro

Si una decisión de arquitectura, esquema de datos o regla de negocio no está cubierta en este archivo, **pregunta antes de asumir**, especialmente en temas de seguridad de datos clínicos y de roles/permisos.
