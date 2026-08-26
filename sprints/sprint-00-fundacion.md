# Sprint 0 — Fundación del proyecto

**Estado:** Completado
**Depende de:** —

## Objetivo

Dejar un proyecto Laravel 12 limpio, corriendo, con autenticación propia (sin Breeze/Jetstream), los tres roles, la identidad visual base y el andamiaje de testing/seguridad listos — antes de tocar cualquier módulo clínico.

## Alcance (CLAUDE.md §2, §3, §7, §8)

- Instalación de Laravel 12 + PHP 8.4.
- Base de datos MySQL configurada (local de desarrollo).
- Autenticación construida a medida (login, logout, recuperación de contraseña) — sin starter kit.
- Los tres roles (`superadmin`, `administrador`, `doctor`) modelados en `users.role` con al menos un seeder de cada uno.
- Tailwind CSS configurado con la paleta de marca (`aura-cream`, `aura-gray-light`, `aura-olive`, `aura-sage`, `aura-gray`, `aura-gray-dark`) en `tailwind.config.js`.
- Layout base Blade (navegación, sidebar/topbar según rol) con la identidad minimalista de §7 — sin implementar módulos clínicos todavía.
- Repositorio Git inicializado (privado en GitHub) — `git init` si aún no existe.
- Pest o PHPUnit configurado y corriendo (`.claude/rules/testing.md`).
- Tabla y mecanismo de `audit_logs` (modelo, migración, y un helper/trait/observer base) — aunque todavía no haya nada que auditar, la infraestructura debe existir antes del Sprint 1.

## Tareas

- [x] `composer create-project laravel/laravel .` (o equivalente) y confirmar PHP 8.4.
- [x] Configurar `.env` de desarrollo (MySQL, `APP_NAME`, `APP_URL` local) — MySQL 8.4 vía `docker-compose.yml`, puerto 3307.
- [x] `git init`, primer commit, `.gitignore` correcto (`.env`, `vendor/`, `node_modules/`, `storage/*.key`).
- [x] Migración `users` con columna `role` (enum `doctor`/`administrador`/`superadmin`).
- [x] Sistema de auth a medida: login, logout, "olvidé mi contraseña" — usando los defaults de seguridad de Laravel (hashing, throttling, CSRF) sin debilitarlos (`CLAUDE.md` §5).
- [x] Middleware/Gate base de rol (aún genérico, sin policies de dominio todavía — esas llegan por módulo) — `EnsureUserHasRole`.
- [x] Tailwind instalado con la paleta de marca — Tailwind v4 vía `@theme` en `resources/css/app.css` (v4 ya no usa `tailwind.config.js`).
- [x] Layout Blade base (`resources/views/components/app-layout.blade.php`) con navegación condicionada por rol.
- [x] Migración + modelo `AuditLog` (`audit_logs`, ver `CLAUDE.md` §6) y un mecanismo reutilizable (trait `Auditable`) para registrar `viewed`/`created`/`updated`/`deleted` que los módulos siguientes puedan enchufar.
- [x] Pest instalado y corriendo — 16 tests en verde.
- [x] Seeders: 1 usuario por rol con datos de prueba mexicanos (Alejandra Rosales Villanueva, Fernando Iturbide Casillas, Dra. Mariana Cabrera Solórzano).
- [x] Verificado que `.claude/settings.json` (hook `validate-bash.sh`, permissions) funciona en este entorno real de desarrollo.

## Criterios de aceptación

- [x] `php artisan serve` levanta el proyecto sin errores (verificado: `GET /login` → 200).
- [x] Login funciona para los tres roles y redirige según corresponda (o al menos deja pasar a un dashboard genérico).
- [x] `php artisan test` corre y pasa (16 passed, 37 assertions).
- [x] La paleta de marca es visible en el layout (no hex codes sueltos en las vistas, ver `CLAUDE.md` §7) — tokens `aura-*`.
- [x] `audit_logs` existe como tabla y hay una forma clara y documentada (en el código) de escribir en ella desde un controlador/modelo — trait `App\Models\Concerns\Auditable`.

## Testing requerido

- Test de que el login/logout funciona por rol.
- Test de que un usuario sin rol válido no puede autenticarse (si aplica una validación de ese tipo).
- Ver `.claude/rules/testing.md` para cobertura mínima y estilo AAA.

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| 2026-08-26 | Proyecto Laravel 12 + PHP 8.4 creado, MySQL 8.4 vía Docker (`docker-compose.yml`, puerto 3307), auth a medida (login/logout/reset password), enum `UserRole`, middleware `EnsureUserHasRole`, Tailwind v4 con paleta de marca (`@theme`), layout Blade con nav por rol, tabla/modelo `AuditLog` + trait `Auditable`, seeders con datos mexicanos, Pest configurado | Sesión interactiva de desarrollo | 16 tests en verde; sesión se cortó por un crash de `bash.exe` (se limpió el `.stackdump` residual) antes de cerrar el sprint |
| 2026-08-26 | Verificación de continuidad: migraciones al día, tests re-corridos (16 passed), smoke test de `php artisan serve` (`/login` → 200), revisión de seeders en BD dev (3 usuarios) | Retomado tras el corte | Sprint 0 confirmado completo — checklist actualizado |
