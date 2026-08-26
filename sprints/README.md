# Sprints — Expedientes Digitales Aura Dental Club

Roadmap de ejecución del MVP, derivado de `CLAUDE.md` §4 (módulos) y §6 (esquema). Cada sprint es un archivo `.md` en esta carpeta que se actualiza a medida que se trabaja con Claude Code: marca tareas, actualiza el **Estado** y agrega una fila en **Registro de ejecución** cada vez que se cierra un bloque de trabajo (una sesión, una feature, un fix relevante).

No se avanza al siguiente sprint con dependencias sin resolver, salvo que se documente explícitamente por qué se decidió adelantar.

## Cómo usar estos archivos con Claude Code

1. Antes de empezar un sprint, ábrelo y confirma que las dependencias listadas ya están en estado `Completado`.
2. Trabaja las tareas con `/fix-issue` para bugs y flujo normal de implementación para features nuevas — el agente `code-reviewer` se invoca automáticamente al modificar código (ver `CLAUDE.md` §10).
3. Al cerrar una tarea o sesión, agrega una fila en la tabla **Registro de ejecución** del sprint correspondiente (fecha, qué se hizo, comando/agente usado, resultado).
4. Antes de marcar un sprint como `Completado`, revisa su sección **Criterios de aceptación** y **Testing requerido**.
5. Los sprints 8 (auditoría/seguridad) y 9 (deploy) no son "features" — son gates de calidad/seguridad que se corren real, no se saltan por prisa.

## Cómo correr el proyecto en local

Guía para levantar el sistema en tu máquina y probarlo en el navegador. Asume Laravel Herd (PHP 8.4) y Docker Desktop ya instalados.

### 1. Primera vez (setup)

```powershell
# Dependencias PHP y JS
composer install
npm install

# Variables de entorno (.env ya está commiteado para este entorno local de desarrollo —
# si no existiera, copia .env.example y ajusta APP_KEY con `php artisan key:generate`)

# Base de datos MySQL vía Docker (contenedor aura_dental_club_mysql, puerto host 3307)
docker compose up -d

# Migraciones + datos de prueba (3 usuarios, uno por rol, password "password")
php artisan migrate
php artisan db:seed
```

> **Nota Windows:** si usas Git Bash y `php`/`composer` no se reconocen (`command not found`), corre estos comandos desde **PowerShell** — en este entorno Herd solo registra el PATH de PHP ahí.

### 2. Correr la app día a día

Necesitas dos procesos corriendo en paralelo:

```powershell
# Terminal 1 — servidor Laravel
php artisan serve --no-reload
# → http://127.0.0.1:8000

# Terminal 2 — Vite (compila Tailwind/JS en caliente)
npm run dev
```

Si el contenedor de MySQL no está levantado (`docker ps` no lo muestra), primero `docker compose up -d`.

Abre `http://127.0.0.1:8000` — redirige a `/login`. Usuarios de prueba (seeder `DatabaseSeeder`, password `password` para los tres):

| Rol | Email |
|---|---|
| Superadmin | `superadmin@auradentalclub.test` |
| Administrador | `administrador@auradentalclub.test` |
| Doctor | `doctor@auradentalclub.test` |

### 3. Correr los tests

```powershell
php artisan test
```

Usa la base `aura_dental_testing` (creada automáticamente por `docker/mysql/init/01-testing-database.sql` al levantar el contenedor por primera vez) — ver `.env.testing`. Nunca corre contra la base de desarrollo.

### 4. Problemas comunes

- **`Failed to listen on 127.0.0.1:8000` (y sigue reintentando 8001, 8002... hasta fallar en los 10):** en Windows, `php artisan serve` intenta respetar `PHP_CLI_SERVER_WORKERS` (definido en `.env`) creando varios procesos worker, pero el *forking* no está soportado en Windows y el bind falla en cascada. Solución: siempre usa `php artisan serve --no-reload` (fuerza un solo proceso) — ya reflejado en el paso 2 de esta guía.
- **`SQLSTATE[HY000] [2002]` o error de conexión a MySQL:** el contenedor no está levantado o no terminó su healthcheck — `docker compose up -d` y espera unos segundos (`docker ps`, columna `STATUS` debe decir `healthy`).
- **Estilos de Tailwind no cargan / la página se ve sin diseño:** falta `npm run dev` corriendo, o falta compilar con `npm run build` para una vista rápida sin hot-reload.
- **`Vite manifest not found`:** corre `npm run build` (o mantén `npm run dev` activo mientras navegas).

## Roadmap

| # | Sprint | Depende de | Estado |
|---|--------|-----------|--------|
| 0 | [Fundación del proyecto](sprint-00-fundacion.md) | — | Completado |
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

- **Sprint 0 completado** (2026-08-26): proyecto Laravel 12 + PHP 8.4 corriendo, auth a medida, roles, Tailwind con paleta de marca, `audit_logs` con trait `Auditable`, Pest en verde. Ya se puede empezar el Sprint 1.
- **4 (Odontograma)** solo depende de Pacientes, no de Historia clínica/Consultas — puede adelantarse en paralelo a 2/3 si conviene.
- **7 (Archivos)** puede empezar en paralelo a 3 una vez que 1 esté listo, ya que `patient_files.consultation_id` es nullable (`CLAUDE.md` §6).
- **8 y 9 nunca se saltan** — son los gates de "esto toca datos clínicos reales" antes de que la clínica use el sistema en producción.
