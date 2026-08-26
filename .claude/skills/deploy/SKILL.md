---
name: hostinger-deploy
description: Guía y checklist para desplegar Expedientes Digitales Aura Dental Club (Laravel 11 + Livewire + MySQL) en hosting Hostinger. Úsala cuando el usuario pida desplegar, publicar, subir a producción/staging, configurar el dominio en Hostinger, o preparar el primer deploy de este sistema.
---

# Deploy en Hostinger — Expedientes Digitales Aura Dental Club

> Este sistema vivirá eventualmente bajo `auradentalclub.com` junto a otros dos sistemas (Citas, Inventarios), pero con **su propia base de datos y autenticación** (ver `CLAUDE.md` §1). Trátalo como un despliegue independiente aunque comparta hosting/dominio raíz.

## 0. Antes de tocar nada de infraestructura

**No despliegues sin antes correr los agentes de seguridad/calidad de este repo.** Un despliegue expone datos clínicos reales — no es un paso reversible con un `git revert`.

1. Invoca el agente `securrity-auditor` (`.claude/agents/securrity-auditor.md`) sobre el repo completo. Un solo hallazgo CRÍTICO bloquea el deploy.
2. Invoca el agente `code-reviewer` (`.claude/agents/code-reviewer.md`) si hay cambios sin revisar desde el último deploy.
3. Corre la suite de pruebas completa (`.claude/rules/testing.md`) — no despliegues con tests en rojo.
4. Confirma con el usuario/cliente cualquier duda de cumplimiento NOM-004/015/013 pendiente (`CLAUDE.md` §5, §13) — no la resuelvas por tu cuenta en el momento del deploy.

Si cualquiera de estos pasos no se puede correr (ej. no hay entorno de pruebas configurado aún), dilo explícitamente antes de continuar — no asumas que "está bien" para avanzar más rápido.

## 1. Primero: identifica el tipo de plan de Hostinger

Hostinger tiene ofertas muy distintas y el flujo de deploy cambia según cuál se contrató. **Pregunta al usuario cuál aplica** si no está claro (no lo asumas):

| Plan | SSH | Composer/Artisan vía SSH | Cron jobs | Ideal para |
|---|---|---|---|---|
| Hosting Compartido (Premium/Business) | Sí, en planes Business+ | Sí (PHP CLI disponible) | Sí, desde hPanel | Sitios pequeños de bajo tráfico — clínica de un solo consultorio encaja aquí |
| Hostinger Cloud/VPS | Sí, acceso root | Sí, control total | Sí, vía `crontab` | Más control, necesario si se requiere queue worker persistente (Supervisor), Redis, etc. |

Para una clínica de un solo consultorio con tráfico bajo-medio, **Hosting Compartido Business o Cloud Startup** suele ser suficiente — confírmalo con el usuario en vez de sobredimensionar la infraestructura sin necesidad (YAGNI).

## 2. Preparación del proyecto para producción

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build        # si hay assets Tailwind/Vite compilados
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

`.env` de producción (nunca lo subas por FTP tal cual desde tu máquina de desarrollo — créalo directo en el servidor o vía variables de entorno de hPanel):

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://expedientes.auradentalclub.com   # confirmar subdominio real con el cliente
APP_KEY=                                          # generar en el servidor: php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=                                      # crear en hPanel > Bases de datos MySQL
DB_USERNAME=                                      # usuario dedicado, NO el usuario root del hosting
DB_PASSWORD=

FILESYSTEM_DISK=local
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
```

Checklist de este paso:

- [ ] `APP_DEBUG=false` — verificado, no solo asumido.
- [ ] `APP_KEY` generada en el servidor de destino, no reutilizada de desarrollo.
- [ ] Credenciales de MySQL son de un usuario con privilegios limitados a esa base de datos (no el usuario admin de todo el hosting).
- [ ] `.env` **no** se sube al repositorio ni queda accesible por HTTP (ver §5).

## 3. Estructura de carpetas y document root

Laravel sirve desde `public/`, pero Hostinger apunta el dominio/subdominio a `public_html/` (o la carpeta que definas) por defecto. Dos enfoques válidos — elige uno y sé consistente:

**Opción A — Document root apuntando directo a `public/` (recomendada si el plan lo permite):**
En hPanel, al crear el subdominio, configura su *carpeta raíz* como `expedientes/public` (donde `expedientes/` contiene todo el proyecto Laravel fuera de `public_html`). Así el resto del código (`app/`, `.env`, `storage/`) queda fuera del árbol servido públicamente — la opción más segura.

**Opción B — Proyecto completo dentro de `public_html/` (si el hosting no permite root personalizado):**
Sube el proyecto completo a una carpeta hermana de `public_html` (ej. `expedientes_app/`) y copia/enlaza solo el contenido de `public/` dentro de `public_html/expedientes/`, ajustando las rutas `index.php` (`require __DIR__.'/../expedientes_app/vendor/autoload.php'`, etc.) para apuntar al proyecto real. Más frágil — prefiere la Opción A si el plan lo soporta.

**Nunca** subas el proyecto completo (incluyendo `.env`, `app/`, `storage/`) directamente dentro de `public_html/` sin ajustar el document root — eso deja `.env` y el código fuente potencialmente accesibles por URL.

## 4. Transferencia de código

Según el plan (§1):

**Con SSH (recomendado):**
```bash
ssh usuario@servidor -p <puerto>
cd ~/expedientes
git clone <repo-privado> .          # o git pull si ya existe
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
```

**Sin SSH (solo SFTP/File Manager de hPanel):**
Compila localmente (`composer install --no-dev`, build de assets), sube por SFTP excluyendo `.git/`, `.env`, `node_modules/` y `tests/`. Este camino es más lento y propenso a errores — prefiere habilitar SSH si el plan lo permite.

**Git de Hostinger (hPanel > Avanzado > Git):** Hostinger permite conectar un repo y hacer deploy automático a un push. Si se usa, confirma que el hook post-deploy corre `composer install --no-dev`, `migrate --force` y los `cache` de Laravel — si no, hazlo manual tras cada push.

## 5. Hardening específico de este proyecto (datos clínicos)

- [ ] `storage/` y `bootstrap/cache/` con permisos de escritura para el proceso PHP (`chmod -R 775` sobre esas carpetas, nunca `777`, y nunca sobre el proyecto completo).
- [ ] `.env`, `.git/`, `composer.json`, `artisan` **no accesibles por URL** — si usaste la Opción B de §3, agrega un `.htaccess` en la raíz servida que niegue acceso a esos archivos/carpetas explícitamente.
- [ ] SSL activo (Hostinger ofrece Let's Encrypt gratis en hPanel > SSL) y HTTPS forzado — confirma que `APP_URL` usa `https://` y que hay redirect automático de `http` a `https`.
- [ ] El disco de `patient_files` (radiografías, fotos, documentos, firmas) vive fuera de `public/` — confirma que la ruta de storage configurada en `.env`/`config/filesystems.php` no cae dentro del document root servido.
- [ ] Backups de la base de datos configurados (Hostinger ofrece backups automáticos en hPanel según el plan) — para un expediente clínico esto no es opcional; si el plan no los incluye, dilo explícitamente al usuario en vez de asumir que ya existen.
- [ ] Corre el agente `securrity-auditor` una vez más **ya en el servidor de destino** (o contra la config final) antes de anunciar el sistema como disponible.

## 6. Tareas programadas y colas

Si el proyecto usa `Schedule` o `Queue` de Laravel:

**Cron del scheduler (hPanel > Avanzado > Cron Jobs):**
```
* * * * * php /home/usuario/expedientes/artisan schedule:run >> /dev/null 2>&1
```

**Colas en hosting compartido** (sin Supervisor): usa el driver `database` y un cron cada minuto en vez de un worker persistente:
```
* * * * * php /home/usuario/expedientes/artisan queue:work --stop-when-empty >> /dev/null 2>&1
```
Si el plan es VPS con acceso root, prefiere Supervisor con `queue:work` persistente en vez de este workaround por cron.

## 7. Verificación post-deploy

```bash
php artisan about                 # confirma env=production, debug=false, driver de sesión/caché
php artisan migrate:status
php artisan route:list --json | grep -i patient   # confirma que las rutas clínicas tienen middleware
```

Checklist final antes de dar el deploy por completo:

- [ ] Login funciona para los tres roles (`doctor`, `administrador`, `superadmin`) con al menos un usuario real de prueba.
- [ ] Subir un archivo de prueba a `patient_files` y confirmar que **no** es accesible por URL directa sin autenticación.
- [ ] Un registro de auditoría se genera al ver/editar un expediente de prueba (`audit_logs`).
- [ ] `APP_DEBUG=false` confirmado provocando un error intencional (ej. ruta inexistente) y verificando que no se filtra ningún stack trace.
- [ ] HTTPS activo y sin advertencias de certificado mixto (assets cargados por `http://`).

## 8. Rollback

- Si el deploy es vía Git: `git checkout <commit-anterior>` en el servidor + `composer install --no-dev` + `php artisan migrate:rollback` (solo si la migración del release es reversible y segura de revertir sin pérdida de datos clínicos — confírmalo antes de correrlo).
- Si el deploy es manual (SFTP): mantén el release anterior en una carpeta hermana (`releases/YYYY-MM-DD/`) para poder repuntar el document root a la versión previa sin perder tiempo re-subiendo archivos.
- **Nunca** hagas rollback de la base de datos en un sistema con datos clínicos reales sin respaldo verificado inmediatamente antes — un rollback de esquema puede perder registros de pacientes ya capturados.

## Fuera de alcance de esta skill

- Migrar de Hostinger a otro proveedor.
- Configurar los otros dos sistemas (Citas, Inventarios) que convivirán en `auradentalclub.com` — cada uno es independiente (`CLAUDE.md` §1).
- Decisiones de arquitectura de infraestructura no cubiertas aquí (CDN, balanceo de carga) — este es un sistema de un solo consultorio, no sobredimensiones sin que el cliente lo pida.
